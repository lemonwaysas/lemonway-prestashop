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
    public function __construct()
    {
        parent::__construct();
        require_once _PS_MODULE_DIR_ . $this->module->name . '/services/LemonWayKit.php';
    }

    /**
     * This class should be use by your Instant Payment
     * Notification system to validate the order remotely
     */
    public function postProcess()
    {
        // If the module is not active anymore, no need to process anything.
        if (!$this->module->active) {
            die;
        }

        if ((Tools::isSubmit('response_wkToken') == false) || Tools::isSubmit('action') == false) {
            die;
        }

        $action = Tools::getValue('action');
        $cart_id = $this->module->getCartIdFromToken(Tools::getValue('response_wkToken'));

        // Restore the context from the $cart_id & the $customer_id to process the validation properly.
        Context::getContext()->cart = new Cart((int) $cart_id);

        if (!Context::getContext()->cart->id) {
            die;
        }

        Context::getContext()->customer = new Customer((int) Context::getContext()->cart->id_customer);
        Context::getContext()->currency = new Currency((int) Context::getContext()->cart->id_currency);
        Context::getContext()->language = new Language((int) Context::getContext()->customer->id_lang);

        /* @var $methodInstance Method */
        $methodInstance = $this->module->methodFactory(Tools::getValue('payment_method'));
        $cart_total_paid = (float) Tools::ps_round(
            (float) Context::getContext()->cart->getOrderTotal(true, Cart::BOTH),
            2
        );

        $redirectParams = array(
            'action' => $action,
            'secure_key' => Tools::getValue('secure_key'),
            'cart_id' => $cart_id,
            'payment_method' => $methodInstance->getCode()
        );

        $profile = new SplitpaymentProfile();

        // If is X times method, we split the payment
        if ($methodInstance->isSplitPayment() &&
            ($splitPaypentProfileId = Tools::getValue('splitpayment_profile_id'))) {
            $profile = new SplitpaymentProfile($splitPaypentProfileId);

            if ($profile) {
                $splitpayments = $profile->splitPaymentAmount($cart_total_paid);
                $firstSplit = $splitpayments[0];
                $cart_total_paid = (float) Tools::ps_round((float) $firstSplit['amountToPay'], 2);

                // Add prodile Id to base callbackparamters
                $redirectParams['splitpayment_profile_id'] = $splitPaypentProfileId;
            } else {
                $this->addError($this->l('Split payment profile not found!'));
                return $this->displayError();
            }
        }

        if ($this->isGet()) { // Is redirection from Lemonway
            PrestaShopLogger::addLog("Lemon Way redirection: " . print_r($_GET, true));

            if ((Tools::isSubmit('secure_key') == false)) {
                die;
            }

            Tools::redirect($this->context->link->getModuleLink('lemonway', 'confirmation', $redirectParams, true));
        } elseif ($this->isPost()) { // Is instant payment notification
            PrestaShopLogger::addLog("Lemon Way IPN: " . print_r($_POST, true));

            if (Tools::isSubmit('response_code') == false) {
                die;
            }
            
            $response_code = Tools::getValue('response_code');
            $amount = (float) Tools::getValue('response_transactionAmount');
            $amount_paid = Tools::ps_round((float) $amount, 2);

            $register_card = (bool) Tools::getValue('register_card', false);

            $secure_key = Context::getContext()->customer->secure_key;

            // Default status to error
            $id_order_state = Configuration::get('PS_OS_ERROR');
            // Default message;
            $message = Tools::getValue('response_msg');

            if ($this->isValidOrder($action, $response_code) === true) {
                switch ($action) {
                    case 'return':
                        $id_order_state = Configuration::get('PS_OS_PAYMENT');

                        if ($methodInstance->isSplitPayment()) {
                            $id_order_state = Configuration::get(Lemonway::LEMONWAY_SPLIT_PAYMENT_OS);
                        }

                        $message = Tools::getValue('response_msg');

                        if (($customer_id = Context::getContext()->customer->id) && $register_card) {
                            $card = $this->module->getCustomerCard($customer_id);

                            if (!$card) {
                                $card = array();
                            }

                            $card['id_customer'] = $customer_id;
                            $card['card_num'] = $this->getMoneyInTransDetails()->TRANS->HPAY[0]->EXTRA->NUM;
                            $card['card_type'] = $this->getMoneyInTransDetails()->TRANS->HPAY[0]->EXTRA->TYP;
                            $card['card_exp'] = $this->getMoneyInTransDetails()->TRANS->HPAY[0]->EXTRA->EXP;

                            $this->module->insertOrUpdateCard($customer_id, $card);
                        }
                        break;
                    default:
                        break;
                }
            }
            
            // $module_name = $this->module->displayName;
            $currency_id = (int)Context::getContext()->currency->id;

            $isSameAmount = (
                number_format($cart_total_paid, _PS_PRICE_COMPUTE_PRECISION_) ===
                number_format($amount_paid, _PS_PRICE_COMPUTE_PRECISION_)
            );

            if (!$isSameAmount) {
                $id_order_state = Configuration::get('PS_OS_ERROR');
            }

            $order_id = (int)Order::getOrderByCartId($cart_id);

            if (!Context::getContext()->cart->OrderExists()) {
                $this->module->validateOrder(
                    $cart_id,
                    $id_order_state,
                    $amount_paid,
                    $methodInstance->getTitle(),
                    $message,
                    array(),
                    $currency_id,
                    false,
                    $secure_key
                );
                $order_id = Order::getOrderByCartId((int) $cart_id);

                if ($methodInstance->isSplitPayment()) {
                    $order_id = (int)Order::getOrderByCartId($cart_id); //Get new order id

                    /* @var $order OrderCore */
                    $order = new Order($order_id);

                    /* @var $invoiceCollection PrestaShopCollectionCore */
                    $invoiceCollection = $order->getInvoicesCollection();
                    $lastInvoice = $invoiceCollection->orderBy('date_add')->setPageNumber(1)->setPageSize(1)->getFirst();

                    $order->addOrderPayment(
                        $amount_paid,
                        $methodInstance->getTitle(),
                        Tools::getValue('response_transactionId'),
                        null,
                        null,
                        ($lastInvoice ? $lastInvoice : null)
                    );
                }
            }

            if ($methodInstance->isSplitPayment() && !$profile) {
                throw new Exception("Wrong data for split payment");
            }

            $order = new Order($order_id);

            if ($methodInstance->isSplitPayment()) {
                //$card = $this->module->getCustomerCard($order->id_customer);
                $cardKey = 'LEMONWAY_CARD_ID_' . $order->id_customer . '_' . $order->id_cart;
                $cardId = Configuration::get($cardKey);
                if ($cardId) {
                    //Save deadlines
                    $profile->generateDeadlines($order, $cardId, $methodInstance->getCode(), $this->isValidOrder($action, $response_code), $this->isValidOrder($action, $response_code));
                    ConfigurationCore::deleteByName($cardKey);
                } else {
                    throw new Exception($this->module->l("Card token not found"));
                }
            }

            try {
                $history = new OrderHistory();
                $history->id_order = (int)$order_id;

                $history->changeIdOrderState($id_order_state, $order, false);
                $history->save();
            } catch (Exception $e) {
                PrestaShopLogger::addLog($e->getMessage(), 4);
            }

            if ($methodInstance->isSplitPayment()) {
                /* @var $invoiceCollection PrestaShopCollectionCore */
                $invoiceCollection = $order->getInvoicesCollection();

                $lastInvoice = $invoiceCollection->orderBy('date_add')->setPageNumber(1)->setPageSize(1)->getFirst();

                try {
                    $order->addOrderPayment(
                        $amount_paid,
                        $methodInstance->getTitle(),
                        Tools::getValue('response_transactionId'),
                        null,
                        null,
                        $lastInvoice
                    );
                } catch (Exception $e) {
                    PrestaShopLogger::addLog($e->getMessage(), 4);
                }
            } else { //Update order payment
                foreach ($order->getOrderPaymentCollection() as $orderPayment) {
                    try {
                        $orderPayment->payment_method = $methodInstance->getTitle();
                        $orderPayment->update();
                    } catch (Exception $e) {
                        PrestaShopLogger::addLog($e->getMessage(), 4);
                    }
                }
            }

            $templateVars = array();
            $history->sendEmail($order, $templateVars);
        } else {
            //@TODO throw error for not http method supported
            die("HTTP Method not Allowed");
        }
    }

    protected function getMoneyInTransDetails()
    {
        // Call directkit to get Webkit Token
        $params = array('transactionMerchantToken' => Tools::getValue('response_wkToken'));

        // Call api to get transaction detail for this order
        /* @var $kit LemonWayKit */
        $kit = new LemonWayKit();

        try {
            $res = $kit->getMoneyInTransDetails($params);
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage(), 4);
            throw $e;
        }

        if (isset($res->E)) {
            throw new Exception((string) $res->E->Msg, (int) $res->E->Code);
        }

        return $res;
    }

    protected function isValidOrder($action, $response_code)
    {
        if ($response_code != "0000") {
            return false;
        }

        $actionToStatus = array(
            "return" => "3",
            "error" => "0",
            "cancel" => "0"
        );

        if (!isset($actionToStatus[$action])) {
            return false;
        }

        /* @var $operation Operation */
        $operation = $this->getMoneyInTransDetails();

        if ($operation) {
            if ($operation->TRANS->HPAY[0]->STATUS == $actionToStatus[$action]) {
                return true;
            }
        }

        return false;
    }

    protected function isGet()
    {
        return Tools::strtoupper($_SERVER['REQUEST_METHOD']) == 'GET';
    }

    protected function isPost()
    {
        return Tools::strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
    }
}
