<?php


require base_path('app/validations/Validator.php');
require base_path('app/repositories/ProductRepository.php');
require base_path('app/repositories/CategoryRepository.php');

class AdminProductController {
    private object $product_repo;
    private object $category_repo;

    public function __construct()
    {
        $this->product_repo = new ProductRepository(db());
        $this->category_repo = new CategoryRepository(db());
    }

    public function index(): void {
        $all_products = $this->product_repo->findAll();
        
        view('admin/product/index', ['all_products' => $all_products]);
    }

    public function create(): void {
        $category_repo = new CategoryRepository(db());
        $all_categories = $category_repo->findAll();
        view('admin/product/create', ['all_categories' => $all_categories]);
    }

    public function store(): void {
        $validator = new Validator($_POST);
        $validator->required('name');
        $validator->required('description');
        $validator->required('category');
        $validator->min('name', 3);
        $validator->min('description', 8);
        if($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/admin/products/create');
        }

        // check name uniqueness
        $name = ucwords(trim($_POST['name']));
        $exist = $this->product_repo->findByName($name);

        if($exist) {
            back_with_errors(['name' => ['A product with the same name already exist']], $_POST, '/admin/products/create');
        }
        $deleted = $this->product_repo->findDeletedByName($name);
        if($deleted) {
            back_with_errors(['name' => ['A product with the same name exist in the trash, restore it.']], $_POST, '/admin/products/create');
        }

        // store the product
        $description = trim($_POST['description']);
        $category_id = (int)trim($_POST['category']);
        $category = $this->category_repo->findById($category_id);
        if(! $category) {
            abort(404);
        }
        $id = $this->product_repo->create($category_id, $name, $description);
        if(! $id) {
            abort(500, 'internal server error');
        }

        // flash success
        flash('success', 'product created successfully');

        // redirect
        redirect('/admin/products');

    }

    public function edit(): void {
        $id = (int)$_GET['id'];
        $product = $this->product_repo->findById($id);
        if (! $product) {
            abort(404);
        }
        $all_categories = $this->category_repo->findAll();

        view('admin/product/edit', [
            'product' => $product, 
            'all_categories' => $all_categories
        ]);
    }

    public function update(): void {
        $id = (int)$_POST['id'];
        $old_product = $this->product_repo->findById($id);
        if (! $old_product) {
            abort(404);
        }
        $all_categories = $this->category_repo->findAll();

        $validator = new Validator($_POST);
        $validator->required('name');
        $validator->required('description');
        $validator->required('category');
        $validator->min('name', 3);
        $validator->min('description', 8);
        if($validator->fails()) {
            flash('old_input', $_POST);
            flash('errors', $validator->errors());
            view('admin/product/edit', [
                'product' => $old_product, 
                'all_categories' => $all_categories
            ]);
            exit();
        }

        // check name uniqueness
        $name = ucwords(trim($_POST['name']));
        $exist = $this->product_repo->findByName($name);

        if($exist && $exist['id'] !== $id) {
            flash('errors', ['name' => ['A product with the same name already exist']]);
            flash('old_input', $_POST);
            view('admin/product/edit', [
                'product' => $old_product, 
                'all_categories' => $all_categories
            ]);
            exit();
        }
        $deleted = $this->product_repo->findDeletedByName($name);
        if($deleted) {
            flash('errors', ['name' => ['A product with the same name exist in the trash, restore it.']]);
            flash('old_input', $_POST);
            view('admin/product/edit', [
                'product' => $old_product, 
                'all_categories' => $all_categories
            ]);
            exit();
        }

        // update the product
        $description = trim($_POST['description']);
        $category_id = (int)trim($_POST['category']);
        $category = $this->category_repo->findById($category_id);
        if(! $category) {
            abort(404);
        }
        $updated = $this->product_repo->update($id, $category_id, $name, $description);
        if(! $updated) {
            abort(500, 'internal server error');
        }

        // flash success
        flash('success', 'product updated successfully');

        // redirect
        redirect('/admin/products');

    }

    public function destroy(): void {
        $id = (int)trim($_POST['id']);
        
        $product = $this->product_repo->findById($id);
        if(! $product) {
            abort(404);
        }
        $deleted = $this->product_repo->softDelete($id);
        if(! $deleted) {
            abort(500, 'internal server error');
        }
        flash('success', 'product deleted successfully');
        redirect('/admin/products');

    }

    public function trash(): void {
        $deleted_products = $this->product_repo->findAllDeleted();
        view('/admin/product/trash', ['deleted_products' => $deleted_products]);
    }

    public function restore(): void {
        $id = (int)trim($_POST['id']);

        $product = $this->product_repo->findDeletedById($id);
        
        if(! $product) {
            abort(404);
        }

        // check if the category is deleted
        // if the category is deleted you can't restore the product until you restore the category
        if($product['category_deleted_at'] !== null) {
            flash('error', 'the category this product attached to is deleted, restore the parent category first then, you can restore the product');
            redirect('/admin/products/trash');
        }

        $restored = $this->product_repo->restore($id);
        if(! $restored) {
            abort(500, 'internal server error');
        }
        flash('success', 'product restored successfully');
        redirect('/admin/products/trash');
    }


}