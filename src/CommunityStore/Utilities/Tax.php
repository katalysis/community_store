<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Controller\Controller;
use Concrete\Core\Support\Facade\Session;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer;

class Tax extends Controller
{
    public function validateVatNumber($vat_number)
    {
        $e = $this->app->make('helper/validation/error');

        // If not VAT number set, return empty errors
        if (empty($vat_number)) {
            return $e;
        }

        // Taken from: https://www.safaribooksonline.com/library/view/regular-expressions-cookbook/9781449327453/ch04s21.html
        $regex = "/^((AT)?U[0-9]{8}|(BE)?0[0-9]{9}|(BG)?[0-9]{9,10}|(CY)?[0-9]{8}L|(CZ)?[0-9]{8,10}|(DE)?[0-9]{9}|(DK)?[0-9]{8}|(EE)?[0-9]{9}|(EL|GR)?[0-9]{9}|(ES)?[0-9A-Z][0-9]{7}[0-9A-Z]|(FI)?[0-9]{8}|(FR)?[0-9A-Z]{2}[0-9]{9}|(GB)?([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})|(HU)?[0-9]{8}|(IE)?[0-9]S[0-9]{5}L|(IE)?[0-9]{7}[A-Z]*|(IT)?[0-9]{11}|(LT)?([0-9]{9}|[0-9]{12})|(LU)?[0-9]{8}|(LV)?[0-9]{11}|(MT)?[0-9]{8}|(NL)?[0-9]{9}B[0-9]{2}|(PL)?[0-9]{10}|(PT)?[0-9]{9}|(RO)?[0-9]{2,10}|(SE)?[0-9]{12}|(SI)?[0-9]{8}|(SK)?[0-9]{10})$/i";

        if ('' != $vat_number && !preg_match($regex, $vat_number)) {
            $e->add(t('You must enter a valid VAT Number'));
        }

        return $e;
    }

    public function setVatNumber()
    {
        $token = $this->app->make('token');

        if ($this->request->request->all() && $token->validate('community_store')) {
            $data = $this->request->request->all();
            // VAT Number validation
            if (Config::get('community_store.vat_number')) {
                $vat_number = str_replace(' ', '', trim($data['vat_number']));
                $e = $this->validateVatNumber($vat_number);
                if ($e->has()) {
                    echo $e->outputJSON();
                } else {
                    $this->updateVatNumber($data);
                    echo json_encode([
                        'vat_number' => $vat_number,
                        'error' => false,
                    ]);
                }
            }
        } else {
            echo "An error occured";
        }

        exit();
    }

    private function updateVatNumber($data)
    {
        //update the users vat number
        $customer = new Customer();
        $customer->setValue("vat_number", trim($data['vat_number']));
        Session::set('vat_number', trim($data['vat_number']));
    }
}
