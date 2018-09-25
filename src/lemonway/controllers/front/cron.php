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

class LemonwayCronModuleFrontController extends ModuleFrontController
{
    const IS_RUNNING = 'LEMONWAY_SPLITPAYMENT_IS_RUNNING';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     */
    public function postProcess()
    {
        if ($this->isRunning()) {
            return '';
        }

        set_time_limit(0);

        $this->setRunningState(true);
        $date = new \DateTime();

        //get all splitpayment to be paid
        /* @var $splitpaymentCollection PrestashopCollectionCore */
        $splitpaymentCollection = new PrestaShopCollection('SplitpaymentDeadline');
        $splitpaymentCollection
            ->where('status', '!=', SplitpaymentDeadline::STATUS_COMPLETE)
            ->where('attempts', '<', SplitpaymentDeadline::MAX_ATTEMPTS)
            ->where('date_to_pay', '<=', $date->format('Y-m-d 00:00:00'));

        /* @var $splitpayment SplitpaymentDeadline */
        foreach ($splitpaymentCollection as $splitpayment) {
            try {
                $splitpayment->pay(true);
            } catch (Exception $e) {
                echo $e;
                continue;
            }
        }

        $this->setRunningState(false);
        die('Pay Splitpayment was ran.');
    }

    protected function isRunning()
    {
        return (bool) Configuration::get(self::IS_RUNNING);
    }

    protected function setRunningState($isRunning)
    {
        Configuration::updateValue(self::IS_RUNNING, $isRunning);
    }
}
