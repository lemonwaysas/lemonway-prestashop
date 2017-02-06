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

class Wallet
{
    /**
    * ID as defined by merchant
    * @var string
    */
    public $ID;
    
    /**
    * LWID number ID as defined by Lemon Way
    * @var string
    */
    public $LWID;
    
    /**
    * STATUS {2,3,4,5,6,7,8,12}
    * @var string
    */
    public $STATUS;
    
    /**
    * BAL balance
    * @var string
    */
    public $BAL;
    
    /**
    * NAME full name
    * @var string
    */
    public $NAME;
    
    /**
    * EMAIL
    * @var string
    */
    public $EMAIL;
    
    /**
    * kycDocs
    * @var array KycDoc
    */
    public $kycDocs;
    
    /**
    * ibans 
    * @var array Iban
    */
    public $ibans;
    
    /**
    * sddMandates 
    * @var array SddMandate
    */
    public $sddMandates;
    
    public function __construct($WALLET)
    {
        $this->ID = $WALLET->ID;
        $this->LWID = $WALLET->LWID;
        $this->STATUS = $WALLET->STATUS;
        $this->BAL = $WALLET->BAL;
        $this->NAME = $WALLET->NAME;
        $this->EMAIL = $WALLET->EMAIL;
        $this->kycDocs = array();
        if (isset($WALLET->DOCS)) {
            foreach ($WALLET->DOCS->DOC as $DOC) {
                $this->kycDocs[] = new KycDoc($DOC);
            }
        }

        $this->ibans = array();
        if (isset($WALLET->IBANS)) {
            foreach ($WALLET->IBANS->IBAN as $IBAN) {
                $this->ibans[] = new Iban($IBAN);
            }
        }

        $this->sddMandates = array();
        if (isset($WALLET->SDDMANDATES)) {
            foreach ($WALLET->SDDMANDATES->SDDMANDATE as $SDDMANDATE) {
                $this->sddMandates[] = new SddMandate($SDDMANDATE);
            }
        }
    }
}
