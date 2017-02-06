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

class KycDoc
{
    /**
    * ID as defined by Lemon Way
    * @var string
    */
    public $ID;
    
    /**
    * STATUS {1,2,3,4,5}
    * @var string
    */
    public $STATUS;
    
    /**
    * TYPE {0,1,2,3,4,5,6,7,11,12,13,14,15,16,17,18,19,20}
    * @var string
    */
    public $TYPE;
    
    /**
    * VD validity date
    * @var string
    */
    public $VD;
    
    public function __construct($node)
    {
        $this->ID = $node->ID;
        $this->STATUS = $node->S;
        $this->TYPE = $node->TYPE;
        $this->VD = $node->VD;
    }
}
