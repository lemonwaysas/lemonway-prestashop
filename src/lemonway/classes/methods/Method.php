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
 * @author Kassim Belghait <kassim@sirateck.com>, PHAM Quoc Dat <dpham@lemonway.com>
 * @copyright  2017 Lemon way
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

abstract class Method{
	
	protected $code = null;
	protected $template = null;
	protected $data = array();
	
	protected $context=null;
	protected $module=null;
	
	protected  $isSplitpayment = false;
	
	
	public function __construct(){
		if(!$this->code){
			throw new Exception('You must to set code to your payment method!', 500);
		}
		
		if(!$this->template){
			throw new Exception('You must to define a template for your payment method!', 500);
		}
		
		$this->context = Context::getContext();
		$this->module =  Module::getInstanceByName('lemonway');
		
		$this->code = strtoupper($this->code);
	}
	
	protected function prepareData(){
		return $this;
	}
	
	public function getCode(){
		return $this->code;
	}
	
	public function isActive(){
		return $this->getConfig('enabled');
	}
	
	public  function getTitle(){
		return $this->getConfig('title');
	}
	
	public function isValid(){
		return $this->isActive() && $this->getTitle();
	}
	
	public function getData($key){
		$this->prepareData();
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}
	
	public function getTemplate(){
		$prefix = _PS_MODULE_DIR_;
		if($this->module->isVersion17())
		{
			$prefix = 'module:';
			$this->template = '17_' . $this->template;
		}
		return $prefix . 'lemonway/views/templates/front/methods/' . $this->template;
	}
	
	public function getConfig($key){
		return Configuration::get('LEMONWAY_' . $this->code . '_' . strtoupper($key));
	}
	
	/**
	 * @return Lemonway
	 */
	public function getModule(){
		return $this->module;
	}
	
	public function isSplitPayment(){
		return $this->isSplitpayment;
	}
	
	
	public function isAllowed(){
		 
		if(!$this->isActive()){
			return false;
		}
		 
		switch( $this->getCode()){
	
			case "creditcard_xtimes":
				if(!in_array(Tools::getValue('splitpayment_profile_id'),$this->getModule()->getSplitpaymentProfiles())){
					return false;
				}
				else{
					return true;
				}
				 
			default:
				return true;
	
		}
		 
		return false;
	}
}