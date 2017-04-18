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

require_once _PS_MODULE_DIR_ . 'lemonway/classes/SplitpaymentDeadline.php';
require_once _PS_MODULE_DIR_ . 'lemonway/services/LemonWayKit.php';
class AdminSplitpaymentDeadlineController extends ModuleAdminController
{
	/** @var SplitpaymentDeadline Instantiation of the class associated with the AdminSplitpaymentDeadlineController */
	protected $object;
	
    
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'lemonway_splitpayment_deadline';
        $this->identifier = 'id_splitpayment';
        $this->identifier_name = 'id_splitpayment';
        $this->className = 'SplitpaymentDeadline';
        $this->lang = false;
        $this->list_no_link = false;
        $this->allow_export = false;
        $this->deleted = false;
        $this->context = Context::getContext();
        $this->multiple_fieldsets = true;
        $this->addRowAction('edit');
        $this->_orderBy = 'id_splitpayment';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;

        $this->fields_list = array(
            'id_splitpayment' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'order_reference' => array(
                'title' => $this->l('Order Reference')
            ),
        	'total_amount' => array(
        		'title' => $this->l('Order total'),
        		'align' => 'text-right',
        		'type' => 'price',
        		'currency' => true,
        		'callback' => 'setOrderCurrency',
        		'badge_success' => true
        	),
        	'amount_to_pay' => array(
        		'title' => $this->l('Amount to pay'),
        		'align' => 'text-right',
        		'type' => 'price',
        		'currency' => true,
        		'callback' => 'setOrderCurrency',
        		'badge_success' => true
        	),
        	'date_to_pay' => array(
        		'title' => $this->l('Date to pay'),
        		'align' => 'text-right',
                'type' => 'date',
               // 'filter_key' => 'a!date_to_pay'
        	),
        	'method_code' => array(
        		'title' => $this->l('Payment Method'),
        		'callback' => 'setMethodTitle',
        		'search' => false,
        	),
        	'attempts' => array(
        		'title' => $this->l('Attempts'),
        		"type" => "number",
        		'align' => 'text-right',
        	),
        	'status' => array(
        		'title' => $this->l('Status'),
        		'type' => 'select',
        		'color' => 'color',
        		'list' => SplitpaymentDeadline::getStatuesKeyValue(),
        		'filter_key' => 'status',
        		'filter_type' => 'string',
        		//'order_key' => 'status'
        	),
        	'paid_at' => array(
        		'title' => $this->l('Paid at'),
        		'align' => 'text-right',
        		'type' => 'date',
        		// 'filter_key' => 'a!date_to_pay'
        	),
        		
        	
        );
        
        $this->module =  Module::getInstanceByName('lemonway');

        parent::__construct();
    }
    
    public static function setOrderCurrency($amount, $tr)
    {
    	$order = new Order($tr['id_order']);
    	return Tools::displayPrice($amount, (int)$order->id_currency);
    }
    
    public static function setMethodTitle($methodCode, $tr)
    {
    	$methodInstance = Lemonway::methodInstanceFactory($methodCode);
    	return $methodInstance->getTitle() ;
    }

    public function initToolbar()
    {
        parent::initToolbar();
        if (isset($this->toolbar_btn['new'])) {
        	unset($this->toolbar_btn['new']);
        }
       
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
		if($this->display === 'edit' && $this->object->canPaid()){	
		
	        $this->page_header_toolbar_btn['pay_now'] = array(
	        		//'href' => self::$currentIndex.'&addlemonway_moneyout&token=' . $this->token,
	        		'href' => self::$currentIndex.'&action=pay_now&'.$this->identifier.'='.$this->object->id.'&token=' . $this->token,
	        		'desc' => $this->l('Pay now', null, null, false),
	        		'icon' => 'process-icon-payment'
	        );
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
        
       /*  if ($this->display == 'edit') {
            $this->toolbar_title = array();
            $this->toolbar_title[] = $this->l('Edit split payment deadline', null, null, false);
            $this->addMetaTitle($this->l('Edit split payment deadline', null, null, false));
        } */
    }
    

    public function processPayNow(){
    	
    	if (!$this->loadObject(true)) {
    		return;
    	}
    	


    	try {
    			

    		$this->object->pay(true); 
    			
    			
    	} catch (Exception $e) {

    		$this->errors[] = Tools::displayError('An error occurred while executing payment.').
    			' ('.$e->getMessage().')';	
    	}
    	
    	

    	if(count($this->errors)){
    		$this->display = 'edit';
    		return false;
    	}
    	else{	
	    	//Redirect to list
    		$this->redirect_after = self::$currentIndex.'&token='.$this->token;
    	}
    }

    
    public function renderForm()
    {
        $this->display = 'edit';

         $deadlineForm = array();
         $deadlineForm['form'] =  array(
         		'legend' => array(
         				'title' => $this->l('Splitpayment deadline'),
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
         						'name' => 'amount_to_pay',
         						'label' => $this->l('Amount to pay'),
         						'lang' => false,
         						'disabled' => false,
         						'required'=>true
         				),
         				array(
         						'col' => 6,
         						'type' => 'date',
         						'name' => 'date_to_pay',
         						'label' => $this->l('Date to pay'),
         						'lang' => false,
         						'disabled' => false,
         						'required'=>true
         				),
         				array(
         						'col' => 3,
         						'type' => 'text',
         						'name' => 'attempts',
         						'label' => $this->l('Attempts'),
         						'lang' => false,
         						'disabled' => false,
         						'required'=>true
         				),
         				array(
         						'col' => 3,
         						'type' => 'select',
         						'options' => array(
         								'query'=>SplitpaymentDeadline::getStatues(),
         								'id'=>'value',
         								'name'=>'name'
         						),
         						'identifier' => 'value',
         						'name' => 'status',
         						'label' => $this->l('Status'),
         						'lang' => false,
         						'disabled' => false,
         						'required'=>true,
         				),
         		),
         		'submit' => array(
         				'title' => $this->l('Save'),
         		)
         );

         
         $this->fields_form['form'] = $deadlineForm;
         
         $this->fields_value = array(
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
    
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
    	if(Context::getContext()->getTranslator()){
    
    		$parameters = array();
    		if(is_array($class)) $parameters = $class;
    
    		return Context::getContext()->getTranslator()->trans($string, $parameters, 'Admin.Lemonway');
    	}
    	else{
    		return parent::l($string,$class,$addslashes,$htmlentities);
    	}
    }

}
