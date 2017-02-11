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

class LemonWayConfig
{
    public static function isTestMode()
    {
        return (bool)Configuration::get('LEMONWAY_IS_TEST_MODE', null);
    }
    
    public static function getDirectkitUrl()
    {
        $url = Configuration::get('LEMONWAY_DIRECTKIT_URL', null);
        if (LemonWayConfig::isTestMode()) {
            $url = Configuration::get('LEMONWAY_DIRECTKIT_URL_TEST', null);
        }

        return rtrim($url, '/');
    }
    
    public static function getWebkitUrl()
    {
        $url = Configuration::get('LEMONWAY_WEBKIT_URL', null);
        if (LemonWayConfig::isTestMode()) {
            $url = Configuration::get('LEMONWAY_WEBKIT_URL_TEST', null);
        }

        return rtrim($url, '/');
    }
    
    public static function getWalletMerchantId()
    {
        return Configuration::get('LEMONWAY_MERCHANT_ID', null);
    }
    
    public static function getApiLogin()
    {
        return Configuration::get('LEMONWAY_API_LOGIN', null);
    }
    
    public static function getApiPassword()
    {
        return Configuration::get('LEMONWAY_API_PASSWORD', null);
    }
    
    public static function getCssUrl()
    {
        return Configuration::get('LEMONWAY_CSS_URL', null);
    }
    
    public static function getOneclicEnabled()
    {
        return Configuration::get('LEMONWAY_ONECLIC_ENABLED', null);
    }
}
