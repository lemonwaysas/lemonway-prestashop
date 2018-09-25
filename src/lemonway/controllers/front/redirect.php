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

class LemonwayRedirectModuleFrontController extends ModuleFrontController
{
    protected $supportedLangs = array(
        'da' => 'da',
        'de' => 'ge',
        'en' => 'en',
        'es' => 'sp',
        'fi' => 'fi',
        'fr' => 'fr',
        'it' => 'it',
        'ko' => 'ko',
        'no' => 'no',
        'pt' => 'po',
        'sv' => 'sw'
    );

    protected $defaultLang = 'en';

    protected function displayError()
    {
        if ($this->module->isVersion17()) {
            return $this->redirectWithNotifications($this->context->link->getPageLink("order"));
        }

        /**
         * Create the breadcrumb for your ModuleFrontController.
         */
        $path = '<a href="' . $this->context->link->getPageLink('order', null, null, 'step=3') . '">'
            . $this->module->l('Payment')
            . '</a><span class="navigation-pipe">&gt;</span>' . $this->module->l('Error');

        $this->context->smarty->assign(
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

    protected function methodIsAllowed($method_code)
    {
        $method_code = Tools::strtoupper($method_code);

        if (!Configuration::get('LEMONWAY_' . $method_code . '_ENABLED')) {
            return false;
        }

        switch ($method_code) {
            case "creditcard_xtimes":
                if (!in_array(Tools::getValue('splitpayment_profile_id'), $this->module->getSplitpaymentProfiles())) {
                    return false;
                } else {
                    return true;
                }
                break;

            default:
                return true;
        }
    }

    protected function registerCard()
    {
        return Tools::getValue('lw_oneclic') === 'register_card';
    }

    protected function useCard()
    {
        return Tools::getValue('lw_oneclic') === 'use_card';
    }

    /**
     * Return current lang code
     *
     * @return string
     */
    protected function getLang()
    {
        if (array_key_exists($this->context->language->iso_code, $this->supportedLangs)) {
            return $this->supportedLangs[$this->context->language->iso_code];
        }

        return $this->defaultLang;
    }

    // Public functions
    public function __construct()
    {
        parent::__construct();
        require_once _PS_MODULE_DIR_ . $this->module->name . '/services/LemonWayConfig.php';
        require_once _PS_MODULE_DIR_ . $this->module->name . '/services/LemonWayKit.php';
    }

    // Main function: Create payment
    public function postProcess()
    {
        try {
            // Get context
            $cart = $this->context->cart;
            $customer = $this->context->customer;

            $method_code = Tools::getValue("method_code");

            $methodInstance = $this->module->methodFactory($method_code);
        
            if (!$methodInstance->isAllowed()) {
                throw new Exception($this->module->l('Payment method not allowed.'), $method_code);
            }

            // If split payment
            if ($methodInstance->isSplitPayment()) {
                $splitPaypentProfileId = Tools::getValue("splitpayment_profile_id");

                if (!$splitPaypentProfileId) {
                    throw new Exception($this->module->l('Split payment profile ID not found.'));
                }

                $profile = new SplitpaymentProfile($splitPaypentProfileId);

                if (!$profile) {
                    throw new Exception($this->module->l('Split payment profile not found.'));
                }
            }

            // Generate a new wkToken for this cart
            $wkToken = $this->module->saveWkToken($cart->id);

            // Get wallet name
            $wallet = LemonWayConfig::getWalletMerchantId();

            // Get amount
            $orderTotal = $cart->getOrderTotal();

            if ($methodInstance->isSplitPayment()) {
                // If split payment
                $splitpayments = $profile->splitPaymentAmount($orderTotal);
                $firstSplit = $splitpayments[0];
                $amountTot = number_format((float) $firstSplit["amountToPay"], 2, ".", "");
            } else {
                // If not split payment
                $amountTot = number_format((float)$orderTotal, 2, ".", "");
            }

            // LW Entreprise => autocom
            $autoCommission = LemonWayConfig::is4EcommerceMode() ? 0 : 1;

            // Generate comment
            $comment = Configuration::get("PS_SHOP_NAME")
                . " - " . $cart->id
                . " - " . $customer->lastname . " " . $customer->firstname
                . " - " . $customer->email;

            // Urls
            $secure_key = $cart->secure_key;
            $baseCallbackParams = array(
                "secure_key" => $secure_key,
                "method_code" => $method_code,
                "register_card" => (int) $this->registerCard()
            );

            // If split payment
            if ($methodInstance->isSplitPayment()) {
                //Add prodile ID to base callback params
                $baseCallbackParams["splitpayment_profile_id"] = $splitPaypentProfileId;
            }

            $returnCallbackParams = array_merge($baseCallbackParams, array(
                "action" => "return"
            ));

            $cancelCallbackParams = array_merge($baseCallbackParams, array(
                "action" => "cancel"
            ));

            $errorCallbackParams = array_merge($baseCallbackParams, array(
                "action" => "error"
            ));

            $kit = new LemonWayKit();

            // Payment
            if (!$customer->isLogged() || !$this->useCard()) {
                // Not rebill => MoneyInWebInit
                $params = array(
                    "wkToken" => $wkToken,
                    "wallet" => $wallet,
                    "amountTot" => $amountTot,
                    "amountCom" => "0.00",
                    "autoCommission" => $autoCommission,
                    "comment" => $comment,
                    "returnUrl" => urlencode($this->context->link->getModuleLink(
                        "lemonway",
                        "validation",
                        $returnCallbackParams,
                        true
                    )),
                    "errorUrl" => urlencode($this->context->link->getModuleLink(
                        "lemonway",
                        "validation",
                        $errorCallbackParams,
                        true
                    )),
                    "cancelUrl" => urlencode($this->context->link->getModuleLink(
                        "lemonway",
                        "validation",
                        $cancelCallbackParams,
                        true
                    )),
                    "registerCard" => (int)($this->registerCard() || $methodInstance->isSplitPayment())
                );

                $moneyInWeb = $kit->moneyInWebInit($params);

                // Error from API
                if (isset($moneyInWeb->E)) {
                    throw new Exception((string) $moneyInWeb->E->Msg, (int) $moneyInWeb->E->Code);
                }

                // If signed in and saved card
                if ($customer->id && $customer->isLogged() && isset($moneyInWeb->CARD) && $this->registerCard()) {
                    $card = $this->module->getCustomerCard($customer->id);

                    if (!$card) {
                        $card = array();
                    }

                    $card['id_customer'] = $customer->id;
                    $card['id_card'] = $moneyInWeb->CARD->ID;

                    $this->module->insertOrUpdateCard($customer->id, $card);
                }

                // If split payment
                if ($methodInstance->isSplitPayment()) {
                    if (!$moneyInWeb->CARD->ID) {
                        throw new Exception("Cannot save card token.");
                    }

                    // Save card id temporarily
                    ConfigurationCore::updateValue(
                        'LEMONWAY_CARD_ID_' . $customer->id .'_' . $cart->id,
                        $moneyInWeb->CARD->ID
                    );
                }
           

                $moneyInToken = $moneyInWeb->TOKEN;

                // Generate payment page link
                $paymentPage = LemonWayConfig::getWebkitUrl()
                    . '?moneyintoken=' . $moneyInToken
                    . '&p=' . urlencode(LemonWayConfig::getCssUrl())
                    . '&tpl=' . urlencode(LemonWayConfig::getTpl())
                    . '&lang=' . $this->getLang();

                Tools::redirect($paymentPage);
            } else {
                // Rebill
                $card = $this->module->getCustomerCard($customer->id);
                if (!isset($card["id_card"])) {
                    throw new Exception($this->module->l('Card not found.'), $customer->id);
                }

                // MoneyInWithCardId
                $params = array(
                    "wkToken" => $wkToken,
                    "wallet" => $wallet,
                    "cardId" => $card["id_card"],
                    "amountTot" => $amountTot,
                    "amountCom" => "0.00",
                    "comment" => $comment . " (MoneyInWithCardId)",
                    "autoCommission" => $autoCommission
                );

                $hpay = $kit->moneyInWithCardId($params);

                if ($hpay->INT_STATUS == 0) {
                    if (!$cart->OrderExists()) {
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
                            $hpay->MSG, // Message to attach to order
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

                            // Save deadlines
                            $profile->generateDeadlines(
                                $order,
                                $card["id_card"],
                                $methodInstance->getCode(),
                                true,
                                true
                            );
                        }
                    }

                    //The order has been placed so we redirect the customer on the confirmation page.
                    Tools::redirect(
                        $this->context->link->getPageLink(
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
                    throw new Exception($this->module->l('Payment error.'), $card["id_card"]);
                }
            }
        } catch (Exception $e) {
            $cart_id = isset($cart) ? $cart->id : null;
            PrestaShopLogger::addLog(
                "LemonWay::redirect - " . $e->getMessage() . " (" . $e->getCode() . ")",
                4,
                null,
                "Cart",
                $cart_id,
                true
            );
            array_push($this->errors, $e->getMessage() . " (" . $e->getCode() . ")");
            return $this->displayError();
        }
    }
}
