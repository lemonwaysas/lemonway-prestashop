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

/**
 * Detailed information regarding Card payment
 */
class Extra
{
    /**
     * IS3DS indicates if payment was 3D Secure
     * @var bool
     */
    public $IS3DS;

    /**
     * CTRY country of card
     * @var string
     */
    public $CTRY;

    /**
     * AUTH authorization number
     * @var string
     */
    public $AUTH;

    /**
     * Number of registered card
     * @var string
     * @since api version 1.8
     */
    public $NUM;

    /**
     * Expiration date of registered card
     * @var string
     * @since api version 1.8
     */
    public $EXP;

    /**
     * Type of card
     * @var string
     * @since api version 1.8
     */
    public $TYP;

    public function __construct($extraXml)
    {
        $this->AUTH = $extraXml->AUTH;
        $this->IS3DS = $extraXml->IS3DS;
        $this->CTRY = $extraXml->CTRY;
        $this->NUM = $extraXml->NUM;
        $this->EXP = $extraXml->EXP;
        $this->TYP = $extraXml->TYP;
    }
}
