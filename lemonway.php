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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once 'services/LemonWayConfig.php';
require_once 'classes/SplitpaymentProfile.php';
require_once 'classes/methods/Cc.php';
require_once 'classes/methods/CcXtimes.php';
require_once 'classes/methods/Check.php';

class Lemonway extends PaymentModule
{

    const DEBUG_MODE = true;
    const LEMONWAY_PENDING_OS = 'LEMONWAY_PENDING_OS';
    const LEMONWAY_SPLIT_PAYMENT_OS = 'LEMONWAY_SPLIT_PAYMENT_OS';

    protected $config_form = false;
    protected $current_card = null;
    protected $splitpaymentProfiles = null;

    protected $supportedLangs = array(
        'da' => 'da',
        'de' => 'ge',
        'en' => 'en',
        'es' => 'sp',
        'fi' => 'fi',
        'fr' => 'fr',
        'it' => 'it',
        'ko' => 'ko',
        'no' => 'no',
        'pt' => 'po',
        'sv' => 'sw'
    );
    protected $defaultLang = 'en';

    /**
     * @since 1.5.0.1
     * @var string Module local path (eg. '/home/prestashop/modules/modulename/')
     */
    protected $local_path = null;

    /** @var bool */
    public static $is_active = 1;

    public static $statuesLabel = array(
        1 => "Document uniquement reçu",
        2 => "Document vérifié et accepté",
        3 => "Document vérifié mais non accepté",
        4 => "Document remplacé par un autre document",
        5 => "Validité du document expiré"
    );

    public static $subMethods = array(
        'CC' => array(
            'classname' => 'Cc',
            "code" => 'CC',
            "title" => 'Credit Card',
            'template' => '../front/methods/creditcard.tpl'
        ),
        'CC_XTIMES' => array(
            'classname' => 'CcXtimes',
            "code" => 'CC_XTIMES',
            "title" => 'Credit Card (Split Payment)',
            'template' => '../front/methods/creditcard.tpl'
        ),
        /*'CHECK' => array(
            'classname'=>'Check',
            "code"=>'CHECK',
            "title"=>'Check',
            'template'=>'../front/methods/check.tpl'
        ),*/
    );

    public function __construct()
    {
        $this->name = 'lemonway';
        $this->tab = 'payments_gateways';
        $this->version = '1.3.2';
        $this->author = 'SIRATECK';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        $this->module_key = 'f342e3b756786cb82b45c362cadd2813';

        parent::__construct();

        $this->displayName = $this->l('Lemon Way for E-commerce');
        $this->description = $this->l('A one minute integration for the cheapest payment solution in Europe. Accept payment by credit cards from all around the world.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall? You will lose your datas!');
        $this->limited_countries = array();
        $this->local_path = _PS_MODULE_DIR_ . $this->name . '/';

        /* Backward compatibility */
        if (_PS_VERSION_ < '1.5') {
            require(_PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php');
        }
    }

    public function installModuleTab($tabClass, $translations, $idTabParent, $moduleName = null)
    {
        @copy(_PS_MODULE_DIR_ . $this->name . '/logo.png', _PS_IMG_DIR_ . 't/' . $tabClass . '.png');
        /* @var $tab TabCore */
        $tab = new Tab();
        foreach (Language::getLanguages(false) as $language) {
            if (isset($translations[Tools::strtolower($language['iso_code'])])) {
                $tab->name[(int)$language['id_lang']] = $translations[Tools::strtolower($language['iso_code'])];
            } else {
                $tab->name[(int)$language['id_lang']] = $translations['en'];
            }
        }

        $tab->class_name = $tabClass;
        if (is_null($moduleName)) {
            $moduleName = $this->name;
        }

        $tab->module = $moduleName;
        $tab->id_parent = $idTabParent;
        if (!$tab->save()) {
            return false;
        }

        return true;
    }

    public function uninstallModuleTab($tabClass)
    {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $tab->delete();
            @unlink(_PS_IMG_DIR . "t/" . $tabClass . ".png");
            return true;
        }
        return false;
    }

    /**
     * @param string $key Configuration key
     * @param array $translations array of name by lang
     * @param string $color Hex code or color's name
     * @param bool $hidden
     * @param bool $delivery
     * @param bool $logable
     * @param bool $invoice
     * @return boolean
     */
    protected function addStatus(
        $key,
        $translations,
        $color = 'royalblue',
        $hidden = false,
        $delivery = false,
        $logable = false,
        $invoice = false,
        $pdf_invoice = false,
        $paid = false,
        $send_email = false
    )
    {
        if (!Configuration::get($key)) {
            $os = new OrderState();
            $os->name = array();
            foreach (Language::getLanguages(false) as $language) {
                if (isset($translations[Tools::strtolower($language['iso_code'])])) {
                    $os->name[(int)$language['id_lang']] = $translations[Tools::strtolower($language['iso_code'])];
                } else {
                    $os->name[(int)$language['id_lang']] = $translations['en'];
                }
            }

            $os->color = $color;
            $os->hidden = $hidden;
            $os->send_email = $send_email;
            $os->delivery = $delivery;
            $os->logable = $logable;
            $os->invoice = $invoice;
            $os->pdf_invoice = $pdf_invoice;
            $os->paid = $paid;
            $os->module_name = $this->name;

            if ($os->add()) {
                Configuration::updateValue($key, $os->id);
                copy(
                    dirname(__FILE__) . '/views/img/icon.gif',
                    dirname(__FILE__) . '/../../img/os/' . (int)$os->id . '.gif'
                );
            } else {
                return false;
            }
        }

        return true;
    }

    public function addStatusSplitpayment()
    {
        $translationsStatus = array(
            'en' => 'Split Payment accepted',
            'fr' => 'Paiement en plusieurs fois accepté'
        );

        return $this->addStatus(
            self::LEMONWAY_SPLIT_PAYMENT_OS,
            $translationsStatus,
            '#32CD32',
            false,
            false,
            true,
            true,
            true,
            false,
            true
        );
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        //API CONFIGURATION
        Configuration::updateValue('LEMONWAY_API_LOGIN', '');
        Configuration::updateValue('LEMONWAY_API_PASSWORD', '');
        Configuration::updateValue('LEMONWAY_MERCHANT_ID', LemonWayConfig::getWalletMerchantId());
        Configuration::updateValue('CUSTOM_ENVIRONMENT_NAME', '');
        Configuration::updateValue('LEMONWAY_IS_TEST_MODE', false);


        //METHOD CONFIGURATION
        Configuration::updateValue(
            'LEMONWAY_CSS_URL',
            'https://webkit.lemonway.fr/css/mercanet/mercanet_lw_custom.css'
        );

        //COMMON CREDIT CARD Configuration
        foreach (self::$subMethods as $method) {
            Configuration::updateValue('LEMONWAY_' . Tools::strtoupper($method['code']) . '_ONECLIC_ENABLED', null);
            Configuration::updateValue('LEMONWAY_' . Tools::strtoupper($method['code']) . '_TITLE', $method['title']);
        }

        Configuration::updateValue('LEMONWAY_CC_ENABLED', 1);
        Configuration::updateValue('LEMONWAY_CC_XTIMES_ENABLED', null);

        //CREDIT CARD X TIMES (split)
        Configuration::updateValue('LEMONWAY_CC_XTIMES_SPLITPAYMENTS', null);

        //Prepare status values
        $key = self::LEMONWAY_PENDING_OS;

        $translationsAdminLemonway = array(
            'en' => 'Lemon Way',
            'fr' => 'Lemon Way'
        );

        $this->installModuleTab('AdminLemonway', $translationsAdminLemonway, 0);

        $translationsStatus = array(
            'en' => 'Pending payment validation from Lemonway',
            'fr' => 'En attente de validation par Lemonway'
        );

        $translationsAdminMoneyOut = array(
            'en' => 'Money out',
            'fr' => 'Virements bancaire'
        );

        $adminLemonwayId = Db::getInstance()->getValue(
            "SELECT `id_tab` FROM " . _DB_PREFIX_ . "tab WHERE `class_name` = 'AdminLemonway'"
        );

        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentReturn') &&
            $this->addStatus($key, $translationsStatus, 'orange') && //Add new Status
            $this->installModuleTab('AdminMoneyOut', $translationsAdminMoneyOut, $adminLemonwayId) &&
            installSQL($this);
    }

    public function uninstall()
    {
        //API CONFIGURATION
        Configuration::deleteByName('LEMONWAY_API_LOGIN');
        Configuration::deleteByName('LEMONWAY_API_PASSWORD');
        Configuration::deleteByName('LEMONWAY_MERCHANT_ID');
        Configuration::deleteByName('CUSTOM_ENVIRONMENT_NAME');
        Configuration::deleteByName('LEMONWAY_IS_TEST_MODE');
        Configuration::deleteByName('LEMONWAY_CSS_URL');

        //METHOD CONFIGURATION
        Configuration::deleteByName('LEMONWAY_ONECLIC_ENABLED'); //Keeped for old module versions

        //COMMON CREDIT CARD Configuration
        foreach (self::$subMethods as $method) {
            Configuration::deleteByName('LEMONWAY_' . Tools::strtoupper($method['code']) . '_ONECLIC_ENABLED');
            Configuration::deleteByName('LEMONWAY_' . Tools::strtoupper($method['code']) . '_ENABLED');
            Configuration::deleteByName('LEMONWAY_' . Tools::strtoupper($method['code']) . '_TITLE');
            Configuration::deleteByName('LEMONWAY_' . Tools::strtoupper($method['code']) . '_SPLITPAYMENTS');
        }


        //CREDIT CARD X TIMES (split)
        Configuration::deleteByName('LEMONWAY_SPLITPAYMENT_IS_RUNNING');
        // Configuration::deleteByName('LEMONWAY_SPLIT_PAYMENT_OS');

        //Do Not delete this configuration
        //Configuration::deleteByName('LEMONWAY_PENDING_OS');

        $this->uninstallModuleTab('AdminMoneyOut');
        $this->uninstallModuleTab('AdminLemonway');
        $this->uninstallModuleTab('AdminSplitpaymentProfile');
        $this->uninstallModuleTab('AdminSplitpaymentDeadline');

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues($formCode)
    {
        $formCode = Tools::strtoupper($formCode);
        $apilogin = Configuration::get('LEMONWAY_API_LOGIN', null);
        $wallet = $this->getWalletDetails($apilogin);
        switch ($formCode) {
            case 'API':
                return array(
                    'LEMONWAY_API_LOGIN' => Configuration::get('LEMONWAY_API_LOGIN', null),
                    'LEMONWAY_API_PASSWORD' => Configuration::get('LEMONWAY_API_PASSWORD', null),
                    'LEMONWAY_MERCHANT_ID' => $wallet->WALLET->ID,
                    'CUSTOM_ENVIRONMENT_NAME' => Configuration::get('CUSTOM_ENVIRONMENT_NAME', null),
                    'LEMONWAY_IS_TEST_MODE' => Configuration::get('LEMONWAY_IS_TEST_MODE', null),
                    'LEMONWAY_CSS_URL' => Configuration::get('LEMONWAY_CSS_URL', null)
                );

            case 'CC_XTIMES':
                //Manage checkboxes splitpayment profiles
                $splitpaymentIds = explode(',', Configuration::get('LEMONWAY_' . $formCode . '_SPLITPAYMENTS', ''));
                $splitpaymentFormValues = array();
                if (count($splitpaymentIds)) {
                    foreach ($splitpaymentIds as $id) {
                        $splitpaymentFormValues['LEMONWAY_' . $formCode . '_SPLITPAYMENTS_' . $id] = $id;
                    }
                }

                return array_merge(
                    array(
                        'LEMONWAY_' . $formCode . '_ENABLED' => Configuration::get(
                            'LEMONWAY_' . $formCode . '_ENABLED',
                            null
                        ),
                        'LEMONWAY_' . $formCode . '_TITLE' => Configuration::get(
                            'LEMONWAY_' . $formCode . '_TITLE',
                            self::$subMethods[$formCode]['title']
                        ),
                        'LEMONWAY_' . $formCode . '_ONECLIC_ENABLED' => Configuration::get(
                            'LEMONWAY_' . $formCode . '_ONECLIC_ENABLED',
                            null
                        ),
                        'LEMONWAY_' . $formCode . '_SPLITPAYMENTS' => Configuration::get(
                            'LEMONWAY_' . $formCode . '_SPLITPAYMENTS',
                            ''
                        )
                    ),
                    $splitpaymentFormValues
                );

            default:
                return array(
                    //CREDIT CARD
                    'LEMONWAY_' . $formCode . '_ENABLED' => Configuration::get(
                        'LEMONWAY_' . $formCode . '_ENABLED',
                        null
                    ),
                    'LEMONWAY_' . $formCode . '_TITLE' => Configuration::get(
                        'LEMONWAY_' . $formCode . '_TITLE',
                        self::$subMethods[$formCode]['title']
                    ),
                    'LEMONWAY_' . $formCode . '_ONECLIC_ENABLED' => Configuration::get(
                        'LEMONWAY_' . $formCode . '_ONECLIC_ENABLED',
                        null
                    )
                );
        }
    }

    /**
     * Save form data.
     */
    protected function postProcess($formCode)
    {
        $formCode = Tools::strtoupper($formCode);
        $form_values = $this->getConfigFormValues($formCode);

        foreach (array_keys($form_values) as $key) {
            $value = Tools::getValue($key);

            switch ($formCode) {
                case 'API':
                    if ($key == 'LEMONWAY_API_PASSWORD' && trim($value) == "") {
                        continue;
                    }

                    if ($key != 'LEMONWAY_API_PASSWORD') {
                        $value = trim($value);
                    }
                    break;

                case 'CC_XTIMES':
                    if (strpos($key, 'LEMONWAY_CC_XTIMES_SPLITPAYMENTS_') !== false) {
                        continue;
                    }
                    //manage checkbox
                    if ($key == 'LEMONWAY_CC_XTIMES_SPLITPAYMENTS') {
                        $values = array();

                        if (!empty($form_values[$key])) {
                            $values = explode(',', $form_values[$key]);
                        }

                        foreach ($this->getSplitpaymentProfiles() as $profile) {
                            $value = Tools::getValue($key . '_' . $profile['id_profile']);
                            //die('value: '. $value);
                            if ($value == 'on' && !in_array($profile['id_profile'], $values)) {   //Add new profile
                                $values[] = $profile['id_profile'];
                            } elseif ($value != 'on' && in_array($profile['id_profile'], $values)) { //remove profile
                                $index = array_search($profile['id_profile'], $values);
                                unset($values[$index]);
                            }
                        }

                        $value = implode(',', $values);
                    }
                    break;

                default:
            }

            Configuration::updateValue($key, $value);
        }
    }

    public function moduleMktIsInstalled()
    {
        return !(Module::isInstalled('lemonwaymkt') === false);
    }

    public function moduleMktIsEnabled()
    {
        return !(Module::isEnabled('lemonwaymkt') === false);
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitLemonwayApiConfig')) == true) {
            $this->postProcess('API');
        }

        foreach (self::$subMethods as $methodCode => $method) {
            if (((bool)Tools::isSubmit('submitLemonwayMethodConfig_' . Tools::strtoupper($methodCode))) == true) {
                $this->postProcess($methodCode);
            }
        }

        $this->context->smarty->assign('module_version', $this->version);
        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('api_configuration_form', $this->renderForm('API'));

        $methodForms = array();
        foreach (self::$subMethods as $methodCode => $method) {
            $configurationKey = $methodCode;
            $methodForms[$methodCode] = array(
                'form' => $this->renderForm($configurationKey),
                'title' => $this->l($method['title'])
            );

            //$this->context->smarty->assign($configurationKey.'_form', $this->renderForm($configurationKey));
        }

        $this->context->smarty->assign('methodForms', $methodForms);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output;// . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm($type)
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitLemonwayModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues($type), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $form = '';

        switch ($type) {
            case 'API':
                $form = $helper->generateForm(
                    $this->getApiConfigForm()
                );
                break;

            case 'CC':
                $form = $helper->generateForm(array(
                    $this->getBaseMethodCcConfigForm($type)
                ));
                break;

            case 'CC_XTIMES':
                $splitpaymentProfiles = $this->getSplitpaymentProfiles();
                $baseFrom = $this->getBaseMethodCcConfigForm($type);
                $adminSplitPaymentTabUrl = $this->context->link->getAdminLink('AdminSplitpaymentProfile', true);
                $fieldPaymentProfile = array(
                    'type' => 'checkbox',
                    'label' => $this->l('Split Payment profile'),
                    'desc' => $this->l('Choose split payment to show in front') . "<br/>" . sprintf('<a href="%s">%s</a>', $adminSplitPaymentTabUrl, $this->l('Create a split payment profile')),
                    'name' => 'LEMONWAY_CC_XTIMES_SPLITPAYMENTS',
                    'values' => array(
                        'query' => $splitpaymentProfiles,
                        'id' => 'id_profile',
                        'name' => 'name'
                    ),
                    'expand' => array(
                        'print_total' => count($splitpaymentProfiles),
                        'default' => 'hide',
                        'show' => array('text' => $this->l('show'), 'icon' => 'plus-sign-alt'),
                        'hide' => array('text' => $this->l('hide'), 'icon' => 'minus-sign-alt')
                    )
                );
                $inputArr = $baseFrom['form']['input'];

                array_splice($inputArr, 2, 0, array($fieldPaymentProfile));

                $baseFrom['form']['input'] = $inputArr;

                //Create description
                $cronUrl = $this->context->link->getModuleLink('lemonway', 'cron', array(), true);
                $description =
                    $this->l('To use split payment, you need to schedule a cron task to perform a request on ') .
                    sprintf('<a href="%s">%s</a>', $cronUrl, $cronUrl) . '<br/>';
                $description .= sprintf('E.g: "0 1 * * * wget <a href="%s">%s</a>". ', $cronUrl, $cronUrl) .
                    $this->l('Execute a request every day at 01h00');
                $baseFrom['form'] = array('description' => $description) + $baseFrom['form'];

                if (!count($splitpaymentProfiles)) {
                    $adminSplitPaymentTabUrl = $this->context->link->getAdminLink('AdminSplitpaymentProfile', true);
                    $warningMessage = sprintf(
                        '<a href="%s">' .
                        $this->l('Create a split payment profile') .
                        '</a>',
                        $adminSplitPaymentTabUrl
                    );
                    $baseFrom['form'] = array('warning' => $warningMessage) + $baseFrom['form'];
                }

                $form = $helper->generateForm(array(
                    $baseFrom
                ));

                break;

            default:
                $form = $helper->generateForm(array($this->getBaseMethodConfigForm($type)));
        }

        return $form;
    }

    protected function getBaseMethodCcConfigForm($methodCode)
    {
        $methodCode = Tools::strtoupper($methodCode);
        $container = $this->getBaseMethodConfigForm($methodCode);

        $switch = array(
            'type' => 'switch',
            'label' => $this->l('Enable Oneclic'),
            'name' => 'LEMONWAY_' . $methodCode . '_ONECLIC_ENABLED',
            'is_bool' => true,
            'class' => 't',
            'desc' => $this->l('Display oneclic form on payment step'),
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
            )
        );

        //Backward compatibility with version < 1.6.
        //Switch type not exists
        if (version_compare(_PS_VERSION_, "1.6.0.0") == -1) {
            $switch = array(
                'type' => 'select',
                'label' => $this->l('Enable Oneclic'),
                'name' => 'LEMONWAY_' . $methodCode . '_ONECLIC_ENABLED',
                'is_bool' => true,
                'desc' => $this->l('Display oneclic form on payment step'),
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
            );
        }

        $container['form']['input'][] = $switch;

        return $container;
    }

    /**
     * Create the structure of api informations form.
     */
    protected function getBaseMethodConfigForm($methodCode)
    {
        $methodCode = Tools::strtoupper($methodCode);
        $container = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('METHOD CONFIGURATION'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $switchEnabled = array(
            'type' => 'switch',
            'label' => $this->l('Enabled'),
            'name' => 'LEMONWAY_' . $methodCode . '_ENABLED',
            'is_bool' => true,
            'class' => 't',
            'desc' => $this->l('Display this method form on payment step'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('yes')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            )
        );

        //Backward compatibility with version < 1.6.
        //Switch type not exists
        if (version_compare(_PS_VERSION_, "1.6.0.0") == -1) {
            $switchEnabled = array(
                'type' => 'select',
                'label' => $this->l('Enabled'),
                'name' => 'LEMONWAY_' . $methodCode . '_ENABLED',
                'is_bool' => true,
                'desc' => $this->l('Display this method form on payment step'),
                'options' => array(
                    'query' => array(
                        array(
                            'id' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'id' => 'id',
                    'name' => 'label'
                ),
            );
        }

        $container['form']['input'][] = $switchEnabled;

        //Add title field
        $container['form']['input'][] = array(
            'col' => 3,
            'type' => 'text',
            'label' => $this->l('Title'),
            'name' => 'LEMONWAY_' . $methodCode . '_TITLE',
        );

        $container['form']['submit'] = array(
            'title' => $this->l('Save'),
            'name' => 'submitLemonwayMethodConfig_' . $methodCode
        );

        return $container;
    }

    /**
     * Create the structure of api informations form.
     */
    protected function getApiConfigForm()
    {
        $form_config = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('ACCOUNT CONFIGURATION'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-user"></i>',
                        'desc' => "<a href='https://www.lemonway.com/ecommerce' target='_blank'>" .
                            $this->l('Create account') . "</a>",
                        'name' => 'LEMONWAY_API_LOGIN',
                        'label' => $this->l('Login'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'password',
                        'desc' => "<a href='" . $this->l('https://ecommerce.lemonway.com/en/seller/lost-password') .
                            "' target='_blank'>" . $this->l('Forgotten password?') . "</a>",
                        'name' => 'LEMONWAY_API_PASSWORD',
                        'label' => $this->l('Password'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitLemonwayApiConfig'
                ),
            ),
        );

        $switch = array(
            'type' => 'switch',
            'label' => $this->l('Test mode?'),
            'name' => 'LEMONWAY_IS_TEST_MODE',
            'is_bool' => true,
            'class' => 't',
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
        );

        //Backward compatibility with version < 1.6.
        //Switch type not exists

        if (version_compare(_PS_VERSION_, "1.6.0.0") == -1) {
            $switch = array(
                'type' => 'select',
                'label' => $this->l('Test mode?'),
                'name' => 'LEMONWAY_IS_TEST_MODE',
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
            );
        }

        $form_config_advanced = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('ADVANCED ACCOUNT CONFIGURATION'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 6,
                        'label' => $this->l('Payment page CSS URL'),
                        'name' => 'LEMONWAY_CSS_URL',
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-css3"></i>',
                        'desc' => $this->l('Customise the stylesheet of the payment page') . " " .
                            $this->l('(Notice: If your website is in https, the CSS URL has to be in https too)'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-leaf"></i>',
                        'desc' => $this->l('If you have a specific environment with Lemon Way'),
                        'name' => 'CUSTOM_ENVIRONMENT_NAME',
                        'label' => $this->l('Custom environment name'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitLemonwayApiConfig'
                ),
            ));

        $form_config['form']['input'][] = $switch;

        return array($form_config, $form_config_advanced);
    }

    public function getSplitpaymentProfiles()
    {
        if (is_null($this->splitpaymentProfiles)) {
            $this->splitpaymentProfiles = SplitpaymentProfile::getProfiles();
        }

        return $this->splitpaymentProfiles;
    }

    public function methodFactory($methodCode)
    {
        return self::methodInstanceFactory($methodCode);
    }

    public static function methodInstanceFactory($methodCode)
    {
        //Create method instance and return it
        $methodClassName = self::$subMethods[$methodCode]['classname'];
        return new $methodClassName();
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            if ($this->isVersion17() && method_exists($this->context->controller, 'setMedia')) {
                $this->context->controller->setMedia(true);
            }
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
        }

        $this->context->controller->addCSS($this->_path . 'views/css/back.css');
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
        $methodsEnabled = array();

        foreach (self::$subMethods as $method) {
            //Create method instance
            $methodInstance = $this->methodFactory($method['code']);

            //Check if method is enbaled
            if ($methodInstance->isValid()) {
                $methodsEnabled[$method['code']] = $methodInstance;
            }
        }

        $this->smarty->assign(array(
            'module_dir' => $this->_path,
            'methodsEnabled' => $methodsEnabled,
            'open_basedir' => (ini_get('open_basedir') == '') ? "1" : "0"
        ));

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    /**
     * This hook is used to pass options to payment method in version >= 1.7
     */
    public function hookPaymentOptions($params)
    {
        $options = array();

        foreach (self::$subMethods as $method) {
            //Create method instance
            $methodInstance = $this->methodFactory($method['code']);

            //Check if method is enbaled
            if ($methodInstance->isValid()) {
                $this->context->smarty->assign(array(
                    'module_dir' => $this->_path,
                    'method' => $methodInstance,
                    'open_basedir' => (ini_get('open_basedir') == '') ? "1" : "0"
                ));

                /*$inputs = array(
                            'method_code' => array(
                                        'name' =>'method_code',
                                        'type' =>'hidden',
                                        'value' =>$methodInstance->getCode(),
                            ));*/

                $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
                $newOption
                    ->setCallToActionText($methodInstance->getTitle())
                    ->setLogo(Media::getMediaPath($this->local_path . 'views/img/paiement-mode-17.png'))
                    ->setModuleName($this->name)
                    ->setForm($this->context->smarty->fetch($methodInstance->getTemplate()));

                $options[] = $newOption;
            }
        }

        return $options;
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        if ($this->active == false) {
            return;
        }

        $order = isset($params['objOrder']) ? $params['objOrder'] : $params['order'];
        $total_to_pay = $order->getOrdersTotalPaid();
        $currency = new Currency($order->id_currency);

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($total_to_pay, $currency, false),
        ));

        return $this->getHookPaymentReturnOutput();
    }

    public function getHookPaymentReturnOutput()
    {
        if ($this->isVersion17()) {
            return $this->fetch('module:' . $this->name . '/views/templates/hook/confirmation.tpl');
        }

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    /**
     * This hook is used to add color to splitpayment deadlines results.
     * @param array $params
     */
    public function hookActionAdminSplitpaymentDeadlineListingResultsModifier($params)
    {
        $list = &$params['list'];

        foreach ($list as $index => $tr) {
            switch ($tr['status']) {
                case "failed":
                    $list[$index]['color'] = 'red';
                    break;
                case 'complete':
                    $list[$index]['color'] = 'green';
                    break;
                case 'pending':
                    $list[$index]['color'] = 'orange';
                    break;
            }
        }
    }

    public function getCustomerCard($id_customer)
    {
        if (is_null($this->current_card)) {
            $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'lemonway_oneclic` lo WHERE lo.`id_customer` = '
                . (int)pSQL($id_customer);
            $this->current_card = Db::getInstance()->getRow($query);
        }

        return $this->current_card;
    }

    public function insertOrUpdateCard($id_customer, $data)
    {
        $oldCard = $this->getCustomerCard($id_customer);

        if ($oldCard) {
            $oldCard['id_oneclic'] = (int)$oldCard['id_oneclic'];
            $data = array_merge($oldCard, $data);
            $data['date_upd'] = date('Y-m-d H:i:s');
        } else {
            $data['date_add'] = date('Y-m-d H:i:s');
        }

        // Escape data
        foreach ($data as $key => $value) {
            $data[$key] = pSQL($value);
        }
        $data['id_customer'] = (int)$data['id_customer'];
        $data['id_card'] = (int)$data['id_card'];

        Db::getInstance()->insert('lemonway_oneclic', $data, false, true, Db::REPLACE);
    }

    public function getWalletDetails($wallet)
    {

        $params = array("email" => $wallet);

        $kit = new LemonWayKit();
        try {
            $res = $kit->getWalletDetails($params);

        } catch (Exception $e) {
            throw $e;
        }

        return $res;
    }

    public function getWkToken($id_cart)
    {
        return Db::getInstance()->getValue(
            'SELECT `wktoken` FROM `' . _DB_PREFIX_ . 'lemonway_wktoken` lw WHERE lw.`id_cart` = ' . (int)pSQL($id_cart)
        );
    }

    public function checkIfCartHasWkToken($id_cart)
    {
        return (bool)$this->getWkToken($id_cart);
    }

    /**
     * Insert or Update new unique wkToken
     * @param int $id_cart
     * @return string $wkToken
     */
    public function saveWkToken($id_cart)
    {
        $wkToken = $this->generateUniqueCartId($id_cart);

        //Default  update query
        $query = 'UPDATE `' . _DB_PREFIX_ . 'lemonway_wktoken` SET `wktoken` = \'' . pSQL($wkToken) .
            "' WHERE `id_cart` = " . (int)pSQL($id_cart);

        //If cart haven't wkToken we insert it
        if (!$this->checkIfCartHasWkToken($id_cart)) {
            $query = 'INSERT INTO `' . _DB_PREFIX_ . 'lemonway_wktoken` (`id_cart`, `wktoken`) VALUES (\''
                . (int)pSQL($id_cart) . '\',\'' . pSQL($wkToken) . '\') ';
        }

        Db::getInstance()->execute($query);

        return $wkToken;
    }

    public function generateUniqueCartId($id_cart)
    {
        return $id_cart . "-" . time() . "-" . uniqid();
    }

    public function getCartIdFromToken($wktoken)
    {
        if ($id_cart = Db::getInstance()->getValue(
            'SELECT `id_cart` FROM `' . _DB_PREFIX_ . 'lemonway_wktoken` lw WHERE lw.`wktoken` = \''
            . pSQL($wktoken) . "'"
        )) {
            return $id_cart;
        }

        throw new Exception($this->l("Cart not found!"), 406);
    }

    public function ajaxPlaceOrder()
    {
        $cart = $this->context->cart;
        /* @var $customer CustomerCore */
        $customer = $this->context->customer;

        require_once _PS_MODULE_DIR_ . $this->name . '/services/LemonWayKit.php';
        $kit = new LemonWayKit();


        // Generate a new wkToken for this cart ID
        // It's necessary to send a new wkToken for each requests
        $wkToken = $this->saveWkToken($cart->id);
        $comment = Configuration::get('PS_SHOP_NAME') . " - " . $cart->id . " - " .
            $customer->lastname . " " . $customer->firstname . " - " . $customer->email;

        $secure_key = $customer->secure_key;
        $registerCard = (Tools::getValue('lw_oneclic') === 'register_card');

        //call directkit to get Webkit Token
        $params = array(
            'wkToken' => $wkToken,
            'wallet' => LemonWayConfig::getWalletMerchantId(),
            'amountTot' => number_format((float)$cart->getOrderTotal(true, 3), 2, '.', ''),
            'amountCom' => "0.00",
            'comment' => $comment,
            'returnUrl' => urlencode($this->context->link->getModuleLink('lemonway', 'validation', array(
                'register_card' => (int)$registerCard,
                'action' => 'return',
                'secure_key' => $secure_key
            ), true)),
            'cancelUrl' => urlencode($this->context->link->getModuleLink('lemonway', 'validation', array(
                'action' => 'cancel',
                'secure_key' => $secure_key
            ), true)),
            'errorUrl' => urlencode($this->context->link->getModuleLink('lemonway', 'validation', array(
                'action' => 'error',
                'secure_key' => $secure_key
            ), true)),
            'autoCommission' => LemonWayConfig::is4EcommerceMode() ? 0 : 1,
            'registerCard' => (int)$registerCard, //For Atos
            'useRegisteredCard' => (int)$registerCard, //For payline
        );
        
        try {
            $res = $kit->moneyInWebInit($params);
            var_dump($res);
            //Oops, an error occured.
            if (isset($res->lwError)) {
                throw new Exception((string)$res->lwError->MSG, (int)$res->lwError->CODE);
            }

            if ($customer->id && isset($res->lwXml->MONEYINWEB->CARD) && $registerCard) {
                $card = $this->getCustomerCard($customer->id);
                if (!$card) {
                    $card = array();
                }

                $card['id_customer'] = $customer->id;
                $card['id_card'] = (string)$res->lwXml->MONEYINWEB->CARD->ID;

                $this->insertOrUpdateCard($customer->id, $card);
            }
        } catch (Exception $e) {
            return $this->displayError($e->getMessage() . " - " . $e->getCode());
        }

        //moneyInToken
        $moneyInToken = (string)$res->lwXml->MONEYINWEB->TOKEN;

        //language
        $language = array_key_exists($this->context->language->iso_code, $this->supportedLangs) ?
            $this->supportedLangs[$this->context->language->iso_code] : $this->defaultLang;

        $cardForm = $kit->printCardForm($moneyInToken, urlencode(LemonWayConfig::getCssUrl()), $language);
        return $cardForm;
    }

    public function isVersion17()
    {
        return (bool)version_compare(_PS_VERSION_, '1.7', '>=');
    }
}
