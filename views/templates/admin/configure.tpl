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

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li class="active"><a href="#about_us" role="tab" data-toggle="tab">{l s='About us' mod='lemonway'}</a></li>
    <li class=""><a href="#access_api" role="tab" data-toggle="tab">{l s='Account configuration' mod='lemonway'}</a></li>
    {foreach from=$methodForms item=method key=methodCode}
    <li class=""><a href="#method_{$methodCode}" role="tab" data-toggle="tab">{$method['title']}</a></li>
    {/foreach}
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div class="tab-pane active" id="about_us">
        {include file='./about_us.tpl'}
    </div>
    
    <div class="tab-pane" id="access_api">
        {$api_configuration_form}
    </div>
    
    {foreach from=$methodForms item=method key=methodCode}
    <div class="tab-pane" id="method_{$methodCode}">
        {$method['form']}
    </div>
    {/foreach}

    <div class="text-right">
        <small>v{$module_version}</small>
    </div>
</div>
