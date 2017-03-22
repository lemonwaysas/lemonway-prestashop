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
    
    public static function getProfiles($mustActive = true){
    	$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'lemonway_splitpayment_profile` sp '; 
    	
    	if($mustActive)
    		$sql .=	'WHERE sp.`active` = ' . pSQL(true);
    	
    	$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    	if (!$res) {
    		return array();
    	}
    	
    	return $res;
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

}
