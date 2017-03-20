<div class="row">
	<div class="col-xs-12 col-md-12">
	{if $data['oneclic_allowed'] == 1}
<div class="payment_module" id="lemonway_creditcard_payment_form">
	<div id="lemonway_creditcard_payment_form_container">
		<img src="{$module_dir|escape:'html':'UTF-8'}views/img/paiement-mode.jpg" width="500px" class="img-responsive"  alt="{l s='Pay with Lemonway' mod='lemonway'}" id="payment-lemonway-logo" />
    	<form action="{$link->getModuleLink('lemonway', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" method="POST" >
			{if $data['customer_has_card'] == 0}
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
					<label>{l s='Actual card' mod='lemonway'} : <span>{$data['card_num']|escape:'html':'UTF-8'}</span></label>			
				</div>
				{if $data['card_exp'] != ''}
					<div class="">
						<label>{l s='Expiration date' mod='lemonway'} : {$data['card_exp']|escape:'html':'UTF-8'}</label>
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
		<p class="payment_module" id="lemonway_creditcard_payment_button">
				<a href="{$link->getModuleLink('lemonway', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay with Lemonway' mod='lemonway'}">
					<img src="{$module_dir|escape:'html':'UTF-8'}views/img/paiement-mode.jpg" class="img-responsive" alt="{l s='Pay with Lemonway' mod='lemonway'}" width="500px" id="payment-lemonway-logo" />
					{l s='Pay with Lemonway' mod='lemonway'}
				</a>

		</p>
    {/if}
	</div>
</div>
