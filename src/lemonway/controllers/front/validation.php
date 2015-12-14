<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class LemonwayValidationModuleFrontController extends ModuleFrontController
{
	
	public function __construct(){
		parent::__construct();
		require_once _PS_MODULE_DIR_.$this->module->name.'/services/LemonWayKit.php';
	}
	
	/**
	 *
	 * @var Operation
	 */
	protected $_moneyin_trans_details = null;
	
    /**
     * This class should be use by your Instant Payment
     * Notification system to validate the order remotely
     */
    public function postProcess()
    {
        /**
         * If the module is not active anymore, no need to process anything.
         */
        if ($this->module->active == false) {
            die;
        }
        
        
        
        if ((Tools::isSubmit('response_wkToken') == false) || Tools::isSubmit('action') == false) {
        	die;
        }
        
        $action = Tools::getValue('action');
        $cart_id = (int)Tools::getValue('response_wkToken');
        
        if($this->isGet())//IS redirection from lemonway
        {

        	if ((Tools::isSubmit('secure_key') == false)) {
        		die;
        	}

        	Tools::redirect($this->context->link->getModuleLink('lemonway', 'confirmation', array('action' => $action, 'secure_key' => Tools::getValue('secure_key'),'response_wkToken'=>$cart_id) , true));
        	
        }
        elseif($this->isPost())//Is instant payment notification
        {
        	//wait for GET redirection in front
        	//sleep(8);
        	
        	if(Tools::isSubmit('response_code') == false){
        		die;
        	}
        	
        	$response_code = Tools::getValue('response_code');
        	$amount = (float)Tools::getValue('response_transactionAmount');
        	$register_card = (bool)Tools::getValue('register_card',false);

        	/**
        	 * Restore the context from the $cart_id & the $customer_id to process the validation properly.
        	 */
        	Context::getContext()->cart = new Cart((int)$cart_id);
        	if(!Context::getContext()->cart->id)
        		die;
        	

        	Context::getContext()->customer = new Customer((int)Context::getContext()->cart->id_customer);
        	Context::getContext()->currency = new Currency((int)Context::getContext()->cart->id_currency);
        	Context::getContext()->language = new Language((int)Context::getContext()->customer->id_lang);
        	
        	$secure_key = Context::getContext()->customer->secure_key;
        	
        	//Default status to error
        	$payment_status = Configuration::get('PS_OS_ERROR');       	
        	//Default message;
        	$message = Tools::getValue('response_msg');

        	if ($this->isValidOrder($action,$response_code) === true) {
	        	switch ($action){
	        		case 'return':
	        			$payment_status = Configuration::get('PS_OS_PAYMENT');
	        			$message = Tools::getValue('response_msg');
	  
	        			if(($customer_id =Context::getContext()->customer->id) && $register_card)
	        			{
	        				$card = $this->module->getCustomerCard($customer_id);
	        				if(!$card)
	        					$card = array();
	        				
	        				$card['id_customer'] = $customer_id;
	        				$card['card_num'] = $this->GetMoneyInTransDetails()->EXTRA->NUM;
	        				$card['card_type'] = $this->GetMoneyInTransDetails()->EXTRA->TYP;
	        				$card['card_exp'] = $this->GetMoneyInTransDetails()->EXTRA->EXP;
							
	        				$this->module->insertOrUpdateCard($customer_id,$card);
	        				
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
        }
        else
        {
        	//@TODO throw error for not http method supported
        	die;
        }

        
        if (!Context::getContext()->cart->OrderExists())
        {
        	$this->module->validateOrder($cart_id, $payment_status, $amount, $module_name, $message, array(), $currency_id, false, $secure_key);
        	//Logger::AddLog('New order added.');
        	die('New order added.');
        	
        }
        else
        {
        	$order_id = (int)Order::getOrderByCartId($cart_id);
        	$history = new OrderHistory();
        	$history->id_order = (int)$order_id;
        	$history->changeIdOrderState(Configuration::get('PS_OS_PAYMENT'), (int)$order_id);
        	//Logger::AddLog('Order updated');
        	die('Order updated.');
  
        }

    }
    
    /**
     * 
     * @return boolean|Operation
     */
    protected function GetMoneyInTransDetails(){
    	if(is_null($this->_moneyin_trans_details))
    	{
    		
	    	//call directkit to get Webkit Token
	    	$params = array('transactionMerchantToken'=>Tools::getValue('response_wkToken'));
	    	
	    	//Call api to get transaction detail for this order
	    	/* @var $kit LemonWayKit */
	    	$kit = new LemonWayKit();
	    	
	    	try {
	    			
	    		$res = $kit->GetMoneyInTransDetails($params);

	    	
	    	} catch (Exception $e) {
	    		Logger::AddLog($e->getMessage());
	    		throw $e;
	    	}
	    	
	    	if (isset($res->lwError)){
	    		throw new Exception((string)$res->lwError->MSG, (int)$res->lwError->CODE);
	    	}
	    	
	    	$this->_moneyin_trans_details = current($res->operations);
	    	
    	}
    	return $this->_moneyin_trans_details;
    }

    protected function isValidOrder($action,$response_code)
    {

    	if($response_code != "0000")
    		return false;
    	
        $actionToStatus = array("return"=>"3","error"=>"0","cancel"=>"0");
		if(!isset($actionToStatus[$action]))
			return false;
		
		/* @var $operation Operation */
		$operation = $this->GetMoneyInTransDetails();
		 
		if($operation)
		{		
			if($operation->STATUS == $actionToStatus[$action])
				return true;
		}

		return false;
    }
    
    protected function isGet()
    {
    	return strtoupper($_SERVER['REQUEST_METHOD']) == 'GET';
    }
    
    protected function isPost(){
    	return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
    }
}
