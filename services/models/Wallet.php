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

    public function __construct($WALLET)
    {
        $this->ID = $WALLET->ID;
        $this->LWID = $WALLET->LWID;
        $this->STATUS = $WALLET->STATUS;
        $this->BAL = $WALLET->BAL;
        $this->NAME = $WALLET->NAME;
        $this->EMAIL = $WALLET->EMAIL;
    }
}
