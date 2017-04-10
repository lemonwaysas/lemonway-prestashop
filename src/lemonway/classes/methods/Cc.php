<?php
require_once 'Method.php';
class Cc extends Method{
	
	protected $code= 'cc';
	protected $template = 'creditcard.tpl';
	
	
	protected function prepareData(){

		/* @var $customer CustomerCore */
		$customer = $this->context->customer;
		 
		$card_num = "";
		$card_type = "";
		$card_exp = "";
		$card = $this->module->getCustomerCard($customer->id);
		 
		if ($card) {
			$card_num = $card['card_num'];
			$card_type = $card['card_type'];
			$card_exp = $card['card_exp'];
		}
		 
		$customer_has_card = $card && !empty($card_num);
		$this->data = array(	'oneclic_allowed' => LemonWayConfig::getOneclicEnabled($this->code) && $customer->isLogged(),
				'customer_has_card' => $customer_has_card,
				'card_num' => $card_num,
				'card_type' => $card_type,
				'card_exp' => $card_exp
					
		);
		return $this;
	}
}