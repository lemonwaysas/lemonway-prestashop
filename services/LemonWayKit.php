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

require_once 'models/Iban.php';
require_once 'models/KycDoc.php';
require_once 'models/LwError.php';
require_once 'models/LwModel.php';
require_once 'models/Extra.php';
require_once 'models/Operation.php';
require_once 'models/SddMandate.php';
require_once 'models/Wallet.php';
require_once 'ApiResponse.php';
require_once 'LemonWayConfig.php';

class LemonWayKit
{
    private static $printInputAndOutputXml = false;

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

    public function registerWallet($params)
    {
        $res = self::sendRequest('RegisterWallet', $params);

        if (!isset($res->lwError)) {
            $res->wallet = new Wallet($res->lwXml->WALLET);
        }

        return $res;
    }

    public function moneyIn($params)
    {
        $res = self::sendRequest('MoneyIn', $params);

        if (!isset($res->lwError)) {
            $res->operations = array(new Operation($res->lwXml->TRANS->HPAY));
        }

        return $res;
    }

    public function updateWalletDetails($params)
    {
        $res = self::sendRequest('UpdateWalletDetails', $params);

        if (!isset($res->lwError)) {
            $res->wallet = new Wallet($res->lwXml->WALLET);
        }

        return $res;
    }

    public  function getWalletDetails($params)
    {

        $res = self::sendRequest('GetWalletDetails', $params);


        return $res;
    }

    public function moneyIn3DInit($params)
    {
        return self::sendRequest('MoneyIn3DInit', $params);
    }

    public function moneyIn3DConfirm($params)
    {
        return self::sendRequest('MoneyIn3DConfirm', $params);
    }

    public function moneyInWebInit($params)
    {

        return self::sendRequest('MoneyInWebInit', $params);
    }

    public function registerCard($params)
    {
        return self::sendRequest('RegisterCard', $params);
    }

    public function unregisterCard($params)
    {
        return self::sendRequest('UnregisterCard', $params);
    }

    public function moneyInWithCardId($params)
    {
        $res = self::sendRequest('MoneyInWithCardId', $params);

        if (!isset($res->lwError)) {
            $res->operations = array(
                new Operation($res->lwXml->TRANS->HPAY)
            );
        }

        return $res;
    }

    public function moneyInValidate($params)
    {
        return self::sendRequest('MoneyInValidate', $params);
    }

    public function sendPayment($params)
    {
        $res = self::sendRequest('SendPayment', $params);

        if (!isset($res->lwError)) {
            $res->operations = array(
                new Operation($res->lwXml->TRANS->HPAY)
            );
        }

        return $res;
    }

    public function registerIBAN($params)
    {
        $res = self::sendRequest('RegisterIBAN', $params);

        if (!isset($res->lwError)) {
            $res->iban = new Iban($res->lwXml->IBAN);
        }

        return $res;
    }

    public function getPaymentDetails($params)
    {
        $res = self::sendRequest('GetPaymentDetails', $params);

        if (!isset($res->lwError)) {
            $res->operations = array();

            foreach ($res->lwXml->TRANS->HPAY as $HPAY) {
                $res->operations[] = new Operation($HPAY);
            }
        }

        return $res;
    }

    public function getMoneyInTransDetails($params)
    {
        $res = self::sendRequest('GetMoneyInTransDetails', $params);

        if (!isset($res->lwError)) {
            $res->operations = array();

            foreach ($res->TRANS->HPAY as $HPAY) {
                $res->operations[] = new Operation($HPAY);
            }
        }

        return $res;
    }

    public function uploadFile($params)
    {
        $res = self::sendRequest('UploadFile', $params);

        if (!isset($res->lwError)) {
            $res->kycDoc = new KycDoc($res->lwXml->UPLOAD);
        }

        return $res;
    }

    public function getKycStatus($params)
    {
        return self::sendRequest('GetKycStatus', $params);
    }

    public function getMoneyInIBANDetails($params)
    {
        return self::sendRequest('GetMoneyInIBANDetails', $params);
    }

    public function refundMoneyIn($params)
    {
        return self::sendRequest('RefundMoneyIn', $params);
    }

    public function getBalances($params)
    {
        return self::sendRequest('GetBalances', $params);
    }

    public function moneyIn3DAuthenticate($params)
    {
        return self::sendRequest('MoneyIn3DAuthenticate', $params);
    }

    public function moneyInIDealInit($params)
    {
        return self::sendRequest('MoneyInIDealInit', $params);
    }

    public function moneyInIDealConfirm($params)
    {
        return self::sendRequest('MoneyInIDealConfirm', $params);
    }

    public function registerSddMandate($params)
    {
        $res = self::sendRequest('RegisterSddMandate', $params);

        if (!isset($res->lwError)) {
            $res->sddMandate = new SddMandate($res->lwXml->SDDMANDATE);
        }

        return $res;
    }

    public function unregisterSddMandate($params)
    {
        return self::sendRequest('UnregisterSddMandate', $params);
    }

    public function moneyInSddInit($params)
    {
        return self::sendRequest('MoneyInSddInit', $params);
    }

    public function getMoneyInSdd($params)
    {
        return self::sendRequest('GetMoneyInSdd', $params);
    }

    public function getMoneyInChequeDetails($params)
    {
        return self::sendRequest('GetMoneyInChequeDetails', $params);
    }

    private function printDirectkitOutput($res)
    {
        if (self::$printInputAndOutputXml) {
            print '<br/>DEBUG OUTPUT START<br/>';

            foreach ($res[0] as $keyLevel1 => $valueLevel1) {
                print (string) $keyLevel1 . ': ' . (string) $valueLevel1;

                if ($valueLevel1->count() > 0) {
                    foreach ($valueLevel1 as $keyLevel2 => $valueLevel2) {
                        print '<br/>----' . (string) $keyLevel2 . ': ' . (string) $valueLevel2;

                        if ($valueLevel2->count() > 0) {
                            foreach ($valueLevel2 as $keyLevel3 => $valueLevel3) {
                                print '<br/>--------' . (string) $keyLevel3 . ': ' . (string) $valueLevel3;

                                if ($valueLevel3->count() > 0) {
                                    foreach ($valueLevel3 as $keyLevel4 => $valueLevel4) {
                                        print '<br/>------------' . (string) $keyLevel4 . ': ' . (string) $valueLevel4;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            print '<br/>DEBUG OUTPUT END<br/>';
        }
    }

    private function printDirectkitInput($string)
    {
        if (self::$printInputAndOutputXml) {
            print '<br/>DEBUG INTPUT START<br/>';
            echo htmlentities($string);
            //$xml = new SimpleXMLElement($string); echo $xml->asXML();
            print '<br/>DEBUG INTPUT END<br/>';
        }
    }

    private function sendRequest($methodName, $params)
    {
        $accessConfig = self::accessConfig();
        $ua = '';

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }

        $ua = "Prestashop-" . _PS_VERSION_ . "/" . $ua;
        $ip = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $tmpip = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($tmpip[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
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
        curl_setopt($ch, CURLOPT_URL, $accessConfig['directKitUrl'] . '/' . $methodName);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestParams));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
       // var_dump($params);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        } else {
            $responseCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            switch ($responseCode) {
                case 200:
                    break;
                case 400:
                    $message = "Bad Request : The server cannot or will not process the request due to something that is perceived to be a client error";
                    break;
                case 403:
                    $message = "IP is not allowed to access Lemon Way's API, please contact support@lemonway.fr";
                    break;
                case 404:
                    $message = "Check that the access URLs are correct. If yes, please contact support@lemonway.fr";
                    break;
                case 500:
                    $message = "Lemon Way internal server error, please contact support@lemonway.fr";
                    break;
                default:
                    sprintf("HTTP CODE %d IS NOT SUPPORTED", $responseCode);
                    break;
            }

            if ($responseCode == 200) {
                //General parsing
                $response = json_decode($response);
                //Check error
                if (isset($response->d->E)) {
                    throw new Exception($response->d->E->Msg . " (Error code: " . $response->d->E->Code . ")");
                }
                return $response->d;
            }
        }
        curl_close($ch);
    }

    public function printCardForm($moneyInToken, $cssUrl = '', $language = 'en')
    {
        $accessConfig = self::accessConfig();

        $ch = curl_init();
        curl_setopt(
            $ch,
            CURLOPT_URL,
            $accessConfig['webkitUrl'] . "?moneyintoken=" . $moneyInToken . '&p=' . urlencode($cssUrl)
            . '&lang=' . $language
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$accessConfig['isTestMode']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $server_output = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        } else {
            $returnCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

            switch ($returnCode) {
                case 200:
                    curl_close($ch);
                    $parsedUrl = parse_url($accessConfig['webkitUrl']);
                    $root = strstr($accessConfig['webkitUrl'], $parsedUrl['path'], true);
                    $server_output = preg_replace(
                        "/src=\"([a-zA-Z\/\.]*)\"/i",
                        "src=\"" . $root . "$1\"",
                        $server_output
                    );

                    return $server_output;
                default:
                    throw new Exception($returnCode);
            }
        }
    }

    private function cleanRequest($str)
    {
        $str = strtr($str, 'ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝ', 'AAAAAACEEEEEIIIINOOOOOUUUUY');
        $str = strtr($str, 'áàâäãåçéèêëíìîïñóòôöõúùûüýÿ', 'aaaaaaceeeeiiiinooooouuuuyy');
        $str = strtr($str, '&', '_');
        $str = strtr($str, '<', '_');
        $str = strtr($str, '>', '_');

        return $str;
    }
}
