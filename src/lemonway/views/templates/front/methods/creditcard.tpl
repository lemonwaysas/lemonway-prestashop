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
			<div class="lemonway-payment"
				id="lemonway_{$method->getCode()}_payment_form">
				<div class="lemonway-payment-container" id="lemonway_{$method->getCode()}_payment_form_container">
					<div class="lemonway-payment-img-container">
						<img class="lemonway-payment-icon"
						src="{$module_dir|escape:'html':'UTF-8'}views/img/paiement-mode.jpg"
						width="500px" class="img-responsive" alt="{$method->getTitle()}"
						id="payment-lemonway-{$method->getCode()}-logo" /> 
					</div>
					{if $method->getCode() == 'CC_XTIMES'}
						<div class="lemonway-payment-splitpayment-profiles-container">
							{if $method->getData('splitpayments_profiles_length') == 1}
								{assign var="splitpayment_profile" value="$method->getData('splitpayments_profiles')"}
								<span>{$splitpayment_profile[0]['name']}</span>
								<input type="hidden" value="{$splitpayment_profile[0]['id_profile']}" name="splitpayment_profile_id" />
							{elseif $method->getData('splitpayments_profiles_length') > 1}
							<label for="lemonway_{$method->getCode()}_splitpayment_profile_select">{l s='Select your split payment profile' mod='lemonway'}</label>
								<select id="lemonway_{$method->getCode()}_splitpayment_profile_select" name="splitpayment_profile_id">
									{foreach from=$method->getData('splitpayments_profiles') item='profile'}
										<option value="{$profile.id_profile}">{$profile.name}</option>
									{/foreach}
								</select>
							{else}
								<span>{l s='No split payment available. Your order will be totally paid.' mod='lemonway'}</span>
							{/if}
						</div>
						
					{/if}
						{if $method->getData('oneclic_allowed') == 1} <!-- Oneclic form -->
							<div class="lemonway-payment-oneclic-container">			
							{if $method->getData('customer_has_card') == 0} <!-- User can choose to save his card -->
								<div class="checkbox">
									<label for="lw_register_card"> <input id="lw_register_card"
										value="register_card" type="checkbox" name="lw_oneclic" /> {l s='Save your card data for a next buy.' mod='lemonway'}
									</label>
								</div>
							{else} <!-- User already have a card. He can choose to use it or not-->
								<div>
									<div class="radio">
										<label for="lw_use_card"> <input id="lw_use_card"
											value="use_card" checked="checked" type="radio"
											name="lw_oneclic" checked /> {l s='Use my recorded card' mod='lemonway'}
										</label>
									</div>
								</div>
								<div class="">
									<label>{l s='Actual card' mod='lemonway'} : <span>{$method->getData('card_num')|escape:'html':'UTF-8'}</span></label>
								</div>	
								{if $method->getData('card_exp') != ''}
									<div class="">
										<label>{l s='Expiration date' mod='lemonway'} : {$method->getData('card_exp')|escape:'html':'UTF-8'}</label>
									</div>
								{/if}
								<div>
									<div class="radio">
										<label for="lw_register_card"> <input id="lw_register_card"
										value="register_card" type="radio" name="lw_oneclic" /> {l s='Save new card data' mod='lemonway'}
										</label>
									</div>
								</div>
								<div>
									<div class="radio">
										<label for="lw_no_use_card"> <input id="lw_no_use_card"
											type="radio" name="lw_oneclic" value="no_use_card" /> {l s='Not
											use recorded card data' mod='lemonway'}
										</label>
									</div>
								</div>
								<br /> 
							{/if}
							</div>
						{/if}
						<div class="lemonway-payment-button-submit-container Lemonway_payment_btn">
							<button type="submit" name="lwPay"
								class="button btn btn-default">
								<span> {$method->getTitle()} <i
									class="icon-chevron-right right"></i>
								</span>
							</button>
						</div>
									
				</div>
			</div>
			
			<!-- <p class="payment_module"
				id="lemonway_{$method->getCode()}_payment_button">
				<a
					href="{$link->getModuleLink('lemonway', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}"
					title="{$method->getTitle()}"> <img
					src="{$module_dir|escape:'html':'UTF-8'}views/img/paiement-mode.jpg"
					class="img-responsive" alt="{$method->getTitle()}" width="500px"
					id="payment-lemonway-{$method->getCode()}-logo" /> {$method->getTitle()}
				</a>

			</p>-->
			<input id="open_basedir" type="hidden" value="{$open_basedir|escape:'htmlall':'UTF-8'}" />
		</form>
		</div>
	</div>
</div>
