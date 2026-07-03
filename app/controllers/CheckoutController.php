<?php 

require base_path('app/repositories/CartRepository.php');
require base_path('app/repositories/CheckoutRepository.php');
require base_path('app/repositories/ProductVariantsRepository.php');


class CheckoutController {
    private object $cart_repo; 
    private object $checkout_repo;
    private object $variant_repo;

    public function __construct() {
        $this->cart_repo = new CartRepository(db());
        $this->checkout_repo = new CheckoutRepository(db());
        $this->variant_repo = new ProductVariantsRepository(db());
    }

    public function index () {
        $user_id = user()['id'];
        $cart = $this->cart_repo->getCartByUserId($user_id);
        if(!$cart) {
            redirect('/cart');
        }
        $details = $this->cart_repo->getCartDetailsByCartId($cart['id']);
        if(empty($details['items'])) {
            redirect('/cart');
        }
        $items = $details['items'];
        foreach($items as $i) {
            if($i['is_available'] === 0) {
                flash('error', 'Some cart items are unavailable.Remove them before checkout.');
                redirect('/cart');
            }
        }

        view('client/checkout/index', $details);
    }

    public function placeOrder() {
        $user_id = user()['id'];
        $notes = trim($_POST['notes']);
        $status = 'pending';
        $payment_method = trim($_POST['payment_method']);

        $done = $this->checkout_repo->placeOrder($user_id, $payment_method, $notes, $status);
        if(!$done['status']) {
            flash('error', $done['message']);
            redirect('/cart');
        }

        flash('success', $done['message']);
        redirect('/cart');
        
    }
}