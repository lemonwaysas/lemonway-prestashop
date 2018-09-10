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

require_once _PS_MODULE_DIR_ . 'lemonway/services/LemonWayConfig.php';
require_once _PS_MODULE_DIR_ . 'lemonway/services/LemonWayKit.php';

class SplitpaymentDeadline extends ObjectModel
{
    const MAX_ATTEMPTS = 3;

    const STATUS_PENDING = 'pending';
    const STATUS_FAILED = 'failed';
    const STATUS_COMPLETE = 'complete';

    public $id_order;
    public $order_reference;
    public $id_customer;
    public $id_splitpayment_profile;
    public $token;
    public $total_amount;
    public $amount_to_pay;
    public $date_to_pay;
    public $method_code;
    public $attempts;
    public $status;
    public $paid_at;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        $this->module = Module::getInstanceByName('lemonway');
    }

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'lemonway_splitpayment_deadline',
        'primary' => 'id_splitpayment',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => array(
            'id_order' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true
            ),
            'order_reference' => array(
                'type' => self::TYPE_STRING,
                'required' => true
            ),
            'id_customer' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true
            ),
            'id_splitpayment_profile' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true
            ),
            'token' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
            ),
            'total_amount' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => true
            ),
            'amount_to_pay' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => true
            ),
            'date_to_pay' => array(
                'type' => self::TYPE_DATE,
                'required' => true,
                'validate' => 'isDate'
            ),
            'method_code' => array(
                'type' => self::TYPE_STRING,
                'required' => true
            ),
            'attempts' => array(
                'type' => self::TYPE_INT,
                'required' => false,
                'validate' => 'isInt'
            ),
            'status' => array(
                'type' => self::TYPE_STRING,
                'required' => true
            ),
            'paid_at' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public static function allIsPaid($order)
    {
        if (is_int($order)) {
            $order = new Order($order);
        }

        $deadlines = new PrestaShopCollection('SplitpaymentDeadline');
        $deadlines->where('id_order', '=', $order->id);

        foreach ($deadlines as $deadline) {
            if ($deadline->status != self::STATUS_COMPLETE) {
                return false;
            }
        }

        return true;
    }

    public function pay($update = false)
    {
        if (!$this->canPaid()) {
            $message = Tools::displayError('Can\'t pay this splitpayment.');
            throw new Exception($message);
        }

        /* @var $methodInstance Method */
        $methodInstance = $this->module->methodFactory($this->method_code);
        $order = new Order($this->id_order);
        $customer = new Customer($this->id_customer);

        if (Validate::isLoadedObject($order) && Validate::isLoadedObject($customer)) {
            $kit = new LemonWayKit();

            $comment = Configuration::get('PS_SHOP_NAME') . " - " . $order->reference . " - " .
                $customer->lastname . " " . $customer->firstname . " - " . $customer->email;
          
            $autocommission = LemonWayConfig::is4EcommerceMode() ? 0 : 1;
          
            //Call directkit for MoneyInWithCardId to execute this split payment
            $params = array(
                'wkToken' => $order->id,
                'wallet' => LemonWayConfig::getWalletMerchantId(),
                'amountTot' => number_format($this->amount_to_pay, 2, '.', ''),
                'amountCom' => number_format(0, 2, '.', ''),
                'comment' => $comment . " (Splitpayment #" . $this->id . ")",
                'autoCommission' => $autocommission,
                'cardId' => $this->token
            );

            try {
                $this->attempts++;
                $res = $kit->moneyInWithCardId($params);

                if (isset($res->E)) {
                    $this->status = SplitpaymentDeadline::STATUS_FAILED;
                    $message = Tools::displayError("An error occurred while trying to pay split payment. 
                        Error code: " . $res->E->Code . " - Message: " . $res->E->Msg);

                    throw new Exception($message, (int)$res->E->Code);
                } else {
                    if ($res->TRANS->HPAY->STATUS == "3") {
                        $this->status = SplitpaymentDeadline::STATUS_COMPLETE;

                        /* @var $invoiceCollection PrestaShopCollectionCore */
                        $invoiceCollection = $order->getInvoicesCollection();
                        $lastInvoice =
                            $invoiceCollection->orderBy('date_add')->setPageNumber(1)->setPageSize(1)->getFirst();

                        try {
                            $order->addOrderPayment(
                                $this->amount_to_pay,
                                $methodInstance->getTitle(),
                                $res->TRANS->HPAY->ID,
                                null,
                                null,
                                $lastInvoice
                            );
                        } catch (Exception $e) {
                            PrestaShopLogger::addLog($e->getMessage(), 4, null, null, null, true);
                        }

                        //@TODO change order state if is the last split payment
                        //$id_order_state = Configuration::get('PS_OS_PAYMENT');
                        // change order state if is the last split payment
                        /* if(SplitpaymentDeadline::allIsPaid($order)){
                            $id_order_state = Configuration::get('PS_OS_PAYMENT');
                        } */
                    } else {
                        $this->status = SplitpaymentDeadline::STATUS_FAILED;
                        $message = Tools::displayError($res->TRANS->HPAY->MSG);
                        throw new Exception($message);
                    }
                }
            } catch (Exception $e) {
                $this->status = SplitpaymentDeadline::STATUS_FAILED;

                if ($update) {
                    $this->update();
                }

                throw $e;
            }

            //Update attempts, state ,etc ..
            if ($update) {
                $this->update();
            }
        }
    }

    /**
     * Updates the current object in the database
     *
     * @param bool $null_values
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function update($null_values = false)
    {
        if ($this->status == self::STATUS_COMPLETE &&
            ($this->paid_at == null || $this->paid_at == '0000-00-00 00:00:00')) {
            $this->paid_at = date('Y-m-d H:i:s');
        } else {
            $this->paid_at = null;
            $null_values = true;
        }

        return parent::update($null_values);
    }

    public function getMaxAttempts()
    {
        return self::MAX_ATTEMPTS;
    }

    public function canPaid()
    {
        return $this->attempts <= $this->getMaxAttempts() && $this->status != self::STATUS_COMPLETE;
    }


    /**
     * Getter for statues
     *
     * @param bool $withLabels
     * @return array
     */
    public static function getStatues($withLabels = true)
    {
        $statues = array(
            self::STATUS_COMPLETE,
            self::STATUS_FAILED,
            self::STATUS_PENDING,
        );

        if ($withLabels) {
            $result = array();

            foreach ($statues as $status) {
                $result[] = array('value' => $status, 'name' => self::getStatusLabel($status));
            }

            return $result;
        }

        return $statues;
    }

    public static function getStatuesKeyValue()
    {
        return array(
            self::STATUS_COMPLETE => self::getStatusLabel(self::STATUS_COMPLETE),
            self::STATUS_FAILED => self::getStatusLabel(self::STATUS_FAILED),
            self::STATUS_PENDING => self::getStatusLabel(self::STATUS_PENDING),
        );
    }

    /**
     * Render label for specified status
     *
     * @param string $status
     * @return string
     */
    public static function getStatusLabel($status)
    {
        switch ($status) {
            case self::STATUS_COMPLETE:
                return self::l('Complete');

            case self::STATUS_FAILED:
                return self::l('Failed');

            case self::STATUS_PENDING:
                return self::l('Pending');
        }

        return $status;
    }

    public static function l($string)
    {
        return Translate::getModuleTranslation('lemonway', $string, 'splitpaymentdeadline');
    }
}
