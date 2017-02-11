{**
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
 *}

<div class="row">
	<div class="col-xs-12 col-md-12">
	{if $oneclic_allowed == 1}
<div class="payment_module" id="Lemonway_payment_form">
	<div id="Lemonway_payment_form_container" class=row"">
		<img src="{$module_dir|escape:'html':'UTF-8'}views/img/paiement-mode.jpg" width="500px" class="img-responsive"  alt="{l s='Pay with Lemonway' mod='lemonway'}" id="payment-lemonway-logo" />
    	<form action="{$link->getModuleLink('lemonway', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" method="POST" >
			{if $customer_has_card == 0}
				<div class="checkbox">
					<label for="lw_register_card">
						<input id="lw_register_card"  value="register_card"  type="checkbox" name="lw_oneclic" />
						{l s='Save your card data for a next buy.' mod='lemonway'}
					</label>				
					
				</div>
			{else}
				<div>
					<div class="radio">
						<label for="lw_use_card">
							<input id="lw_use_card" value="use_card" checked="checked" type="radio" name="lw_oneclic"  checked/>
							{l s='Use my recorded card' mod='lemonway'}
						</label>
					</div>
				</div>
				<div class="">
					<label>{l s='Actual card' mod='lemonway'} : <span>{$card_num|escape:'html':'UTF-8'}</span></label>			
				</div>
				{if $card_exp != ''}
					<div class="">
						<label>{l s='Expiration date' mod='lemonway'} : {$card_exp|escape:'html':'UTF-8'}</label>
					</div>
				{/if}
				<div>
					<div class="radio">
						<label for="lw_register_card">
							<input id="lw_register_card"  value="register_card"  type="radio" name="lw_oneclic" />
							{l s='Save new card data' mod='lemonway'}
						</label>								
					</div>
				</div>
				<div>
					<div class="radio">
						<label  for="lw_no_use_card">
							<input id="lw_no_use_card"  type="radio" name="lw_oneclic" value="no_use_card"  />
							{l s='Not use recorded card data' mod='lemonway'}
						</label>
					</div>
				</div>
			    <br />
			{/if}
			<button type="submit" name="lwPay" class="button btn btn-default standard-checkout button-medium">
				<span>
					{l s='Place order' mod='lemonway'}
					<i class="icon-chevron-right right"></i>
				</span>
			</button>
		</form>
	</div>
</div>
	{else}
		<p class="payment_module" id="Lemonway_payment_button">
				<a href="{$link->getModuleLink('lemonway', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay with Lemonway' mod='lemonway'}">
					<img src="{$module_dir|escape:'html':'UTF-8'}views/img/paiement-mode.jpg" class="img-responsive" alt="{l s='Pay with Lemonway' mod='lemonway'}" width="500px" id="payment-lemonway-logo" />
					{l s='Pay with Lemonway' mod='lemonway'}
				</a>

		</p>
    {/if}
	</div>
</div>
