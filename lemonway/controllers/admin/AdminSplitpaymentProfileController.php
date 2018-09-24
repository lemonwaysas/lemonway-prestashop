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
 * @author Lemon Way <it@lemonway.com>
 * @copyright  2017 Lemon way
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->list_no_link = false;
        $this->allow_export = false;
        $this->deleted = false;
        $this->context = Context::getContext();
        $this->multiple_fieldsets = true;
        $this->addRowAction('edit');
        $this->_orderBy = 'id_profile';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;
        $this->bulk_actions = array();

        $this->fields_list = array(
            'id_profile' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'name' => array(
                'title' => $this->l('Name')
            ),
            'active' => array(
                'title' => $this->l('Enabled'),
                'active' => 'status',
                'type' => 'bool',
                'class' => 'fixed-width-sm'
            )
        );

        $this->module = Module::getInstanceByName('lemonway');

        parent::__construct();
    }

    public function initToolbar()
    {
        parent::initToolbar();

        if (isset($this->toolbar_btn['new'])) {
            $this->toolbar_btn['new']['desc'] = $this->l('Split payment profile');
        }
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        $this->page_header_toolbar_title = $this->l(' Split payment profile');

        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_splitpayment_profile'] = array(
                'href' => self::$currentIndex . '&addlemonway_splitpayment_profile&token=' . $this->token,
                'desc' => $this->l('Add Splitpayment profile'),
                'icon' => 'process-icon-new'
            );
        }

        if ($this->display == 'add') {
            unset($this->page_header_toolbar_btn['save']);
        }

        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP
            && isset($this->page_header_toolbar_btn['new_splitpayment_profile']) && Shop::isFeatureActive()
        ) {
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
            $this->toolbar_title[] = $this->l('New split payment profile');
            $this->addMetaTitle($this->l('New split payment profile'));
        } else {
            $this->toolbar_title = array();
            $this->toolbar_title[] = $this->l('Split payment profiles');
            $this->addMetaTitle($this->l('Split payment profiles'));
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

        $profileForm = array();
        $profileForm['form'] = array(
            'legend' => array(
                'title' => $this->l('New payment profile'),
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'id_employee',
                    'lang' => false,
                    'disabled' => false,
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'is_admin',
                    'lang' => false,
                    'disabled' => false,
                ),
                array(
                    'col' => 3,
                    'type' => 'text',
                    'name' => 'name',
                    'label' => $this->l('Name'),
                    'lang' => false,
                    'disabled' => false,
                    'required' => true
                ),
                array(
                    'col' => 3,
                    'type' => 'select',
                    'options' => array(
                        'query' => SplitpaymentProfile::getAllPeriodUnits(),
                        'id' => 'value',
                        'name' => 'name'
                    ),
                    'identifier' => 'value',
                    'name' => 'period_unit',
                    'label' => $this->l('Period Unit'),
                    'lang' => false,
                    'disabled' => false,
                    'required' => false,
                    'desc' => $this->l('Unit for billing during the subscription period.')
                ),
                array(
                    'col' => 3,
                    'type' => 'text',
                    'name' => 'period_frequency',
                    'label' => $this->l('Period Frequency'),
                    'lang' => false,
                    'disabled' => false,
                    'required' => true,
                    'desc' => $this->l('Number of billing periods that make up one billing cycle.')
                ),
                array(
                    'col' => 3,
                    'type' => 'text',
                    'name' => 'period_max_cycles',
                    'label' => $this->l('Period Max Cycles'),
                    'lang' => false,
                    'disabled' => false,
                    'required' => true,
                    'desc' => $this->l('The number of billing cycles for payment period.')
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        $switch = array(
            'type' => 'switch',
            'label' => $this->l('Enabled'),
            'name' => 'active',
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled')
                )
            ),
            'required' => false,
            'desc' => "[" . $this->l('Period Max Cycles') . "] " . $this->l("times, one every ") .
                "[" . $this->l('Period Frequency') . "]" . " [" . $this->l('Period Unit') . "]"
        );

        //Backward compatibility with version < 1.6.
        //Switch type not exists

        if (version_compare(_PS_VERSION_, "1.6.0.0") == -1) {
            $switch = array(
                'type' => 'select',
                'label' => $this->l('Enabled'),
                'name' => 'active',
                'is_bool' => true,
                'options' => array(
                    'query' => array(
                        array(
                            'id' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'id' => 'id',
                    'name' => 'label'
                ),
                'required' => true
            );
        }

        $profileForm['form']['input'][] = $switch;

        $this->fields_form['form'] = $profileForm;

        $this->fields_value = array(
            'id_employee' => $this->context->employee->id,
            'is_admin' => 1,
        );

        return parent::renderForm();
    }

    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        $module = Module::getInstanceByName('lemonway');
        return $module->l($string, 'ADMINSPLITPAYMENTPROFILECONTROLLER', $class, $addslashes, $htmlentities);
    }
}
