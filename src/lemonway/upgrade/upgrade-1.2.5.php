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

if (!defined('_PS_VERSION_')) {
    exit;
}


/**
 * Create Split payment tables
 * Profile is used to create split payment profile
 * Deadline keep all payment deadline
 * 
 * @return boolean
 */
function upgrade_module_1_2_5($module)
{
	$query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lemonway_splitpayment_profile` (
				`id_profile` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`name` varchar(150) NOT NULL,
				`period_unit` varchar(30) NOT NULL,
				`period_frequency` int(10) UNSIGNED NOT NULL ,
				`period_max_cycles` int(10) UNSIGNED NOT NULL ,
				`active` tinyint(1) UNSIGNED  NOT NULL DEFAULT \'1\',
				PRIMARY KEY  (`id_profile`)
    		) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    		
	    	CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lemonway_splitpayment_deadline` (
				`id_splitpayment` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`id_order` int(10) UNSIGNED NOT NULL ,
				`customer_id` int(10) UNSIGNED NOT NULL ,
				`token` text NOT NULL ,
				`total_amount` decimal(12,4) NOT NULL,
				`amount_to_pay` decimal(12,4) NOT NULL,
				`date_to_pay` datetime NOT NULL ,
				`method_code` varchar(150) NOT NULL,
				`attempts` int(4) UNSIGNED NOT NULL DEFAULT \'0\' ,
				`status` varchar(60) NOT NULL DEFAULT \'pending\',
				PRIMARY KEY  (`id_splitpayment`)
    		) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

	if (Db::getInstance()->execute($query) == false) {
		return false;
	}
	
	addAdminTab($module);
	updateNewConfigurationKeyValue();
	
	$module->addStatusSplitpayment();

	return true;
}

function addAdminTab($module){
	
	$adminLemonwayId = Db::getInstance()->getValue(
			"SELECT `id_tab` FROM " . _DB_PREFIX_ . "tab WHERE `class_name`='AdminLemonway'"
			);
	
	$translationsAdminSplitpaymentProfile = array(
			'en'=>'Split payment profile',
			'fr'=>'Profil de paiement en plusieurs fois'
	);
	
	$module->installModuleTab('AdminSplitpaymentProfile', $translationsAdminSplitpaymentProfile, $adminLemonwayId);
	
}

function updateNewConfigurationKeyValue(){
	$oldconf = Configuration::get('LEMONWAY_ONECLIC_ENABLED');
	Configuration::updateValue('LEMONWAY_CREDITCARD_ONECLIC_ENABLED', $oldconf);
}
