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

require_once 'Method.php';

class Cc extends Method
{
    protected $code = 'cc';
    protected $template = 'creditcard.tpl';

    protected function prepareData()
    {
        /* @var $customer CustomerCore */
        $customer = $this->context->customer;

        $card_num = "";
        $card_type = "";
        $card_exp = "";
        $card = $this->module->getCustomerCard($customer->id);

        if ($card) {
            $card_num = $card['card_num'];
            $card_type = $card['card_type'];
            $card_exp = $card['card_exp'];
        }

        $customer_has_card = $card && !empty($card_num);
        $this->data = array(
            'oneclic_allowed' => LemonWayConfig::getOneclicEnabled($this->code) && $customer->isLogged(),
            'customer_has_card' => $customer_has_card,
            'card_num' => $card_num,
            'card_type' => $card_type,
            'card_exp' => $card_exp
        );

        return $this;
    }
}
