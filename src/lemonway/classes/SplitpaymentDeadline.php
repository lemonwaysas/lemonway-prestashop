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

class SplitpaymentDeadline extends ObjectModel
{

	const MAX_ATTEMPTS = 3;
	
	const STATUS_PENDING = 'pending';
	const STATUS_FAILED = 'failed';
	const STATUS_COMPLETE = 'complete';
	


	public $id_order;
	public $customer_id;
	public $id_splitpayment_profile;
	public $token;
	public $total_amount;
	public $amount_to_pay;
	public $date_to_pay;
	public $method_code;
	public $attempts;
	public $status;
	
	/** @var string Object creation date */
	public $date_add;
	
	/** @var string Object last modification date */
	public $date_upd;


	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
			'table' => 'lemonway_splitpayment_deadline',
			'primary' => 'id_profile',
			'multilang' => false,
			'multilang_shop' => false,
			'fields' => array(
					'id_order' => array(
							'type' => self::TYPE_INT,
							'validate' => 'isUnsignedId',
							'required' => true
					),
					'id_customer' => array(
							'type' => self::TYPE_INT,
							'validate' => 'isUnsignedId',
							'required' => true
					),
					'id_splitpayment_profile' => array(
							'type' => self::TYPE_INT,
							'validate' => 'isUnsignedId',
							'required' => true
					),
					'token' => array(
							'type' => self::TYPE_STRING,
							'required'=>true,
					),
					'total_amount' => array(
							'type' => self::TYPE_FLOAT,
							'validate' => 'isPrice',
							'required' => true
					),
					'amount_to_pay' => array(
							'type' => self::TYPE_FLOAT,
							'validate' => 'isPrice',
							'required' => true
					),
					'date_to_pay' => array(
							'type' => self::TYPE_DATE,
							'required'=>true,
							'validate' => 'isDate'
					),
					'method_code' => array(
							'type' => self::TYPE_STRING,
							'required'=>true
					),
					'attempts' => array(
							'type' => self::TYPE_INT,
							'required'=>false,
							'validate' => 'isInt'
					),
					'status' => array(
							'type' => self::TYPE_STRING,
							'required'=>true
					),
					'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
					'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			),
	);


	/**
	 * Getter for statues
	 *
	 * @param bool $withLabels
	 * @return array
	 */
	public static function getStatues($withLabels = true)
	{
		$statues = array(
				self::STATUS_COMPLETE,
				self::STATUS_FAILED,
				self::STATUS_PENDING,
		);

		if ($withLabels) {
			$result = array();
			foreach ($statues as $status) {
				$result[] = array('value'=>$status,'name'=>self::getStatusLabel($status));
			}
			return $result;
		}
		return $statues;
	}

	/**
	 * Render label for specified status
	 *
	 * @param string $status
	 */
	public static function getStatusLabel($status)
	{
		switch ($status) {
			case self::STATUS_COMPLETE:  return self::l('Complete');
			case self::STATUS_FAILED: return self::l('Failed');
			case self::STATUS_PENDING: return self::l('Pending');
		}
		return $status;
	}

	public static function l($string){
		return Translate::getModuleTranslation('lemonway',$string,'splitpaymentdeadline');
	}

	
}
