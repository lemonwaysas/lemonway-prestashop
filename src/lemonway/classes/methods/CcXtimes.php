<?php
require_once 'Cc.php';
require_once _PS_MODULE_DIR_ . 'lemonway/classes/SplitpaymentProfile.php';

class CcXtimes extends Cc{
	protected $code = 'cc_xtimes';
	protected $splitpaymentProfiles = null;
	
	protected function prepareData(){
		parent::prepareData();

		$this->data['splitpayments_profiles'] = $this->getSplitpaymentProfiles();
		$this->data['splitpayments_profiles_length'] = count($this->data['splitpayments_profiles']);
		
		return $this;

	}
	
	public function getSplitpaymentProfiles(){
		if(is_null($this->splitpaymentProfiles)){
			$this->splitpaymentProfiles = SplitpaymentProfile::getProfiles();
		}
		return $this->splitpaymentProfiles;
	}
}