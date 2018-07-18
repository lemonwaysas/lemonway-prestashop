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

    public function getWalletDetails($params)
    {
        $res = self::sendRequest('GetWalletDetails', $params, '1.5');

        if (!isset($res->lwError)) {
            $res->wallet = new Wallet($res->lwXml->WALLET);
        }

        return $res;
    }

    public function moneyInWebInit($params)
    {
        return self::sendRequest('MoneyInWebInit', $params, '1.3');
    }

    public function moneyInWithCardId($params)
    {
        $res = self::sendRequest('MoneyInWithCardId', $params, '1.1');

        if (!isset($res->lwError)) {
            $res->operations = array(
                new Operation($res->lwXml->TRANS->HPAY)
            );
        }

        return $res;
    }

    public function getMoneyInTransDetails($params)
    {
        $res = self::sendRequest('GetMoneyInTransDetails', $params, '1.8');

        if (!isset($res->lwError)) {
            $res->operations = array();

            foreach ($res->lwXml->TRANS->HPAY as $HPAY) {
                $res->operations[] = new Operation($HPAY);
            }
        }

        return $res;
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

    private function sendRequest($methodName, $params, $version)
    {
        $version = "10.0";
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

        $xml_soap = '<?xml version="1.0" encoding="utf-8"?><soap12:Envelope xmlns:xsi=
        "http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12=
        "http://www.w3.org/2003/05/soap-envelope"><soap12:Body><' . $methodName . ' xmlns="Service_mb_xml">';

        foreach ($params as $key => $value) {
            $xml_soap .= '<' . $key . '>' . $this->cleanRequest($value) . '</' . $key . '>';
        }

        $xml_soap .= '<version>' . $version . '</version>';
        $xml_soap .= '<wlPass>' . $this->cleanRequest($accessConfig['wlPass']) . '</wlPass>';
        $xml_soap .= '<wlLogin>' . $this->cleanRequest($accessConfig['wlLogin']) . '</wlLogin>';
        $xml_soap .= '<language>' . $accessConfig['language'] . '</language>';
        $xml_soap .= '<walletIp>' . $ip . '</walletIp>';
        $xml_soap .= '<walletUa>' . $ua . '</walletUa>';

        $xml_soap .= '</' . $methodName . '></soap12:Body></soap12:Envelope>';
        self::printDirectkitInput($xml_soap);

        $headers = array(
            "Content-type: text/xml;charset=utf-8",
            "Accept: application/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            'SOAPAction: "Service_mb_xml/' . $methodName . '"',
            "Content-length: " . Tools::strlen($xml_soap)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $accessConfig['directKitUrl']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_soap);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$accessConfig['isTestMode']);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        } else {
            $returnCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            switch ($returnCode) {
                case 200:
                    //General parsing
                    //Cleanup XML
                    $response = (string) str_replace(
                        '<?xml version="1.0" encoding="utf-8"?>' .
                        '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" ' .
                        'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
                        'xmlns:xsd="http://www.w3.org/2001/XMLSchema">',
                        '',
                        $response
                    );
                    $response = (string) str_replace('</soap:Envelope>', '', $response);
                    libxml_use_internal_errors(true);
                    $xml = new \SimpleXMLElement($response);

                    //Retrieve result
                    $content = '';

                    switch ($methodName) {
                        case 'UnregisterSddMandate':
                            $content = $xml->{$methodName . 'Response'}->{'UnRegisterSddMandateResult'};
                            break;

                        case 'MoneyInWithCardId':
                            $content = $xml->{$methodName . 'Response'}->{'MoneyInResult'};
                            break;

                        default:
                            $content = $xml->{$methodName . 'Response'}->{$methodName . 'Result'};
                            break;
                    }

                    return new ApiResponse($content);

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
                    break;
            }

            throw new Exception("HTTP CODE IS NOT SUPPORTED ", $returnCode);
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
