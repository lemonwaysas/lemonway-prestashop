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
