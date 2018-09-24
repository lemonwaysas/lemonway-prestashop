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
 * @author Lemon Way <it@lemonway.com>
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
function upgrade_module_1_4_1($module)
{
    $module->uninstallModuleTab('AdminMoneyOut');

    $os = new OrderState(Configuration::get(Lemonway::LEMONWAY_SPLIT_PAYMENT_OS));
    $os->logable = false;
    $os->template = "payment";
    $os->color = "#4169E1";
    $os->save();

    $query = "ALTER TABLE `" . _DB_PREFIX_ . "lemonway_wktoken`
        ADD `is_order_validated` tinyint(1) UNSIGNED  NOT NULL DEFAULT '0'
        ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;"

    Db::getInstance()->execute($query);

    return true;
}
