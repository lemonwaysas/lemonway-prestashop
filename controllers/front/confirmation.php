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

class LemonwayConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (Tools::isSubmit('cart_id') == false ||
            Tools::isSubmit('secure_key') == false ||
            Tools::isSubmit('action') == false) {
            return false;
        }

        $action = Tools::getValue('action');
        $cart_id = Tools::getValue('cart_id');
        $secure_key = Tools::getValue('secure_key');
        /* @var $methodInstance Method */
        $methodInstance = $this->module->methodFactory(Tools::getValue('payment_method'));

        $cart = new Cart((int) $cart_id);
        $customer = new Customer((int) $cart->id_customer);

        $cart_total_paid = (float) Tools::ps_round((float) $cart->getOrderTotal(true, Cart::BOTH), 2);

        //If is X times method, we split the payment
        if ($methodInstance->isSplitPayment() &&
            ($splitPaypentProfileId = Tools::getValue('splitpayment_profile_id'))) {
            $profile = new SplitpaymentProfile($splitPaypentProfileId);

            if ($profile) {
                $splitpayments = $profile->splitPaymentAmount($cart_total_paid);
                $firstSplit = $splitpayments[0];
                $cart_total_paid = (float) Tools::ps_round((float) $firstSplit['amountToPay'], 2);
            } else {
                $this->addError('Split payment profile not found!');
                return $this->displayError();
            }
        }

        /**
         * Restore the context from the $cart_id & the $customer_id to process the validation properly.
         */
        Context::getContext()->cart = $cart;

        if (!Context::getContext()->cart->id) {
            die;
        }

        Context::getContext()->customer = $customer;
        Context::getContext()->currency = new Currency((int) Context::getContext()->cart->id_currency);
        Context::getContext()->language = new Language((int) Context::getContext()->customer->id_lang);

        // Default value for a payment that succeed.
        $payment_status = Configuration::get(Lemonway::LEMONWAY_PENDING_OS);
        $message = $this->module->l("Order in pending validation payment.");

        $currency_id = (int) Context::getContext()->currency->id;

        switch ($action) {
            case 'return':
                /**
                 * If the order has been validated we try to retrieve it
                 */
                $order_id = Order::getOrderByCartId((int) $cart->id);

                if (!$order_id) {
                    /**
                     * Converting cart into a valid order
                     */
                    $this->module->validateOrder(
                        $cart_id,
                        $payment_status,
                        $cart_total_paid,
                        $methodInstance->getTitle(),
                        $message,
                        array(),
                        $currency_id,
                        false,
                        $secure_key
                    );
                    $order_id = Order::getOrderByCartId((int) $cart->id);
                }

                if ($order_id && ($secure_key == $customer->secure_key)) {
                    /**
                     * The order has been placed so we redirect the customer on the confirmation page.
                     */
                    $module_id = $this->module->id;
                    Tools::redirect(
                        'index.php?controller=order-confirmation&id_cart=' . $cart_id
                        . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key
                    );
                }
                break;

            case 'cancel':
               // $this->errors[] = $this->module->l('Order has been canceled');
                //redirect to cart
                Tools::redirect($this->context->link->getPageLink('order', true));
                //return $this->setTemplate('error.tpl');
                break;

            case 'error':
                $order_id = Order::getOrderByCartId((int) $cart->id);

                if ($order_id && ($secure_key == $customer->secure_key)) {
                    $module_id = $this->module->id;
                    return Tools::redirect(
                        'index.php?controller=order-confirmation&id_cart=' . $cart_id
                        . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key
                    );
                }

                /**
                 * An error occured and is shown on a new page.
                 */
                $this->addError('An error occured. Please contact the merchant to have more informations');
                return $this->displayError();
            
            default:
        }
    }
    
    protected function addError($message, $description = false)
    {
        /**
         * Set error message and description for the template.
         */
        array_push($this->errors, $this->module->l($message), $description);
    }
    
    protected function displayError()
    {
        
        if ($this->module->isVersion17()) {
            $cartUrl = 'index.php?controller=cart&action=show';
            return $this->redirectWithNotifications($cartUrl);
        }
        
        /**
         * Create the breadcrumb for your ModuleFrontController.
         */
        $path = '<a href="' . $this->context->link->getPageLink('order', null, null, 'step=3') . '">'
                . $this->module->l('Payment')
                . '</a><span class="navigation-pipe">&gt;</span>' . $this->module->l('Error');
                 
                $this->context->smarty->assign(
                    array(
                        'path'=>$path,
                        'errors'=>$this->errors
                    )
                );
    
                $template = 'error.tpl';
                if ($this->module->isVersion17()) {
                    $template = 'module:' . $this->module->name . '/views/templates/front/error.tpl';
                }
    
                return $this->setTemplate($template);
    }
}
