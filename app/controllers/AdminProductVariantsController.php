<?php 

require base_path('app/repositories/ProductVariantsRepository.php');
require base_path('app/repositories/ProductRepository.php');
require base_path('app/validations/Validator.php');


class AdminProductVariantsController {
    private object $variants_repo;
    private object $product_repo;
    
    public function __construct()
    {
        $this->variants_repo = new ProductVariantsRepository(db());
        $this->product_repo = new ProductRepository(db());
    }
    public function index(): void {
        $product_id = (int)trim($_GET['id']);
        $variants = $this->variants_repo->findAllByProductId($product_id);
        
        view('admin/productvariants/index', ['variants' => $variants, 'product_id' => $product_id]);
    }
    
    public function create(): void {
        $product_id = (int)trim($_GET['id']);
        $product = $this->product_repo->findById($product_id);
        if(! $product) {
            abort(404);
        }
        view('admin/productvariants/create', ['product_id' => $product_id]);
        
    }

    public function store(): void {
        
        $name = trim($_POST['name']);
        $price = trim($_POST['price']);
        $available_quantity = trim($_POST['available_quantity']);
        $product_id = trim($_POST['product_id']);
        $validator = new Validator($_POST);
        $validator->required('name');
        $validator->required('price');
        $validator->required('available_quantity');
        $validator->required('product_id');
        $validator->min('name', 3);
        $validator->positiveNumber('available_quantity');
        $validator->positiveNumber('price');
        if($validator->fails()) {
            flash('errors', $validator->errors());
            flash('old_input', $_POST);
            view('admin/productvariants/create', ['product_id' => $product_id]);
            exit();
        }

        $price = floatval($price);
        $product_id = (int)$product_id;
        $available_quantity = (int)$available_quantity;

        // check if the (product_id + name) combination is unique
        $variant = $this->variants_repo->findByNameAndProductId($name, $product_id);
        if($variant && $variant['deleted_at'] === null) {
            flash('errors', ['name' => ['this product has variant with the same name']]);
            flash('old_input', $_POST);
            view('admin/productvariants/create', ['product_id' => $product_id]);
            exit();
        }
        if($variant && $variant['deleted_at'] !== null) {
            flash('errors', ['name' => ['a variant with the same name already exist in the trash, you can restore it']]);
            flash('old_input', $_POST);
            view('admin/productvariants/create', ['product_id' => $product_id]);
            exit();
        }

        $created = $this->variants_repo->create($product_id, $name, $price, $available_quantity);
        if(! $created) {
            abort(500, 'internal server error');
        }
        
        flash('success', 'product variant created successfully');
        redirect('/admin/productvariants?id='. $product_id);
        
    }

    public function edit(): void {
        $product_id = $_GET['product_id'];
        $variant_id = $_GET['variant_id'];

        $product = $this->product_repo->findById($product_id);
        if(!$product) {
            abort(404);
        }

        $variant = $this->variants_repo->findById($variant_id);
        if(!$variant) {
            abort(404);
        }


        view('admin/productvariants/edit', [
            'variant' => $variant, 
        ]);
    }

    public function update(): void {
        $product_id = (int)$_POST['product_id'];
        $variant_id = (int)$_POST['variant_id'];

        $product = $this->product_repo->findById($product_id);
        if(!$product) {
            abort(404);
        }

        $variant = $this->variants_repo->findById($variant_id);
        if(!$variant) {
            abort(404);
        }
        $name = trim($_POST['name']);
        $price = trim($_POST['price']);
        $available_quantity = trim($_POST['available_quantity']);
        $product_id = trim($_POST['product_id']);
        $validator = new Validator($_POST);
        $validator->required('name');
        $validator->required('price');
        $validator->required('available_quantity');
        $validator->required('product_id');
        $validator->min('name', 3);
        $validator->positiveNumber('available_quantity');
        if($validator->fails()) {
            flash('errors', $validator->errors());
            flash('old_input', $_POST);
            view('admin/productvariants/edit', ['variant' => $variant]);
            exit();
        }

        $price = floatval($price);
        $product_id = (int)$product_id;
        $available_quantity = (int)$available_quantity;

        // check if the (product_id + name) combination is unique
        $variant = $this->variants_repo->findByNameAndProductId($name, $product_id);
        if($variant && $variant['deleted_at'] !== null) {
            flash('errors', ['name' => ['a variant with the same name already exist in the trash, you can restore it']]);
            flash('old_input', $_POST);
            view('admin/productvariants/edit', ['variant' => $variant]);
            exit();
        }
        if($variant && $variant['id'] !== $variant_id) {
            flash('errors', ['name' => ['this product has variant with the same name']]);
            flash('old_input', $_POST);
            view('admin/productvariants/edit', ['variant' => $variant]);
            exit();
        }


        $updated = $this->variants_repo->update($variant_id, $name, $price, $available_quantity);
        if(! $updated) {
            abort(500, 'internal server error');
        }
        
        flash('success', 'variant updated successfully');
        redirect('/admin/productvariants?id='. $product_id);
    }

    public function destroy(): void {
        $variant_id = (int)trim($_POST['id']);
        $variant = $this->variants_repo->findById($variant_id);
        if(!$variant) {
            abort(404);
        }
        $deleted = $this->variants_repo->softDelete($variant_id);
        if(!$deleted) {
            abort(500, 'internal server error');
        }
        $product_id = $variant['product_id'];
        flash('success', 'variant deleted successfully');
        redirect('/admin/productvariants?id=' . $product_id);
        
    }

    public function trash(): void {
        
        $product_id = (int)trim($_GET['id']);
        $deleted_variants = $this->variants_repo->findAllDeletedByProductId($product_id);
        
        view('admin/productvariants/trash', ['deleted_variants' => $deleted_variants]);
    }

    public function restore(): void {
        $variant_id = (int)trim($_POST['variant_id']);
        $product_id = (int)trim($_POST['product_id']);
        $deleted_variant =$this->variants_repo->findDeletedById($variant_id);
        if (!$deleted_variant) {
            abort(404);
        }
        
        $restored = $this->variants_repo->restore($variant_id);
        
        if(!$restored) {
            abort(500, 'internal server error');
        }
        flash('success', 'variant restored successfully');
        
        redirect('/admin/productvariants/trash?id='. $product_id);
        
    }
}