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

require_once 'SplitpaymentDeadline.php';

class SplitpaymentProfile extends ObjectModel
{

	/**
	 * Period units
	 *
	 * @var string
	 */
	const PERIOD_UNIT_DAY = 'day';
	const PERIOD_UNIT_WEEK = 'week';
	const PERIOD_UNIT_SEMI_MONTH = 'semi_month';
	const PERIOD_UNIT_MONTH = 'month';
	const PERIOD_UNIT_YEAR = 'year';


	public $name;
	public $period_unit;
	public $period_frequency;
	public $period_max_cycles;
	public $active = true;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
			'table' => 'lemonway_splitpayment_profile',
			'primary' => 'id_profile',
			'multilang' => false,
			'multilang_shop' => false,
			'fields' => array(
					'name' => array(
							'type' => self::TYPE_STRING,
							'required'=>true,
							'validate' => 'isGenericName'
					),
					'period_unit' => array(
							'type' => self::TYPE_STRING,
							'required'=>true,
					),
					'period_frequency' => array(
							'type' => self::TYPE_INT,
							'required'=>true,
							'validate' => 'isInt'
					),
					'period_max_cycles' => array(
							'type' => self::TYPE_INT,
							'required'=>true,
							'validate' => 'isInt'
					),
					'active' => array(
							'type' => self::TYPE_BOOL,
							'validate' => 'isBool',
							'required'=>false
					)
			),
	);

	public static function getProfiles($mustActive = true,$objCollection = false){
		if(!$objCollection){
			
		$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'lemonway_splitpayment_profile` sp ';
		 
		if($mustActive)
			$sql .=	'WHERE sp.`active` = ' . pSQL(true);
			 
			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
			if (!$res) {
				return array();
			}
			 
			return $res;
		}
		else{
			$profiles = new PrestaShopCollection('SplitpaymentProfile');
			if($mustActive) $profiles->where('active', '=', pSQL(true));
			return $profiles;
		}
	}


	/**
	 * Getter for available period units
	 *
	 * @param bool $withLabels
	 * @return array
	 */
	public static function getAllPeriodUnits($withLabels = true)
	{
		$units = array(
				self::PERIOD_UNIT_DAY,
				self::PERIOD_UNIT_WEEK,
				self::PERIOD_UNIT_SEMI_MONTH,
				self::PERIOD_UNIT_MONTH,
				self::PERIOD_UNIT_YEAR
		);

		if ($withLabels) {
			$result = array();
			foreach ($units as $unit) {
				$result[] = array('value'=>$unit,'name'=>self::getPeriodUnitLabel($unit));
			}
			return $result;
		}
		return $units;
	}

	/**
	 * Render label for specified period unit
	 *
	 * @param string $unit
	 */
	public static function getPeriodUnitLabel($unit)
	{
		switch ($unit) {
			case self::PERIOD_UNIT_DAY:  return self::l('Day');
			case self::PERIOD_UNIT_WEEK: return self::l('Week');
			case self::PERIOD_UNIT_SEMI_MONTH: return self::l('Two Weeks');
			case self::PERIOD_UNIT_MONTH: return self::l('Month');
			case self::PERIOD_UNIT_YEAR:  return self::l('Year');
		}
		return $unit;
	}

	public static function l($string){
		return Translate::getModuleTranslation('lemonway',$string,'splitpaymentprofile');
	}
	
	
	/**
	 *
	 * @param OrderCore $order
	 * @param string $token
	 * @param string $methodCode
	 * @param boolean $completeFirst valid the first deadline
	 * @param boolean $add save deadlines in db
	 *
	 * @return array
	 */
	public function generateDeadlines($order,$token,$methodCode,$completeFirst = false,$add = false){
	
		$deadlines = array();
		
		if(!$this->id) return $deadlines;
		
	
		$splitpayments = $this->splitPaymentAmount($order->total_paid);
	
		foreach ($splitpayments as $index=>$split){
			
			$completeFirst = $completeFirst && ($index == 0);
				
			$splitDealine =  new SplitpaymentDeadline();
			$splitDealine->id_order = $order->id;
			$splitDealine->order_reference = $order->reference;
			$splitDealine->id_splitpayment_profile = $this->id;
			$splitDealine->id_customer = $order->id_customer;
				
			$splitDealine->amount_to_pay = $split['amountToPay'];
			$splitDealine->date_to_pay = $split['dateToPay'];
			$splitDealine->attempts = $completeFirst ? 1 : 0;
			
			if($completeFirst) $splitDealine->paid_at = date('Y-m-d H:i:s');
	
				
			$splitDealine->method_code = $methodCode;
			$splitDealine->status = $completeFirst ? SplitpaymentDeadline::STATUS_COMPLETE : SplitpaymentDeadline::STATUS_PENDING;
			$splitDealine->token = $token;
			$splitDealine->total_amount = $order->total_paid;
				
			$deadlines[] = $splitDealine;
				
			if($add){
				if(!$splitDealine->add()){
					Logger::AddLog('Error during split payment deadline records for order ' . $order->id);
				}
			}
		}

		return $deadlines;
	}

	/**
	 * Return Amount splitted
	 * @param float $amount
	 */
	public function splitPaymentAmount($amount,$asJson=false)
	{
		$paymentsSplit = array();

		$maxCycles = (int)$this->period_max_cycles;
		$periodFrequency = (int)$this->period_frequency;
		$periodUnit = $this->period_unit;

		$todayDate = new \DateTime();

		if($maxCycles < 1){
			throw new Exception("Period max cycles is equals zero or negative for Payment Profile ID: ".$this->getId());
		}

			
		$part = (int)($amount / $maxCycles);
		$fmod = fmod($amount, $maxCycles);
			
		for ($i=0;$i<=($maxCycles-1);$i++)
		{
			$j = $i - 1;
			$todayClone = clone $todayDate;
			$format = 'Y-m-d';
			$freqByCycles = $periodFrequency+$j;
			$interval = null;

			switch ($periodUnit)
			{
				case self::PERIOD_UNIT_MONTH:
					{
						$interval = new \DateInterval("P" . $freqByCycles . "M");
						break;
					}
				case self::PERIOD_UNIT_DAY:
					{
						$interval = new \DateInterval("P" . $freqByCycles . "D");
						break;
					}
				case self::PERIOD_UNIT_SEMI_MONTH:
					{
						$interval = new \DateInterval("P" .$freqByCycles * 2 . "W");
						break;
					}
				case self::PERIOD_UNIT_WEEK:
					{
						$interval = new \DateInterval("P" .$freqByCycles . "W");
						break;
					}
				case self::PERIOD_UNIT_YEAR:
					{
						$interval = new \DateInterval("P" .$freqByCycles . "Y");
						break;
					}
			}

			$dateToPay = $todayClone->add($interval)->format($format);

			$amountToPay = $i==0 ? ($part + $fmod) : $part;
			$paymentsSplit[] = array('dateToPay'=>$dateToPay,'amountToPay'=>$amountToPay);
		}
			
		return $asJson ? json_encode($paymentsSplit) : $paymentsSplit;

	}

}
