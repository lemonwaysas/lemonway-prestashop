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

class MoneyOut extends ObjectModel
{
    public $id_lw_wallet;
    public $id_customer;
    public $id_employee;
    public $is_admin;
    public $id_lw_iban;
    public $prev_bal;
    public $new_bal;
    public $iban;
    public $amount_to_pay;
    public $date_add;
    public $date_upd;
    
    /**
    * @see ObjectModel::$definition
    */
    public static $definition = array(
        'table' => 'lemonway_moneyout',
        'primary' => 'id_moneyout',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => array(
            'id_lw_wallet' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required'=>true
            ),
            'id_customer' => array(
                'type' => self::TYPE_INT,
                'required'=>false
            ),
            'id_employee' => array(
                'type' => self::TYPE_INT,
                'required'=>false
            ),
            'is_admin' => array(
                'type' => self::TYPE_INT,
                'required'=>false
            ),
            'id_lw_iban' => array(
                'type' => self::TYPE_INT,
                'required'=>true
            ),
            'prev_bal' => array(
                'type' => self::TYPE_FLOAT,
                'isFloat' => 'isPrice',
                'required'=>true
            ),
            'new_bal' => array(
                'type' => self::TYPE_FLOAT,
                'isFloat' => 'isPrice',
                'required'=>true
            ),
            'iban' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required'=>true
            ),
            'amount_to_pay' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required'=>true
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ),
        ),
    );

    /**
     * Get customer maoneyout
     *
     * @param int $id_customer Customer|Employee id
     * @param bool $is_admin 
     * @param int $limit
     * @return array MoneyOut $moneyouts
     */
    public static function getCustomerMoneyout($id_customer, $is_admin = false, $limit = 0)
    {
        $field_owner = 'id_customer';
        
        if ($is_admin) {
            $field_owner = 'id_employee';
        }

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'lemonway_moneyout` wt WHERE wt.`' . pSQL($field_owner) . '` = ';
        $sql .= (int)pSQL($id_customer) . ' ORDER BY wt.`date_add` DESC';
        
        if ($limit > 0) {
            $sql .= " LIMIT 0, " . pSQL((int)$limit);
        }

        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!$res) {
            return array();
        }

        return $res;
    }
}
