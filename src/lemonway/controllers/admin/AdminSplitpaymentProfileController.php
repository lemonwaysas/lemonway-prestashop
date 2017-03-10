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

require_once _PS_MODULE_DIR_ . 'lemonway/classes/SplitpaymentProfile.php';

class AdminSplitpaymentProfileController extends ModuleAdminController
{
    
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'lemonway_splitpayment_profile';
        $this->identifier = 'id_profile';
        $this->className = 'SplitpaymentProfile';
        $this->lang = false;
        $this->list_no_link = true;
        $this->allow_export = false;
        $this->deleted = false;
        $this->context = Context::getContext();
        $this->multiple_fieldsets = false;
      /*   $this->explicitSelect = true;
        $this->_select = 'a.*, CONCAT(LEFT(e.`firstname`, 1), \'. \', e.`lastname`) AS `employee`';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'employee` e ON (e.`id_employee` = a.`id_employee`)';
         */
        $this->_orderBy = 'id_profile';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;

        $this->fields_list = array(
            'id_profile' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'name' => array(
                'title' => $this->l('Name')
            )
        );

        parent::__construct();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        if (isset($this->toolbar_btn['new'])) {
            $this->toolbar_btn['new']['desc'] = $this->l('Splitpayment profile');
        }
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_splitpayment_profile'] = array(
                'href' => self::$currentIndex.'&addlemonway_splitpayment_profile&token=' . $this->token,
                'desc' => $this->l('Splitpayment profile', null, null, false),
                'icon' => 'process-icon-new'
            );
        }

        if ($this->display == 'add') {
            unset($this->page_header_toolbar_btn['save']);
        }

        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP
            && isset($this->page_header_toolbar_btn['new_splitpayment_profile']) && Shop::isFeatureActive()) {
            unset($this->page_header_toolbar_btn['new_splitpayment_profile']);
        }
    }

    /**
     * Set default toolbar_title to admin breadcrumb
     *
     * @return void
     */
    public function initToolbarTitle()
    {
        parent::initToolbarTitle();
        
        if ($this->display == 'add') {
            $this->toolbar_title = array();
            $this->toolbar_title[] = $this->l('Add split payment profile', null, null, false);
            $this->addMetaTitle($this->l('Add split payment profile', null, null, false));
        }
    }
    

    /**
    * Object creation
    *
    * @return ObjectModel|false
    * @throws PrestaShopException
    */
    public function processAdd()
    {
        return parent::processAdd();
    }

    
    public function renderForm()
    {
        $this->display = 'add';

       /*  $this->fields_form['form'] =  [];

        $this->fields_value = array(
            'wallet' => $wallet->ID,
            'id_lw_wallet' => $wallet->ID,
            'holder' => $wallet->NAME,
            'holder_name' => $wallet->NAME,
            'bal' => $wallet->BAL,
            'prev_bal' => $wallet->BAL,
            'status' => isset( $this->statuesLabel[trim($wallet->STATUS)]) ?
                $this->statuesLabel[trim($wallet->STATUS)] : "N/A",
            'id_employee' => $this->context->employee->id,
            'is_admin' => 1,
        ); */

        return parent::renderForm();
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . "/views/js/back.js");
    }

}
