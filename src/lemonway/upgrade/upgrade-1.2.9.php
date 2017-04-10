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
function upgrade_module_1_2_9($module)
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
	    		`order_reference` VARCHAR(9) NOT NULL,
				`id_customer` int(10) UNSIGNED NOT NULL ,
	    		`id_splitpayment_profile` int(10) UNSIGNED NOT NULL ,
				`token` text NOT NULL ,
				`total_amount` decimal(12,4) NOT NULL,
				`amount_to_pay` decimal(12,4) NOT NULL,
				`date_to_pay` datetime NOT NULL ,
				`method_code` varchar(150) NOT NULL,
				`attempts` int(4) UNSIGNED NOT NULL DEFAULT \'0\' ,
				`status` varchar(60) NOT NULL DEFAULT \'pending\',
	    		`paid_at` datetime,
	    		`date_add` datetime NOT NULL,
  				`date_upd` datetime NOT NULL,
				PRIMARY KEY  (`id_splitpayment`)
    		) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

	if (Db::getInstance()->execute($query) == false) {
		return false;
	}
	
	
	
	updateNewConfigurationKeyValue();
	
	$module->addStatusSplitpayment();
	
	$translationsAdminSplitpaymentProfile = array(
			'en'=>'Split payment profile',
			'fr'=>'Profil de paiement en plusieurs fois'
	);
	
	addAdminTab($module,'AdminSplitpaymentProfile', $translationsAdminSplitpaymentProfile); 
	
	$translationsAdminSplitpaymentDeadline = array(
			'en'=>'Split payment deadline',
			'fr'=>'Échéances de paiement en plusieurs fois'
	);
	
	addAdminTab($module,'AdminSplitpaymentDeadline', $translationsAdminSplitpaymentDeadline);
	
	$module->registerHook('actionAdminSplitpaymentDeadlineListingResultsModifier');

	return true;
}

function addAdminTab($module,$tabClass, $translations){
	
	$adminLemonwayId = Db::getInstance()->getValue(
			"SELECT `id_tab` FROM " . _DB_PREFIX_ . "tab WHERE `class_name`='AdminLemonway'"
			);
	
	
	$module->installModuleTab($tabClass, $translations, $adminLemonwayId,$module->name);
	
}

function updateNewConfigurationKeyValue(){
	$oldconf = Configuration::get('LEMONWAY_ONECLIC_ENABLED');
	Configuration::updateValue('LEMONWAY_CREDITCARD_ONECLIC_ENABLED', $oldconf);
}
