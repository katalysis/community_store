<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Methods\Invoice;

use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;

class InvoicePaymentMethod extends PaymentMethod
{
    public function getName()
    {
        return t('Invoice');
    }

    public function dashboardForm()
    {
        $this->set('form', Application::getFacadeApplication()->make("helper/form"));
        $this->set('invoiceMinimum', Config::get('community_store.invoiceMinimum'));
        $this->set('invoiceMaximum', Config::get('community_store.invoiceMaximum'));
        $this->set('paymentInstructions', Config::get('community_store.paymentInstructions'));
        $this->set('markPaid', Config::get('community_store.markPaid'));
    }

    public function save(array $data = [])
    {
        Config::save('community_store.invoiceMinimum', $data['invoiceMinimum']);
        Config::save('community_store.invoiceMaximum', $data['invoiceMaximum']);
        Config::save('community_store.paymentInstructions', $data['paymentInstructions']);
        Config::save('community_store.markPaid', $data['markPaid']);
    }

    public function validate($args, $e)
    {
        //$e->add("error message");
        return $e;
    }

    public function checkoutForm()
    {
        $pmID = PaymentMethod::getByHandle('invoice')->getID();

        $this->addFooterItem("
            <script type=\"text/javascript\">
                 $(function() {
                     $('div[data-payment-method-id=" . $pmID . "] .store-btn-complete-order').click(function(){
                         $(this).attr({disabled: true}).val('" . t('Processing...') . "');
                         $(this).closest('form').submit();
                     });
                 });
            </script>
        ");
    }

    public function submitPayment()
    {
        //nothing to do except return success
        return ['error' => 0, 'transactionReference' => ''];
    }

    public function getPaymentMinimum()
    {
        $defaultMin = 0;

        $minconfig = trim(Config::get('community_store.invoiceMinimum'));

        if ('' == $minconfig) {
            return $defaultMin;
        } else {
            return max($minconfig, $defaultMin);
        }
    }

    public function getPaymentMaximum()
    {
        $defaultMax = 1000000000;

        $maxconfig = trim(Config::get('community_store.invoiceMaximum'));
        if ('' == $maxconfig) {
            return $defaultMax;
        } else {
            return min($maxconfig, $defaultMax);
        }
    }

    public function markPaid()
    {
        return (bool) Config::get('community_store.markPaid');
    }

    // to be overridden by individual payment methods
    public function getPaymentInstructions()
    {
        return Config::get('community_store.paymentInstructions');
    }
}
