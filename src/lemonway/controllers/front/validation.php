<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
*/

class LemonwayValidationModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        require_once _PS_MODULE_DIR_ . $this->module->name . '/services/LemonWayKit.php';
    }
    
    /**
    *
    * @var Operation
    */
    protected $moneyin_trans_details = null;
    
    /**
    * This class should be use by your Instant Payment
    * Notification system to validate the order remotely
    */
    public function postProcess()
    {
        /**
        * If the module is not active anymore, no need to process anything.
        */
        if (!$this->module->active) {
            die;
        }
        
        if ((Tools::isSubmit('response_wkToken') == false) || Tools::isSubmit('action') == false) {
            die;
        }

        $action = Tools::getValue('action');
        $cart_id = $this->module->getCartIdFromToken(Tools::getValue('response_wkToken'));
        
        if ($this->isGet()) { //Is redirection from Lemonway
            if ((Tools::isSubmit('secure_key') == false)) {
                die;
            }

            Tools::redirect($this->context->link->getModuleLink('lemonway', 'confirmation', array(
                'action' => $action,
                'secure_key' => Tools::getValue('secure_key'),
                'cart_id'=> $cart_id
            ), true));
        } elseif ($this->isPost()) { //Is instant payment notification
            //wait for GET redirection finish in front
            sleep(4);

            if (Tools::isSubmit('response_code') == false) {
                die;
            }
            
            $response_code = Tools::getValue('response_code');
            $amount = (float)Tools::getValue('response_transactionAmount');
            $register_card = (bool)Tools::getValue('register_card', false);

            /**
            * Restore the context from the $cart_id & the $customer_id to process the validation properly.
            */
            Context::getContext()->cart = new Cart((int)$cart_id);
            if (!Context::getContext()->cart->id) {
                die;
            }

            Context::getContext()->customer = new Customer((int)Context::getContext()->cart->id_customer);
            Context::getContext()->currency = new Currency((int)Context::getContext()->cart->id_currency);
            Context::getContext()->language = new Language((int)Context::getContext()->customer->id_lang);
            
            $secure_key = Context::getContext()->customer->secure_key;
            
            //Default status to error
            $payment_status = Configuration::get('PS_OS_ERROR');
            //Default message;
            $message = Tools::getValue('response_msg');

            if ($this->isValidOrder($action, $response_code) === true) {
                switch ($action)
                {
                    case 'return':
                        $payment_status = Configuration::get('PS_OS_PAYMENT');
                        $message = Tools::getValue('response_msg');

                        if (($customer_id = Context::getContext()->customer->id) && $register_card) {
                            $card = $this->module->getCustomerCard($customer_id);
                            if (!$card) {
                                $card = array();
                            }

                            $card['id_customer'] = $customer_id;
                            $card['card_num'] = $this->getMoneyInTransDetails()->EXTRA->NUM;
                            $card['card_type'] = $this->getMoneyInTransDetails()->EXTRA->TYP;
                            $card['card_exp'] = $this->getMoneyInTransDetails()->EXTRA->EXP;

                            $this->module->insertOrUpdateCard($customer_id, $card);
                        }

                        break;

                    case 'cancel':
                        $payment_status = Configuration::get('PS_OS_CANCELED');

                        /**
                        * Add a message to explain why the order has not been validated
                        */
                        $message = $this->module->l('Order cancel by customer.');
                        
                        break;

                    case 'error':
                    
                    default:
                }
            }

            $module_name = $this->module->displayName;
            $currency_id = (int)Context::getContext()->currency->id;
        } else {
            //@TODO throw error for not http method supported
            die;
        }

        if (!Context::getContext()->cart->OrderExists()) {
            $this->module->validateOrder(
                $cart_id,
                $payment_status,
                $amount,
                $module_name,
                $message,
                array(
                ),
                $currency_id,
                false,
                $secure_key
            );
            //Logger::AddLog('New order added.');
            die('New order added.');
        } else {
            $order_id = (int)Order::getOrderByCartId($cart_id);
            $history = new OrderHistory();
            $history->id_order = (int)$order_id;

            $amount_paid = Tools::ps_round((float)$amount, 2);
            $cart_total_paid = (float)Tools::ps_round(
                (float)Context::getContext()->cart->getOrderTotal(true, Cart::BOTH),
                2
            );
            if (number_format($cart_total_paid, _PS_PRICE_COMPUTE_PRECISION_)
                != number_format($amount_paid, _PS_PRICE_COMPUTE_PRECISION_)) {
                $id_order_state = Configuration::get('PS_OS_ERROR');
            } else {
                $id_order_state = Configuration::get('PS_OS_PAYMENT');
            }

            $history->changeIdOrderState($id_order_state, (int)$order_id);

            $history->save();

            die('Order updated.');
        }
    }

    /**
    * 
    * @return boolean|Operation
    */
    protected function getMoneyInTransDetails()
    {
        if (is_null($this->moneyin_trans_details)) {
            // Call directkit to get Webkit Token
            $params = array('transactionMerchantToken'=>Tools::getValue('response_wkToken'));
          
            // Call api to get transaction detail for this order
            /* @var $kit LemonWayKit */
            $kit = new LemonWayKit();

            try {
                $res = $kit->getMoneyInTransDetails($params);
            } catch (Exception $e) {
                Logger::AddLog($e->getMessage());
                throw $e;
            }

            if (isset($res->lwError)) {
                throw new Exception((string)$res->lwError->MSG, (int)$res->lwError->CODE);
            }

            $this->moneyin_trans_details = current($res->operations);
        }

        return $this->moneyin_trans_details;
    }

    protected function isValidOrder($action, $response_code)
    {
        if ($response_code != "0000") {
            return false;
        }

        $actionToStatus = array(
            "return"=>"3",
            "error"=>"0",
            "cancel"=>"0"
        );

        if (!isset($actionToStatus[$action])) {
            return false;
        }

        /* @var $operation Operation */
        $operation = $this->getMoneyInTransDetails();

        if ($operation) {
            if ($operation->STATUS == $actionToStatus[$action]) {
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
