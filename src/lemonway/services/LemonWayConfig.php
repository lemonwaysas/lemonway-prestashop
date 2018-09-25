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

class LemonWayConfig
{
    const LEMONWAY_DEFAULT_ENVIRONMENT = 'lwecommerce';

    const LEMONWAY_DIRECTKIT_FORMAT_URL_PROD = 'https://ws.lemonway.fr/mb/%s/prod/directkitjson2/service.asmx';
    const LEMONWAY_DIRECTKIT_FORMAT_URL_TEST = 'https://sandbox-api.lemonway.fr/mb/%s/dev/directkitjson2/service.asmx';
    const LEMONWAY_WEBKIT_FORMAT_URL_PROD = 'https://webkit.lemonway.fr/mb/%s/prod';
    const LEMONWAY_WEBKIT_FORMAT_URL_TEST = 'https://sandbox-webkit.lemonway.fr/%s/dev';

    private static function getEvironmentName()
    {
        $env_name = Configuration::get('CUSTOM_ENVIRONMENT_NAME', null);

        //If no custom environment we use lwecommerce
        if (empty($env_name)) {
            $env_name = self::LEMONWAY_DEFAULT_ENVIRONMENT;
        }

        return $env_name;
    }

    public static function isTestMode()
    {
        return (bool) Configuration::get('LEMONWAY_IS_TEST_MODE', null);
    }

    public static function is4EcommerceMode()
    {
        $env_name = Configuration::get('CUSTOM_ENVIRONMENT_NAME', null);

        // If no custom environment name so lwecommerce
        return (empty($env_name));
    }

    public static function getDirectkitUrl()
    {
        $env_name = LemonWayConfig::getEvironmentName();
        
        if (LemonWayConfig::isTestMode()) {
            $url = sprintf(self::LEMONWAY_DIRECTKIT_FORMAT_URL_TEST, $env_name);
        } else {
            $url = sprintf(self::LEMONWAY_DIRECTKIT_FORMAT_URL_PROD, $env_name);
        }

        return $url;
    }

    public static function getWebkitUrl()
    {
        $env_name = LemonWayConfig::getEvironmentName();

        if (LemonWayConfig::isTestMode()) {
            $url = sprintf(self::LEMONWAY_WEBKIT_FORMAT_URL_TEST, $env_name);
        } else {
            $url = sprintf(self::LEMONWAY_WEBKIT_FORMAT_URL_PROD, $env_name);
        }

        return $url;
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

    public static function getTpl()
    {
        return Configuration::get('LEMONWAY_TPL', null);
    }

    public static function getOneclicEnabled($method)
    {
        return Configuration::get('LEMONWAY_' . Tools::strtoupper($method) . '_ONECLIC_ENABLED', null);
    }
}
