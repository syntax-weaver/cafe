<?php 

require base_path('app/repositories/CartRepository.php');
require base_path('app/repositories/ProductVariantsRepository.php');

class CartController {
    private object $cart_repo;
    private object $variant_repo;

    public function __construct()
    {
        $this->cart_repo = new CartRepository(db());
        $this->variant_repo = new ProductVariantsRepository(db());
    }

    public function index() {
        $cart = $this->getOrCreateCart('get or create');
        $cart_id = $cart['id'];
        $items = $this->cart_repo->getCartDetailsByCartId($cart_id);
        view('client/cart/index', $items);
    }

    public function add() {
        $variant_id = $_POST['id'];
        if(!$variant_id) {
            abort(404);
        }
        $variant = $this->variant_repo->findById($variant_id); // this return variant or null
        if(!$variant) {
            abort(404);
        }
        $product_id = $variant['product_id'];
        if($variant['available_quantity'] <= 0) {
            flash('errors', $variant['name'] . ' out of stock currently');
            redirect('/products/show?id=' . $product_id);
        }

        $cart = $this->getOrCreateCart('get');
        if(!$cart) {
            abort(500);
        }
        $cart_id = $cart['id'];
        $exist_in_cart = $this->cart_repo->itemExists($cart_id, $variant_id);


        if(!$exist_in_cart) {
            $added = $this->cart_repo->addItem($cart_id, $variant_id);
            if(!$added) {
                abort(500);
            }
        } else {
            $can_buy = $variant['available_quantity'] >= ($exist_in_cart['quantity']+1) ? true : false;
            if(!$can_buy) {
                flash('errors', 'Maximum available quantity reached');
                redirect('/products/show?id='. $product_id);
            }

            $incremented = $this->cart_repo->incrementItem($cart_id, $variant_id);
            if(!$incremented) {
                abort(500);
            }
        }
        flash('success', $variant['name'] . ' is added to cart');
        redirect('/products/show?id=' . $product_id);
    }

    public function increment() {
        $cart_item_id = (int)trim($_POST['id']);
        $cart_item = $this->cart_repo->getCartItemById($cart_item_id);
        if(!$cart_item) {
            abort(404);
        }
        $cart = $this->getOrCreateCart('get');
        if(!$cart) {
            abort(404);
        }
        $cart_id = $cart['id'];
        if($cart_id !== $cart_item['cart_id']) {
            abort(404);
        }
        $variant = $this->variant_repo->findById($cart_item['variant_id']);
        if(!$variant) {
            flash('errors', 'product is unavailable currently');
            redirect('/cart');
        }
        if($cart_item['quantity']+1 > $variant['available_quantity']) {
            flash('errors', 'maximum available quantity reached');
            redirect('/cart');
        }
        $incremented = $this->cart_repo->incrementByItemId($cart_item_id);
        if(!$incremented) {
            abort(500);
        }
        flash('success', 'product incremented');
        redirect('/cart');
        
    }

    public function decrement() {
        $cart_item_id = (int)trim($_POST['id']);
        $cart_item = $this->cart_repo->getCartItemById($cart_item_id);
        if(!$cart_item) {
            abort(404);
        }
        
        $cart = $this->getOrCreateCart('get');
        if($cart['id'] !== $cart_item['cart_id']) {
            abort(404);
        }
        if($cart_item['quantity'] <= 1) {
            // remove this row 
            $removed = $this->cart_repo->deleteItemById($cart_item_id);
            if(!$removed) {
                abort(500);
            }
            flash('success', 'removed');
            redirect('/cart');
        }
        $decremented = $this->cart_repo->decrementByItemId($cart_item_id);
        if(!$decremented) {
            abort(500);
        }
        flash('success', 'decremented');
        redirect('/cart');
        
    }

    public function remove() {
        $item_id = (int)trim($_POST['id']);
        $item = $this->cart_repo->getCartItemById($item_id);
        if(!$item) {
            abort(404);
        }
        $cart = $this->getOrCreateCart('get');
        if(!$cart) {
            abort(404);
        }
        if($cart['id'] !== $item['cart_id']) {
            abort(404);
        }
        $removed = $this->cart_repo->deleteItemById($item_id);
        if(!$removed) {
            abort(500);
        }
        flash('success', 'removed');
        redirect('/cart');
        
    }

    public function getOrCreateCart(string $task='get'): ?array {
        $user_id = user()['id'];

        if($task !== 'get') {
            $cart = $this->cart_repo->getOrCreateByUserId($user_id);
        } else {
            $cart = $this->cart_repo->getCartByUserId($user_id);
        }
        return $cart;
    }
}