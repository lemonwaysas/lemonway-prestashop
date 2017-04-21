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

require_once 'Cc.php';
require_once _PS_MODULE_DIR_ . 'lemonway/classes/SplitpaymentProfile.php';

class CcXtimes extends Cc{
	protected $code = 'cc_xtimes';
	protected $splitpaymentProfiles = null;
	
	protected  $isSplitpayment = true;
	
	protected function prepareData(){
		parent::prepareData();

		$this->data['splitpayments_profiles'] = $this->getSplitpaymentProfiles(true,true);
		$this->data['splitpayments_profiles_length'] = count($this->data['splitpayments_profiles']);
		
		return $this;

	}
	
	public function getSplitpaymentProfiles($mustActive = true,$objCollection = false){
		if(is_null($this->splitpaymentProfiles)){
			$splitpaymentProfiles = SplitpaymentProfile::getProfiles($mustActive,$objCollection);
			
			//Remove splitpayments profiles not selected by Admin
			$selectedProfileIds = explode(",",$this->getConfig('SPLITPAYMENTS'));
			foreach ($splitpaymentProfiles as $key=>$sp){
				$spId = '';
				if(is_object($sp))
					$spId = $sp->id;
				else
					$spId = $sp['id_profile'];
				
				if(!in_array($spId,$selectedProfileIds)){
					unset($splitpaymentProfiles[$key]);
				}
			}
			$this->splitpaymentProfiles = $splitpaymentProfiles;
		}
		return $this->splitpaymentProfiles;
	}
}