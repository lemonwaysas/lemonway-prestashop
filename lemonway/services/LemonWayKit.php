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

require_once 'LemonWayConfig.php';

class LemonWayKit
{
    private static function accessConfig()
    {
        return array(
            'directKitUrl' => LemonWayConfig::getDirectkitUrl(),
            'webkitUrl' => LemonWayConfig::getWebkitUrl(),
            'isTestMode' => LemonWayConfig::isTestMode(),
            'wlLogin' => LemonWayConfig::getApiLogin(),
            'wlPass' => LemonWayConfig::getApiPassword(),
            'language' => 'en'
        ); // @TODO get language and filter with available languages in lw.
    }

    public function getWalletDetails($params)
    {
        $res = self::sendRequest('GetWalletDetails', $params);

        return $res;
    }

    public function moneyInWebInit($params)
    {
        return self::sendRequest('MoneyInWebInit', $params);
    }

    public function moneyInWithCardId($params)
    {
        $res = self::sendRequest('MoneyInWithCardId', $params);

        return $res;
    }

    public function getMoneyInTransDetails($params)
    {
        $res = self::sendRequest('GetMoneyInTransDetails', $params);

        return $res;
    }

    private function sendRequest($methodName, $params)
    {
        $accessConfig = self::accessConfig();

        $url = $accessConfig['directKitUrl'] . '/' . $methodName;

        $ua = "Prestashop-" . _PS_VERSION_;
        $ua .= (isset($_SERVER['HTTP_USER_AGENT'])) ? "/" . $_SERVER['HTTP_USER_AGENT'] : "";

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $tmpip = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($tmpip[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "";
        }

        $baseParams = array(
            'wlLogin' => $accessConfig['wlLogin'],
            'wlPass' => $accessConfig['wlPass'],
            'language' => 'fr',
            'version' => '10.0',
            'walletIp' => $ip,
            'walletUa' => $ua,
        );

        $requestParams = array_merge($baseParams, $params);
        $requestParams = array('p' => $requestParams);

        $headers = array(
            "Content-type: application/json; charset=utf-8",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache"
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestParams));

        $response = curl_exec($ch);

        // Log
        $requestParams["p"]["wlPass"] = "*masked*";
        PrestaShopLogger::addLog(
            "Lemon Way: " . $url . " - Request: " . json_encode($requestParams) . " - Response: " . $response
        );

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        } else {
            $returnCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            switch ($returnCode) {
                case 200:
                    //General parsing
                    $response = json_decode($response);
                    //Check error
                    if (isset($response->d->E)) {
                        throw new Exception($response->d->E->Msg . " (Error code: " . $response->d->E->Code . ")");
                    }
                    return $response->d;
                case 400:
                    throw new Exception("Bad Request: The server cannot or will not process the request due to something
                     that is perceived to be a client error", 400);

                case 403:
                    throw new Exception("IP is not allowed to access Lemon Way's API,
                        please contact support@lemonway.fr", 403);

                case 404:
                    throw new Exception("Check that the access URLs are correct. If yes,
                        please contact support@lemonway.fr", 404);

                case 500:
                    throw new Exception("Lemon Way internal server error, please contact support@lemonway.fr", 500);

                default:
                    throw new Exception("HTTP CODE IS NOT SUPPORTED ", $returnCode);
            }
        }
    }
}
