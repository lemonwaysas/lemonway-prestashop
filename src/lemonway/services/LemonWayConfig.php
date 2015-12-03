<?php
class LemonWayConfig{
	
	static function isTestMode(){
		return (bool)Configuration::get('LEMONWAY_IS_TEST_MODE', null);
	}
	
	static function getDirectkitUrl()
	{
		$url = Configuration::get('LEMONWAY_DIRECTKIT_URL', null);
		if(LemonWayConfig::isTestMode())
			$url = Configuration::get('LEMONWAY_DIRECTKIT_URL_TEST', null);
		
		return rtrim($url, '/');
	}
	
	static function getWebkitUrl()
	{
		$url = Configuration::get('LEMONWAY_WEBKIT_URL', null);
		if(LemonWayConfig::isTestMode())
			$url = Configuration::get('LEMONWAY_WEBKIT_URL_TEST', null);
	
		return rtrim($url, '/');
	}
	
	static function getWalletMerchantId(){
		return Configuration::get('LEMONWAY_MERCHANT_ID', null);
	}
	
	static function getApiLogin(){
		return Configuration::get('LEMONWAY_API_LOGIN', null);
	}
	
	static function getApiPassword(){
		return Configuration::get('LEMONWAY_API_PASSWORD', null);
	}
	
	static function getCommissionAmount(){
		return Configuration::get('LEMONWAY_COMMISSION_AMOUNT', null);
	}
	
	static function isAutoCommision(){
		return Configuration::get('LEMONWAY_IS_AUTO_COMMISSION', null);
	}
	
	static function getCssUrl(){
		return Configuration::get('LEMONWAY_CSS_URL', null);
	}
	
	static function getOneclicEnabled(){
		return Configuration::get('LEMONWAY_ONECLIC_ENABLED', null);
	}
}