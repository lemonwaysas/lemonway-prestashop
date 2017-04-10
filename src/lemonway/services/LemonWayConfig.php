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
    
    public static function getOneclicEnabled($method)
    {
        return Configuration::get('LEMONWAY_' . strtoupper($method) . '_ONECLIC_ENABLED', null);
    }
}
