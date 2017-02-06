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

class LemonwayConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)
            || Tools::isSubmit('action') == false) {
            return false;
        }
        
        $action = Tools::getValue('action');
        $cart_id = Tools::getValue('cart_id');
        $secure_key = Tools::getValue('secure_key');

        $cart = new Cart((int)$cart_id);
        $customer = new Customer((int)$cart->id_customer);

        /**
        * Restore the context from the $cart_id & the $customer_id to process the validation properly.
        */
        /* Context::getContext()->cart = $cart;
        if(!Context::getContext()->cart->id) {
            die;
        }

        Context::getContext()->customer = $customer;
        Context::getContext()->currency = new Currency((int)Context::getContext()->cart->id_currency);
        Context::getContext()->language = new Language((int)Context::getContext()->customer->id_lang);*/
        
        /**
        * Since it's an example we are validating the order right here,
        * You should not do it this way in your own module.
        */
        $payment_status = Configuration::get('LEMONWAY_PENDING_OS'); // Default value for a payment that succeed.
        $message = $this->module->l("Order in pending validation payment.");
        // You can add a comment directly into the order so the merchant will see it in the BO.
        
        /**
        * Converting cart into a valid order
        */
        
        $module_name = $this->module->displayName;
        $currency_id = (int)Context::getContext()->currency->id;
        
        switch ($action)
        {
            case 'return':
                /**
                * If the order has been validated we try to retrieve it
                */
                $order_id = Order::getOrderByCartId((int)$cart->id);
                if (!$order_id) {
                    $this->module->validateOrder(
                        $cart_id,
                        $payment_status,
                        $cart->getOrderTotal(),
                        $module_name,
                        $message,
                        array(
                        ),
                        $currency_id,
                        false,
                        $secure_key
                    );
                    $order_id = Order::getOrderByCartId((int)$cart->id);
                }

                if ($order_id && ($secure_key == $customer->secure_key)) {
                    /**
                    * The order has been placed so we redirect the customer on the confirmation page.
                    */
                    $module_id = $this->module->id;
                    Tools::redirect(
                        'index.php?controller=order-confirmation&id_cart=' . $cart_id
                        . '&id_module=' .$module_id . '&id_order=' . $order_id . '&key=' . $secure_key
                    );
                }

                break;

            case 'cancel':
                $this->errors[] = $this->module->l('Order has been canceled');
                //redirect to cart
                Tools::redirect($this->context->link->getPageLink('order', true));
                //return $this->setTemplate('error.tpl');
                break;

            case 'error':
                $order_id = Order::getOrderByCartId((int)$cart->id);
                if ($order_id  && ($secure_key == $customer->secure_key)) {
                    $module_id = $this->module->id;
                    return Tools::redirect(
                        'index.php?controller=order-confirmation&id_cart=' . $cart_id
                        . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key
                    );
                }

                /**
                 * An error occured and is shown on a new page.
                 */
                $this->errors[] =
                $this->module->l('An error occured. Please contact the merchant to have more informations');
                return $this->setTemplate('error.tpl');

            default:
        }
    }
}
