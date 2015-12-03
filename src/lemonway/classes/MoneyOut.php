<?php
class MoneyOut extends ObjectModel{
	
	
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
					'id_lw_wallet' =>    array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
					'id_customer' =>            array('type' => self::TYPE_INT,'required'=>false),
					'id_employee' =>            array('type' => self::TYPE_INT,'required'=>false),
					'is_admin' =>            array('type' => self::TYPE_INT,'required'=>false),
					'id_lw_iban' =>         array('type' => self::TYPE_INT,'required'=>true),
					'prev_bal' =>            array('type' => self::TYPE_FLOAT,'validate' => 'isPrice','required'=>true),
					'new_bal' =>            array('type' => self::TYPE_FLOAT,'validate' => 'isPrice','required'=>true),
					'iban' =>    array('type' => self::TYPE_STRING, 'validate' => 'isGenericName','required'=>true),
					'amount_to_pay' =>            array('type' => self::TYPE_FLOAT,'validate' => 'isPrice','required'=>true),
					'date_add' =>                    array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
					'date_upd' =>                    array('type' => self::TYPE_DATE, 'validate' => 'isDate'),

			),
	);
}