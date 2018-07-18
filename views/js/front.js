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

$(function()
{
    /* $(".Lemonway_payment_btn").click(function() {
     $("#placeOrderForm").submit();
     $(this).removeClass("Lemonway_payment_btn");
     });*/

    //<!-- Display deadlines by profile selection -->
    showSelectedProfile();

    $('#lemonway_CC_XTIMES_splitpayment_profile_select').change(function () {
        showSelectedProfile();
    });

    function hideAll() {
        $('*[id^=profile_splitpayment_table_]').hide();
    }

    function showSelectedProfile() {
        hideAll();
        let selected_profile_id = $('*[name=splitpayment_profile_id]').val();
        $('#profile_splitpayment_table_' + selected_profile_id).show();
    }

    $(".lw_no_use_card,.lw_register_card").click(function() {
        $(this).parents('.lemonway-payment-oneclic-container').prev('.lw_container_cards_types').show();
    });

    $(".lw_use_card").click(function() {
        $(this).parents('.lemonway-payment-oneclic-container').prev('.lw_container_cards_types').hide();
    });
    
    $('input[data-module-name=lemonway]').parent().nextAll('label').find('img')
    .css('float', 'left')
    .css('width','150px');
});