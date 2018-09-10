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

function installSQL($module)
{
    PrestaShopLogger::addLog("Database installation for LemonWay.", 1, null, null, null, true);
    $sql = array();

    $sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "lemonway_oneclic` (
    	    `id_oneclic` int(11) NOT NULL AUTO_INCREMENT,
    		`id_customer` int(11) NOT NULL,
    		`id_card` int(11) NOT NULL,
    		`card_num` varchar(30) NOT NULL,
    		`card_exp`  varchar(8) NOT NULL DEFAULT '',
    		`card_type` varchar(20) NOT NULL DEFAULT '',
    		`date_add` datetime NOT NULL,
    	    `date_upd` datetime NOT NULL,
    	    PRIMARY KEY  (`id_oneclic`)
    	) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;";

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            PrestaShopLogger::addLog("Database installation failed.", 4, null, null, null, true);
            return false;
        }
    }

    $upgrade_path = _PS_MODULE_DIR_ . $module->name . '/upgrade/';

    if (file_exists($upgrade_path) && ($files = scandir($upgrade_path))) {
        // Read each file name
        foreach ($files as $file) {
            if (!in_array($file, array('.', '..', '.svn', 'index.php')) && preg_match('/\.php$/', $file)) {
                $tab = explode('-', $file);

                if (!isset($tab[1])) {
                    continue;
                }

                $file_version = basename($tab[1], '.php');

                require $upgrade_path . $file;
                $upgradeFunc = "upgrade_module_" . str_replace(".", "_", $file_version);

                if (function_exists($upgradeFunc)) {
                    $res = $upgradeFunc($module);
                    if (!$res) {
                        PrestaShopLogger::addLog("Database installation failed.", 4, null, null, null, true);
                        return false;
                    }
                }
            }
        }
    } else {
        return false;
    }

    return true;
}
