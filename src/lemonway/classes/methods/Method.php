<?php
abstract class Method{
	
	protected $code = null;
	protected $template = null;
	protected $data = array();
	
	protected $context=null;
	protected $module=null;
	
	
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
		return _PS_MODULE_DIR_ . 'lemonway/views/templates/front/methods/' . $this->template;
	}
	
	public function getConfig($key){
		return Configuration::get('LEMONWAY_' . $this->code . '_' . strtoupper($key));
	}
	
	public function getModule(){
		return $this->module;
	}
	
}