{**
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
*}
<div class="row">
    <div class="col-xs-12 col-md-12">
        <div class="Lemonway_payment_form">
            <form class="placeOrderForm"
            action="{$link->getModuleLink('lemonway', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}"
            method="POST">
                <input type="hidden" value="{$method->getCode()}" name="method_code">

                <div class="lemonway-payment" id="lemonway_{$method->getCode()}_payment_form">
                    {if $method->getCode() == 'CC_XTIMES' || $method->getData('oneclic_allowed') == 1}
                    <div class="lemonway-payment-container" id="lemonway_{$method->getCode()}_payment_form_container">
                    {else}
                    <div class="lemonway-payment-container clickable" id="lemonway_{$method->getCode()}_payment_form_container">
                    {/if}
                        <div class="lemonway-payment-img-container">
                            <img class="lemonway-payment-icon img-responsive"
                            src="{$module_dir|escape:'html':'UTF-8'}views/img/paiement-mode.png"
                            width="500px" class="img-responsive" alt="{$method->getTitle()}"
                            id="payment-lemonway-{$method->getCode()}-logo" /> 
                        </div>

                        <h3 class="lemonway-method-title">{$method->getTitle()}</h3>            

                        {if $method->getCode() == 'CC_XTIMES'}
                        <div class="lemonway-payment-splitpayment-profiles-container">
                            {if $method->getData('splitpayments_profiles_length') == 1}
                                {foreach from=$method->getData('splitpayments_profiles') item='profile'}
                            <div>
                                <h4>{l s='Payment profile:' mod='lemonway'} {$profile->name}</h4>
                                <input type="hidden" value="{$profile->id}" name="splitpayment_profile_id" />
                            </div>
                                {/foreach}
                            {elseif $method->getData('splitpayments_profiles_length') > 1}
                            <label for="lemonway_{$method->getCode()}_splitpayment_profile_select">{l s='Select your split payment profile' mod='lemonway'}</label>
                            <select id="lemonway_{$method->getCode()}_splitpayment_profile_select" name="splitpayment_profile_id">
                                {foreach from=$method->getData('splitpayments_profiles') item='profile'}
                                <option value="{$profile->id}">{$profile->name}</option>
                                {/foreach}
                            </select>
                            {else}
                            
                            <span>{l s='No split payment available. Your order will be totally paid.' mod='lemonway'}</span>
                            {/if}

                            {foreach from=$method->getData('splitpayments_profiles') item='profile'}
                            <div id="profile_splitpayment_table_{$profile->id}" style="display: none">
                                <span>{l s='Your next payments' mod='lemonway'} :</span>
                                
                                <div class="table_block table-responsive">
                                    <table class="table table-bordered" id="split-payment-cc-table">
                                        <thead>
                                            <th>{l s='Debit date' mod='lemonway'}</th>
                                            <th>{l s='Debit amount' mod='lemonway'}</th>
                                        </thead>
                                        
                                        <tbody>
                                            {foreach from=$profile->splitPaymentAmount($total_price) key='index' item='deadline'}
                                            <tr>
                                                <td>{dateFormat date=$deadline.dateToPay full=0}</td>
                                                <td>{displayPrice price=$deadline.amountToPay currency=$cart->id_currency no_utf8=false convert=false} {if $index == 0}{l s='(Debited on order validation)' mod='lemonway'}{/if}</td>
                                            </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            {/foreach}
                        </div>
                        {/if}

                        <!--<div class="row lw_container_cards_types" style="{if $method->getData('oneclic_allowed') == 1 && $method->getData('customer_has_card') == 1}display:none{/if}" >
                            <div class="col-md-2 col-xs-3">
                                <div class="radio">
                                    <label> 
                                        <input type="radio" name="cc_type" value="CB" required checked="checked"> 
                                        <img class="img-responsive" alt="CB" src="{$module_dir|escape:'html':'UTF-8'}views/img/carte-bleue.png">
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 col-xs-3">
                                <div class="radio">
                                    <label> 
                                        <input type="radio" name="cc_type" value="VISA" required> 
                                        <img class="img-responsive" alt="VISA" src="{$module_dir|escape:'html':'UTF-8'}views/img/Visa.png">
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 col-xs-3">
                                <div class="radio">
                                    <label> 
                                        <input type="radio" name="cc_type" value="MASTERCARD" required> 
                                        <img class="img-responsive" alt="MASTERCARD" src="{$module_dir|escape:'html':'UTF-8'}views/img/Mastercard-logo.png">
                                    </label>
                                </div>  
                            </div>
                        </div>-->

                        {if $method->getData('oneclic_allowed') == 1} <!-- Oneclic form -->
                        <div class="lemonway-payment-oneclic-container">            
                            {if $method->getData('customer_has_card') == 0} <!-- User can choose to save his card -->
                            <div class="checkbox">
                                <label for="lw_register_card_{$method->getCode()}"> <input id="lw_register_card_{$method->getCode()}"  class="lw_register_card"
                                value="register_card" type="checkbox" name="lw_oneclic" />
                                    {l s='Save your card data for a next buy.' mod='lemonway'}
                                </label>
                            </div>
                            {else} <!-- User already have a card. He can choose to use it or not-->
                            <div>
                                <div class="radio">
                                    <label for="lw_use_card_{$method->getCode()}"> <input id="lw_use_card_{$method->getCode()}" class="lw_use_card"
                                    value="use_card" checked="checked" type="radio"
                                    name="lw_oneclic" />
                                    {l s='Use my recorded card' mod='lemonway'}
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label>{l s='Actual card' mod='lemonway'} : <span>{$method->getData('card_num')|escape:'html':'UTF-8'}</span></label>
                            </div>  
                            
                                {if $method->getData('card_exp') != ''}
                            <div>
                                <label>{l s='Expiration date' mod='lemonway'} : {$method->getData('card_exp')|escape:'html':'UTF-8'}</label>
                            </div>
                                {/if}
                            
                            <div>
                                <div class="radio">
                                    <label for="lw_register_card_{$method->getCode()}"> <input id="lw_register_card_{$method->getCode()}"  class="lw_register_card"
                                    value="register_card" type="radio" name="lw_oneclic" />
                                    {l s='Save new card data' mod='lemonway'}
                                    </label>
                                </div>
                            </div>
                        
                            <div>
                                <div class="radio">
                                    <label for="lw_no_use_card_{$method->getCode()}"> <input id="lw_no_use_card_{$method->getCode()}" class="lw_no_use_card"
                                    type="radio" name="lw_oneclic" value="no_use_card" />
                                        {l s='Not use recorded card data' mod='lemonway'}
                                    </label>
                                </div>
                            </div>

                            <br />
                            {/if}
                        </div>
                        {/if}

                        <div class="lemonway-payment-button-submit-container clearfix">
                            <button type="submit" name="lwPay" class="button btn btn-default button-medium">
                                <span>
                                    {l s='Proceed to payment' mod='lemonway'}<i class="icon-chevron-right right"></i>
                                </span>
                            </button>
                        </div>
                    </div> <!--<div class="lemonway-payment-container">-->
                </div> <!--<div class="lemonway-payment"> -->

                <input id="open_basedir" type="hidden" value="{$open_basedir|escape:'htmlall':'UTF-8'}" />
            </form>
        </div>
    </div>
</div>
