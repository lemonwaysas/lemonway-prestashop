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

class WalletCore extends ObjectModel
{
    public $id_wallet;
    public $id_lw_wallet;
    public $id_customer;
    public $is_admin;
    public $customer_email;
    public $customer_prefix;
    public $customer_firstname;
    public $customer_lastname;
    public $billing_address_street;
    public $billing_address_postcode;
    public $billing_address_city;
    public $billing_address_country;
    public $billing_address_phone;
    public $billing_address_mobile;
    public $customer_dob;
    public $is_company;
    public $company_name;
    public $company_website;
    public $company_description;
    public $company_id_number;
    public $is_debtor = 0;
    public $customer_nationality;
    public $customer_birth_city;
    public $customer_birth_country;
    public $payer_or_beneficiary = 0;
    public $is_onetime_customer = 0;
    public $is_default = 1;
    public $status;
    public $date_add;
    public $date_upd;

    /**
    * @see ObjectModel::$definition
    */
    public static $definition = array(
        'table' => 'lemonway_wallet',
        'primary' => 'id_wallet',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => array(
            'id_lw_wallet' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'id_customer' => array(
                'type' => self::TYPE_INT,
                'required'=>true
            ),
            'is_admin' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required'=>false
            ),
            'customer_email' => array(
                'type' => self::TYPE_STRING,
                'required'=>true,
                'validate' => 'isEmail'
            ),
            'customer_prefix' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required'=>false
            ),
            'customer_firstname' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isName',
                'required'=>true, 'size'=>32
            ),
            'customer_lastname' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isName',
                'required'=>true,
                'size'=>32
            ),
            'billing_address_street' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isAddress',
                'size' => '128',
                'required'=>false
            ),
            'billing_address_postcode' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isPostCode',
                'required'=>false,
                'size'=>12
            ),
            'billing_address_city' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCityName',
                'required'=>false,
                'size' => 64
            ),
            'billing_address_country' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required'=>false
            ),
            'billing_address_phone' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isPhoneNumber',
                'required'=>false,
                'size'=>32
            ),
            'billing_address_mobile' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isPhoneNumber',
                'required'=>false,
                'size'=>32
            ),
            'customer_dob' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isBirthDate'
            ),
            'is_company' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool'),
            'company_name' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required'=>false
            ),
            'company_website' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isUrl',
                'required'=>false
            ),
            'company_description' => array(
                'type' => self::TYPE_STRING,
                'required'=>false
            ),
            'company_id_number' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isSiret',
                'required'=>false
            ),
            'is_debtor' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required'=>false
            ),
            'customer_nationality' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required'=>false
            ),
            'customer_birth_city' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCityName',
                'required'=>false
            ),
            'customer_birth_country' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required'=>false
            ),
            'payer_or_beneficiary' => array(
                'type' => self::TYPE_INT,
                'required'=>false
            ),
            'is_onetime_customer' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required'=>false),
            'is_default' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required'=>false
            ),
            'status' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isGenericName',
                'required'=>false
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

    public function getFieldsWithoutValidation()
    {
        $fields = $this->formatFields(self::FORMAT_COMMON);

        // For retro compatibility
        if (Shop::isTableAssociated($this->def['table'])) {
            $fields = array_merge($fields, $this->getFieldsShop());
        }

        // Ensure that we get something to insert
        if (!$fields && isset($this->id) && Validate::isUnsignedId($this->id)) {
            $fields[$this->def['primary']] = $this->id;
        }

        return $fields;
    }

    public function getByCustomerId($id_customer)
    {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'lemonway_wallet` lw ' .
        'WHERE lw.`id_customer` = ' . (int)pSQL($id_customer);
        $result = Db::getInstance()->getRow($query);

        if (!$result) {
            return false;
        }

        $this->id = $result['id_wallet'];
        foreach ($result as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        
        return $this;
    }
}
