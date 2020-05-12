<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Checkout;

use Concrete\Core\User\User;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Page\Controller\PageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode;

class Complete extends PageController
{
    public function on_start()
    {
        $u = new User();
        $u->refreshUserGroups();
    }

    public function view()
    {
        $customer = new Customer();
        $lastorderid = $customer->getLastOrderID();

        if ($lastorderid) {
            $order = Order::getByID($customer->getLastOrderID());
        }

        if (is_object($order)) {
            $this->set("order", $order);
        } else {
            return Redirect::to("/cart");
        }

        Cart::clear();
        DiscountCode::clearCartCode();

        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'community-store');
        $this->requireAsset('css', 'community-store');

        // unset the shipping type, as next order might be unshippable
        Session::set('community_store.smID', '');

        $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices', new User());
        $this->set("orderChoicesEnabled", count($orderChoicesAttList) ? true : false);
        if (is_array($orderChoicesAttList) && !empty($orderChoicesAttList)) {
            $this->set("orderChoicesAttList", $orderChoicesAttList);
        }
    }
}
