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
* Create wkToken table
* Used to keep card's token
* 
* @return boolean
*/
function upgrade_module_1_1_1()
{
    $query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lemonway_wktoken` (
    `id_cart_wktoken` int(11) NOT NULL AUTO_INCREMENT,
    `id_cart` int(11) NOT NULL,
    `wktoken` varchar(255) NOT NULL,
    PRIMARY KEY (`id_cart_wktoken`),
    UNIQUE KEY `wktoken` (`wktoken`),
    UNIQUE KEY `id_cart` (`id_cart`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    if (Db::getInstance()->execute($query) == false) {
        return false;
    }

    return true;
}
