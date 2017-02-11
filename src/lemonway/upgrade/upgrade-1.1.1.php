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
* This function updates your module from previous versions to the version 1.1,
* usefull when you modify your database, or register a new hook ...
* Don't forget to create one file per version.
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
