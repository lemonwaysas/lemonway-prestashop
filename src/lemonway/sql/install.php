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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lemonway_oneclic` (
    `id_oneclic` int(11) NOT NULL AUTO_INCREMENT,
	`id_customer` int(11) NOT NULL,
	`id_card` int(11) NOT NULL,
	`card_num` varchar(30) NOT NULL,
	`card_exp`  varchar(8) NOT NULL DEFAULT \'\',
	`card_type` varchar(20) NOT NULL DEFAULT \'\',
	`date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY  (`id_oneclic`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lemonway_moneyout` (
    `id_moneyout` int(11) NOT NULL AUTO_INCREMENT,
	`id_lw_wallet` varchar(255) NOT NULL,
	`id_customer` int(11) NOT NULL DEFAULT 0,
	`id_employee` int(11) NOT NULL DEFAULT 0,
	`is_admin` tinyint(1) NOT NULL DEFAULT 0,
	`id_lw_iban` int(11) NOT NULL,
	`prev_bal` decimal(20,6) NOT NULL,
	`new_bal`  decimal(20,6) NOT NULL,
	`iban` varchar(34) NOT NULL,
	`amount_to_pay`  decimal(20,6) NOT NULL,
	`date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY  (`id_moneyout`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lemonway_iban` (
    `id_iban` int(11) NOT NULL AUTO_INCREMENT,
	`id_lw_iban` int(11) NOT NULL,
	`id_customer` int(11) NOT NULL,
	`id_wallet` varchar(255) NOT NULL,
	`holder` varchar(100) NOT NULL,
	`iban` varchar(34) NOT NULL,
	`bic` varchar(50) NOT NULL DEFAULT \'\',
	`dom1` text NOT NULL DEFAULT \'\',
	`dom2` text NOT NULL DEFAULT \'\',
	`comment` text NOT NULL DEFAULT \'\',
	`id_status` int(2) DEFAULT NULL,
	`date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY  (`id_iban`),
	UNIQUE KEY (`id_lw_iban`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."lemonway_wallet` (
  `id_wallet` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Wallet ID',
  `id_lw_wallet` varchar(255) NOT NULL COMMENT 'Lemonway Wallet ID',
  `id_customer` int(11) NOT NULL COMMENT 'Customer ID',
  `is_admin` smallint(6) NOT NULL COMMENT 'Is Admin',
  `customer_email` varchar(255) NOT NULL COMMENT 'Email',
  `customer_prefix` varchar(100) NOT NULL DEFAULT '' COMMENT 'Prefix',
  `customer_firstname` varchar(255) NOT NULL COMMENT 'Firstname',
  `customer_lastname` varchar(255) NOT NULL COMMENT 'Lastname',
  `billing_address_street` varchar(255) DEFAULT NULL COMMENT 'Street',
  `billing_address_postcode` varchar(255) DEFAULT NULL COMMENT 'Postcode',
  `billing_address_city` varchar(255) DEFAULT NULL COMMENT 'City',
  `billing_address_country` varchar(2) DEFAULT NULL COMMENT 'Country',
  `billing_address_phone` varchar(255) DEFAULT NULL COMMENT 'Phone Number',
  `billing_address_mobile` varchar(255) DEFAULT NULL COMMENT 'Mobile Number',
  `customer_dob` datetime DEFAULT NULL COMMENT 'Dob',
  `is_company` smallint(6) DEFAULT NULL COMMENT 'Is company',
  `company_name` varchar(255) NOT NULL COMMENT 'Company name',
  `company_website` varchar(255) NOT NULL COMMENT 'Company website',
  `company_description` text COMMENT 'Company description',
  `company_id_number` varchar(255) DEFAULT NULL COMMENT 'Company ID number',
  `is_debtor` smallint(6) DEFAULT NULL COMMENT 'Is debtor',
  `customer_nationality` varchar(2) DEFAULT NULL COMMENT 'Nationality',
  `customer_birth_city` varchar(255) DEFAULT NULL COMMENT 'City of Birth',
  `customer_birth_country` varchar(2) DEFAULT NULL COMMENT 'Birth country',
  `payer_or_beneficiary` int(11) DEFAULT NULL COMMENT 'Payer or beneficiary',
  `is_onetime_customer` smallint(6) NOT NULL COMMENT 'Is One time customer',
  `is_default` smallint(6) NOT NULL COMMENT 'Is default',
  `status` smallint(6) DEFAULT NULL COMMENT 'Enabled',
  `date_add` datetime NOT NULL COMMENT 'Wallet Creation Time',
  `date_upd` datetime NOT NULL COMMENT 'Wallet Modification Time',
  PRIMARY KEY (`id_wallet`),
  UNIQUE KEY (`id_lw_wallet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Wallet Table' ;";

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lemonway_wktoken` (
			    `id_cart_wktoken` int(11) NOT NULL AUTO_INCREMENT,
				`id_cart` int(11) NOT NULL,
				`wktoken` varchar(255) NOT NULL,
			    PRIMARY KEY  (`id_cart_wktoken`),
   				UNIQUE KEY `wktoken` (`wktoken`),
   				UNIQUE KEY `id_cart` (`id_cart`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
