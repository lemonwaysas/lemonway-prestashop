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
class Operation
{
    /**
     * type {p2p, moneyin, moneyout}
     * @var string
     */
    public $type;

    /**
     * ID number
     * @var string
     */
    public $ID;

    /**
     * MLABEL iban number or card number
     * @var string
     */
    public $MLABEL;

    /**
     * SEN sender wallet (debited wallet)
     * @var string
     */
    public $SEN;

    /**
     * REC receiver wallet (credited wallet)
     * @var string
     */
    public $REC;

    /**
     * DEB debited amount, xx.xx
     * @var string
     */
    public $DEB;

    /**
     * CRED credited amount, xx.xx
     * @var string
     */
    public $CRED;

    /**
     * COM fees automatically sent to merchant wallet
     * @var string
     */
    public $COM;

    /**
     * MSG comment
     * @var string
     */
    public $MSG;

    /**
     * STATUS {0,3,4}
     * @var string
     */
    public $STATUS;

    /**
     * INT_MSG internal error message with codes
     * @var string
     */
    public $INT_MSG;

    public $EXTRA;

    public function __construct($hpayXml)
    {
        $this->ID = $hpayXml->ID;
        $this->SEN = $hpayXml->SEN;
        $this->REC = $hpayXml->REC;
        $this->DEB = $hpayXml->DEB;
        $this->CRED = $hpayXml->CRED;
        $this->COM = $hpayXml->COM;
        $this->STATUS = $hpayXml->STATUS;
        $this->MLABEL = $hpayXml->MLABEL;
        $this->INT_MSG = $hpayXml->INT_MSG;
        $this->EXTRA = new Extra($hpayXml->EXTRA);
    }
}
