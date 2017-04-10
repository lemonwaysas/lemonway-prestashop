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

class SddMandate
{
    /**
    * ID as defined by Lemon Way
    * @var string
    */
    public $ID;
    
    /**
    * STATUS {0,5,6,8,9}
    * @var string
    */
    public $STATUS;
    
    /**
    * IBAN number
    * @var string
    */
    public $IBAN;
    
    /**
    * BIC or swift code
    * @var string
    */
    public $BIC;
    
    public function __construct($node)
    {
        $this->ID = $node->ID;
        if (isset($node->STATUS)) {
            $this->STATUS = $node->STATUS;
        }
        if (isset($node->S)) {
            $this->STATUS = $node->S;
        }
        if (isset($node->DATA)) {
            $this->IBAN = $node->DATA;
        }
        if (isset($node->SWIFT)) {
            $this->BIC = $node->SWIFT;
        }
    }
}
