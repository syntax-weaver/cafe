<?php 

require base_path('app/repositories/ProductRepository.php');


class ClientProductsController {
    private object $product_repo; 

    public function __construct()
    {
        $this->product_repo = new ProductRepository(db());
    }

    public function index(){
        $visible_products = $this->product_repo->findAllVisibleForCustomers();
        
        foreach($visible_products as &$p) {
            $p['is_out_of_stock'] = boolval($p['is_out_of_stock']);
            $p['short_description'] = substr($p['description'], 0, 20);
        }
        unset($p);
        
        view('client/products/index', ['products' => $visible_products]);
    }

    public function show() {
        $product_id = (int)trim($_GET['id']);        
        $data = $this->product_repo->findVisibleProductForCustomerById($product_id);        
        if(!$data) {
            abort(404);
        }
        
        view('client/products/show', $data);
    }
}