<?php
/**
 * 2017 Lemon way
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@lemonway.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this addon to newer
 * versions in the future. If you wish to customize this addon for your
 * needs please contact us for more information.
 *
 * @author Lemon Way <it@lemonway.com>
 * @copyright  2017 Lemon way
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class LemonwayValidationModuleFrontController extends ModuleFrontController
{
    protected function isGet()
    {
        return Tools::strtoupper($_SERVER['REQUEST_METHOD']) == 'GET';
    }

    protected function isPost()
    {
        return Tools::strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
    }

    protected function displayError()
    {
        if ($this->module->isVersion17()) {
            return $this->redirectWithNotifications(Context::getContext()->link->getPageLink("order"));
        }

        /**
         * Create the breadcrumb for your ModuleFrontController.
         */
        $path = '<a href="' . Context::getContext()->link->getPageLink('order', null, null, 'step=3') . '">'
            . $this->module->l('Payment')
            . '</a><span class="navigation-pipe">&gt;</span>' . $this->module->l('Error');

        Context::getContext()->smarty->assign(
            array(
                "path" => $path,
                "errors" => $this->errors,
                "warning" => $this->warning
            )
        );

        $template = 'error.tpl';

        if ($this->module->isVersion17()) {
            $template = 'module:' . $this->module->name . '/views/templates/front/error.tpl';
        }

        return $this->setTemplate($template);
    }

    // Public functions
    public function __construct()
    {
        parent::__construct();
        require_once _PS_MODULE_DIR_ . $this->module->name . '/services/LemonWayKit.php';
    }

    // Main function: Validating the order
    public function postProcess()
    {
        PrestaShopLogger::addLog(
            "LemonWay::validation - Payment validation: " . print_r($_REQUEST, true),
            1,
            null,
            "LemonWay",
            $this->module->id,
            true
        );

        try {
            if (!Tools::isSubmit("response_wkToken")
                || !Tools::isSubmit("action")
                || !Tools::isSubmit("method_code")
                || !Tools::isSubmit("secure_key")) {
                throw new Exception($this->module->l('Bad request.'));
            }

            $wkToken = Tools::getValue("response_wkToken");

            // Retrieve the cart
            $cart_id = $this->module->getCartIdFromToken($wkToken);
            $cart = new Cart($cart_id);

            if (!$cart_id || !$cart) {
                throw new Exception($this->module->l('Cart not found.'), $cart_id);
            }

            $secure_key = Tools::getValue("secure_key");

            if ($secure_key != $cart->secure_key) {
                throw new Exception($this->module->l('Secure key does not match.'));
            }

            // Operation details
            $kit = new LemonWayKit();

            $params = array(
                "transactionMerchantToken" => $wkToken
            );

            $hpay = $kit->getMoneyInTransDetails($params);

            // If save card
            $register_card = Tools::getValue("register_card", false);
            if ($register_card && $cart->id_customer) {
                $card = $this->module->getCustomerCard($cart->id_customer);

                if ($card) {
                    $card['id_customer'] = $cart->id_customer;
                    $card['card_num'] = isset($hpay->EXTRA) ? $hpay->EXTRA->NUM : null;
                    $card['card_type'] = isset($hpay->EXTRA) ? $hpay->EXTRA->TYP : null;
                    $card['card_exp'] = isset($hpay->EXTRA) ? $hpay->EXTRA->EXP : null;

                    $this->module->insertOrUpdateCard($cart->id_customer, $card);
                }
            }

            $action = Tools::getValue("action");

            $methodInstance = $this->module->methodFactory(Tools::getValue("method_code"));

            if ($this->isPost()) {
                if (!Tools::isSubmit("response_code") || !Tools::isSubmit("response_msg")) {
                    throw new Exception("Bad IPN.");
                }

                $response_code = Tools::getValue("response_code");
                $response_msg = Tools::getValue("response_msg");
            }

            switch ($action) {
                case "return":
                    if (!empty($response_code) && $response_code !== "0000") {
                        if ($response_code === "2002") {
                            $order_state = Configuration::get("PS_OS_CANCELED");
                            $message = $this->module->l('Operation canceled by user:');
                        } else {
                            $order_state = Configuration::get("PS_OS_ERROR");
                            $this->module->l('Payment error:');
                        }

                        if ($cart->OrderExists()) {
                            $order_id = Order::getOrderByCartId($cart->id);

                            if (!$order_id) {
                                throw new Exception($this->module->l('Order ID not found.'));
                            }

                            $order = new Order($order_id);

                            if (!$order) {
                                throw new Exception($this->module->l('Order not found.'));
                            }

                            $order->setCurrentState($order_state);
                        }

                        $message .= " " . $hpay->INT_MSG;
                        if (!empty($response_msg)) {
                            $message .= " (" . $response_msg . ")";
                        }

                        throw new Exception($message);
                    }

                    switch ($hpay->INT_STATUS) {
                        case 6:
                            // Error
                            if ($cart->OrderExists()) {
                                // Abort order if exists
                                $order_id = Order::getOrderByCartId($cart->id);

                                if (!$order_id) {
                                    throw new Exception($this->module->l('Order ID not found.'));
                                }

                                $order = new Order($order_id);

                                if (!$order) {
                                    throw new Exception($this->module->l('Order not found.'));
                                }

                                $order->setCurrentState(Configuration::get("PS_OS_ERROR"));
                            }

                            $message = $this->module->l('Payment error:') . " " . $hpay->INT_MSG;
                            if (!empty($response_msg)) {
                                $message .= " (" . $response_msg . ")";
                            }

                            throw new Exception($message);
                        default:
                            // Not error + returnUrl => success
                            $is_order_validated = Db::getInstance()->getValue(
                                "SELECT `is_order_validated` 
                                FROM `" . _DB_PREFIX_ . "lemonway_wktoken` 
                                WHERE `wktoken` = '" . pSQL($wkToken) . "' AND `id_cart` = '" . pSQL($cart->id) . "'"
                            );

                            if (!$cart->OrderExists() && $is_order_validated === "0") {
                                // Update is_order_validated flag
                                Db::getInstance()->update(
                                    "lemonway_wktoken",
                                    array("is_order_validated" => 1),
                                    " `wktoken` = '" . pSQL($wkToken) .
                                        "' AND `id_cart` = '" . pSQL($cart->id) .
                                        "' AND `is_order_validated` = '0'"
                                );

                                if (Db::getInstance()->Affected_Rows() == 1) {
                                    $message = $hpay->MSG . ": " . $hpay->INT_MSG;
                                    if (!empty($response_msg)) {
                                        $message .= " (" . $response_msg . ")";
                                    }

                                    if ($methodInstance->isSplitPayment()) {
                                        // If split payment
                                        $id_order_state = Configuration::get(Lemonway::LEMONWAY_SPLIT_PAYMENT_OS);
                                    } else {
                                        // If not split payment
                                        $id_order_state = Configuration::get("PS_OS_PAYMENT");
                                    }

                                    // Convert cart into a valid order
                                    $this->module->validateOrder(
                                        $cart->id, // $id_cart
                                        $id_order_state, // $id_order_state
                                        $hpay->CRED, // Amount really paid by customer (in the default currency)
                                        $methodInstance->getTitle(), // Payment method (eg. 'Credit card')
                                        $message, // Message to attach to order
                                        array("transaction_id" => $hpay->ID), // $extra_vars
                                        null, // $currency_special
                                        false, // $dont_touch_amount
                                        $secure_key // $secure_key
                                    );

                                    // If split payment
                                    if ($methodInstance->isSplitPayment()) {
                                        // Get order
                                        $order_id = Order::getOrderByCartId($cart->id);

                                        if (!$order_id) {
                                            throw new Exception($this->module->l('Order ID not found.'));
                                        }

                                        $order = new Order($order_id);

                                        if (!$order) {
                                            throw new Exception($this->module->l('Order not found.'));
                                        }

                                        // Invoice
                                        $invoiceCollection = $order->getInvoicesCollection();
                                        $lastInvoice = $invoiceCollection
                                            ->orderBy("date_add")
                                            ->setPageNumber(1)
                                            ->setPageSize(1)
                                            ->getFirst();

                                        // Add order payment
                                        $order->addOrderPayment(
                                            $hpay->CRED, // $amount_paid
                                            null, // $payment_method
                                            $hpay->ID, // $payment_transaction_id
                                            null, // $currency
                                            null, // $date
                                            $lastInvoice // $order_invoice
                                        );

                                        $cardKey = "LEMONWAY_CARD_ID_" . $order->id_customer . "_" . $order->id_cart;
                                        $cardId = Configuration::get($cardKey);

                                        if (!$cardId) {
                                            throw new Exception($this->module->l('Card token not found.'));
                                        }

                                        $splitPaypentProfileId = Tools::getValue("splitpayment_profile_id");

                                        if (!$splitPaypentProfileId) {
                                            throw new Exception(
                                                $this->module->l('Split payment profile ID not found.')
                                            );
                                        }

                                        $profile = new SplitpaymentProfile($splitPaypentProfileId);

                                        if (!$profile) {
                                            throw new Exception($this->module->l('Split payment profile not found.'));
                                        }

                                        // Save deadlines
                                        $profile->generateDeadlines(
                                            $order,
                                            $cardId,
                                            $methodInstance->getCode(),
                                            true,
                                            true
                                        );

                                        ConfigurationCore::deleteByName($cardKey);
                                    }
                                }
                            }

                            if ($this->isGet()) {
                                //The order has been placed so we redirect the customer on the confirmation page.
                                Tools::redirect(
                                    Context::getContext()->link->getPageLink(
                                        "order-confirmation",
                                        null,
                                        null,
                                        array(
                                            "id_cart" => $cart->id,
                                            "id_module" => $this->module->id,
                                            "key" => $secure_key
                                        )
                                    )
                                );
                            } else {
                                // No redirection if POST
                                return true;
                            }
                            break;
                    }
                    break;
                case "error":
                    if ($cart->OrderExists()) {
                        // Abort order if exists
                        $order_id = Order::getOrderByCartId($cart->id);

                        if (!$order_id) {
                            throw new Exception($this->module->l('Order ID not found.'));
                        }

                        $order = new Order($order_id);

                        if (!$order) {
                            throw new Exception($this->module->l('Order not found.'));
                        }

                        $order->setCurrentState(Configuration::get("PS_OS_ERROR"));
                    }

                    $message = $this->module->l('Payment error:') . " " . $hpay->INT_MSG;
                    if (!empty($response_msg)) {
                        $message .= " (" . $response_msg . ")";
                    }

                    throw new Exception($message);
                case "cancel":
                    PrestaShopLogger::addLog(
                        "LemonWay::validation - Customer has canceled the payment.",
                        1,
                        null,
                        "Cart",
                        $cart->id,
                        true
                    );
                    if ($cart->OrderExists()) {
                        // Cancel order if exists
                        $order_id = Order::getOrderByCartId($cart->id);
                        
                        if (!$order_id) {
                            throw new Exception($this->module->l('Order ID not found.'));
                        }

                        $order = new Order($order_id);

                        if (!$order) {
                            throw new Exception($this->module->l('Order not found.'));
                        }
                        
                        $order->setCurrentState(Configuration::get("PS_OS_CANCELED"));
                    }

                    if (!$this->module->isVersion17()) {
                        $this->warning = array();
                    }
                    array_push($this->warning, $this->module->l('You have canceled the payment.'));
                    return $this->displayError();
                default:
                    throw new Exception($this->module->l('Bad request.'));
            }
        } catch (Exception $e) {
            $cart_id = isset($cart) ? $cart->id : null;
            PrestaShopLogger::addLog(
                "LemonWay::validation - " . $e->getMessage() . " (" . $e->getCode() . ")",
                4,
                null,
                "Cart",
                $cart_id,
                true
            );

            if ($this->isGet()) {
                array_push($this->errors, $e->getMessage() . " (" . $e->getCode() . ")");
                return $this->displayError();
            } else {
                return false;
            }
        }
    }
}
