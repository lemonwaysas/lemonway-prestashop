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

require_once 'models/LwError.php';

class ApiResponse
{
    public function __construct($xmlResponse)
    {
        $this->lwXml = $xmlResponse;
        if (isset($xmlResponse->E)) {
            $this->lwError = new LwError(
                $xmlResponse->E->Code,
                $xmlResponse->E->Msg . " (" . $xmlResponse->E->Error . ")"
            );
        }
    }

    /**
     * lwXml
     * @var SimpleXMLElement
     */
    public $lwXml;

    /**
     * lwError
     * @var LwError
     */
    public $lwError;

    /**
     * wallet
     * @var Wallet
     */
    public $wallet;

    /**
     * operations
     * @var array Operation
     */
    public $operations;
}
