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

require_once _PS_MODULE_DIR_ . 'lemonway/classes/MoneyOut.php';
require_once _PS_MODULE_DIR_ . 'lemonway/services/LemonWayKit.php';
require_once _PS_MODULE_DIR_ . 'lemonway/services/ApiResponse.php';

class AdminMoneyOutController extends ModuleAdminController
{
    protected $walletDetails = null;
    protected $statuesLabel = array(
        "1" => "Document uniquement reçu",
        "2"  => "Document vérifié et accepté",
        "3"  => "Document vérifié mais non accepté",
        "4"  => "Document remplacé par un autre document",
        "5"  => "Validité du document expiré"
    );
    
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'lemonway_moneyout';
        $this->identifier = 'id_moneyout';
        $this->className = 'MoneyOut';
        $this->lang = false;
        $this->list_no_link = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();
        $this->multiple_fieldsets = true;
        $this->explicitSelect = true;
        $this->_select = 'a.*, CONCAT(LEFT(e.`firstname`, 1), \'. \', e.`lastname`) AS `employee`';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'employee` e ON (e.`id_employee` = a.`id_employee`)';
        $this->_orderBy = 'id_moneyout';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;

        $this->fields_list = array(
            'id_moneyout' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'id_lw_wallet' => array(
                'title' => $this->l('Wallet')
            ),
            'employee' => array(
                'title' => $this->l('Employee'),
                'havingFilter' => true,
            ),
            'iban' => array(
                'title' => $this->l('IBAN')
            ),
            'amount_to_pay' => array(
                'title' => $this->l('Amount'),
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'badge_success' => true
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'align' => 'text-right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add'
            ),
        );

        parent::__construct();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        if (isset($this->toolbar_btn['new'])) {
            $this->toolbar_btn['new']['desc'] = $this->l('Do new Money out');
        }
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_moneyout'] = array(
                'href' => self::$currentIndex.'&addlemonway_moneyout&token=' . $this->token,
                'desc' => $this->l('Do new Money out', null, null, false),
                'icon' => 'process-icon-new'
            );
        }

        if ($this->display == 'add') {
            unset($this->page_header_toolbar_btn['save']);
        }

        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP
            && isset($this->page_header_toolbar_btn['new_moneyout']) && Shop::isFeatureActive()) {
            unset($this->page_header_toolbar_btn['new_moneyout']);
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
            $this->toolbar_title[] = $this->l('Do a Money out', null, null, false);
            $this->addMetaTitle($this->l('Do a Money out', null, null, false));
        }
    }
    
    /**
     * @param MoneyOut $moneyOut
     * @return boolean
     */
    public function beforeAdd($moneyOut)
    {
        try {
            $wallet_detail = $this->getWalletDetails();

            if (is_null($wallet_detail)) {
                throw new PrestaShopException($this->module->l('Can\'t retrieve Wallet details'));
            }

            $wallet = $wallet_detail->wallet;

            $params = array(
                "wallet" => $moneyOut->id_lw_wallet,
                "amountTot" => number_format((float)$moneyOut->amount_to_pay, 2, '.', ''),
                'amountCom' => number_format((float)0, 2, '.', ''),
                "message" => Configuration::get('PS_SHOP_NAME') . " - " .
                 $this->module->l("Moneyout from Prestashop module"),
                "ibanId" => $moneyOut->id_lw_iban,
                "autCommission" => 0,
            );

            //Init APi kit
            $kit = new LemonWayKit();
            $apiResponse = $kit->moneyOut($params);

            if ($apiResponse->lwError) {
                throw new PrestaShopException((string)$apiResponse->lwError->MSG, (int)$apiResponse->lwError->CODE);
            }

            if (count($apiResponse->operations)) {
                /* @var $op Operation */
                $op = current($apiResponse->operations);
                if ($op->ID) {
                    $moneyOut->new_bal = (float)$wallet->BAL - (float)$moneyOut->amount_to_pay;
                    return true;
                } else {
                    throw new PrestaShopException($this->module->l("An error occurred. Please contact support."));
                }
            }
        } catch (Exception $e) {
            throw $e;
        }

        return false;
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
    
    public static function setMoneyOutCurrency($echo)
    {
        return Tools::displayPrice($echo, (int)Context::getContext()->currency->id_currency);
    }

    
    public function renderForm()
    {
        $this->display = 'add';
        $wallet_detail = $this->getWalletDetails();

        if (is_null($wallet_detail)) {
            return;
        }

        $wallet = $wallet_detail->wallet;
        
        $ibans = $wallet->ibans;

        $wallet_form = array();
        $wallet_form['form'] = array(
            'legend' => array(
                'title' => $this->l('Wallet informations'),
                'icon' => 'icon-google-wallet'
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
                    'name' => 'wallet',
                    'label' => $this->l('Wallet'),
                    'lang' => false,
                    'disabled' => true,
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_lw_wallet',
                    'lang' => false,
                    'disabled' => false,
                ),
                array(
                    'col' => 3,
                    'type' => 'text',
                    'name' => 'holder_name',
                    'label' => $this->l('Holder'),
                    'lang' => false,
                    'disabled' => true,
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'holder',
                    'lang' => false,
                    'disabled' => false,
                ),
                array(
                    'col' => 3,
                    'type' => 'text',
                    'name' => 'bal',
                    'label' => $this->l('Balance'),
                    'lang' => false,
                    'disabled' => true,
                    'is_number' => true,
                    'prefix' => '<i class="icon icon-eur"></i>',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'prev_bal',
                    'lang' => false,
                ),
                array(
                    'col' => 3,
                    'type' => 'text',
                    'name' => 'status',
                    'label' => $this->l('Status'),
                    'lang' => false,
                    'disabled' => true,
                ),
            )
        );

        $this->fields_form['w_form'] = $wallet_form;
        $moneyout_form = array();
        $moneyout_form['form'] =  array(
            'legend' => array(
                'title' => $this->l('Moneyout'),
                'icon' => 'icon-money'
                ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Iban'),
                    'name' => 'id_lw_iban',
                    'required' => true,
                    'options' => array(
                        'query' => $ibans,
                        'id' => 'ID',
                        'name' => 'IBAN',
                        'default' => array(
                            'label' => $this->l('Select an Iban'),
                            'value' => ""
                        )
                    )
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'iban',
                    'lang' => false,
                ),
                array(
                    'col' => 2,
                    'type' => 'text',
                    'label' => $this->l('Amount'),
                    'name' => 'amount_to_pay',
                    'required' => true,
                    'lang' => false,
                    'hint' => $this->l('Amount to transfert'),
                    'is_number' => true,
                    'prefix' => '<i class="icon icon-eur"></i>',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Pay'),
            )
        );

        $this->fields_form['m_form'] =  $moneyout_form;

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
        );

        return parent::renderForm();
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . "/views/js/back.js");
    }

    /**
    * @return Apiresponse
    */
    public function getWalletDetails()
    {
        if (is_null($this->walletDetails)) {
            try {
                $res = $this->module->getWalletDetails(LemonWayConfig::getWalletMerchantId());
            } catch (Exception $e) {
                Logger::AddLog($e->getMessage());
                $this->errors[] = Tools::displayError($e->getMessage());
                return null;
            }
            
            if (isset($res->lwError)) {
                $this->errors[] =
                    sprintf(Tools::displayError("Error: %s. Code: %s"), $res->lwError->MSG, $res->lwError->CODE);
                return null;
            }
            
            $this->walletDetails = $res;
        }

        return $this->walletDetails;
    }
}
