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
 * @author Lemon Way <it@lemonway.com>
 * @copyright  2017 Lemon way
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *}

{if isset($shop_name)}
    <h3>{l s='Your order on %s is complete.' sprintf=$shop_name mod='lemonway'}</h3>
{else}
    <h3>{l s='Your order is complete.' mod='lemonway'}</h3>
{/if}

<p>
    <br />- {l s='Amount' mod='lemonway'} : <span class="price"><strong>{$total|escape:'htmlall':'UTF-8'}</strong></span>
    <br />- {l s='Reference' mod='lemonway'} : <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
    <br /><br />{l s='An email has been sent with this information.' mod='lemonway'}
    <br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='lemonway'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='lemonway'}</a>
</p>
<hr />