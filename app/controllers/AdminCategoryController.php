<?php 


require base_path('app/repositories/CategoryRepository.php');
require base_path('app/validations/Validator.php');

class AdminCategoryController {
    private object $cat_repo;

    public function __construct() {
        $this->cat_repo = new CategoryRepository(db());
    }

    public function index (): void {
        $all_categories = $this->cat_repo->findAll();

        view('admin/category/index', ['all_categories' => $all_categories]);
    }

    public function create (): void {
        view('admin/category/create');
    }

    public function store (): void {
        // validate
        $validator = new Validator($_POST);
        $validator->required('name');
        $validator->min('name', 3);
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/admin/categories/create');
        }
        
        $name = $_POST['name'];
        $name = ucwords(strtolower(trim($name)));
        
        // check uniqueness

        $deleted = $this->cat_repo->findDeletedByName($name);
        if ($deleted) {
            back_with_errors(['name' => ['this category has been deleted, restore it from the trash.']], $_POST, '/admin/categories/create');
        }

        $exist = $this->cat_repo->findByName($name);
        if ($exist) {
            back_with_errors(['name' => ['this category already exist']], $_POST, '/admin/categories/create');
        }

        // create category
        $id = $this->cat_repo->create($name);
        if (! $id) {
            abort(500, 'internal server error');
        }

        // flash success
        flash('success', 'category created successfully');

        // redirect
        redirect('/admin/categories');
    }

    public function edit () {
        $id = (int)$_GET['id'];
        // load category
        $category = $this->cat_repo->findById($id);
        if (! $category) {
            abort(404);
        }
        // display form
        view('admin/category/edit', ['category' => $category]);
    }

    public function update () {
        
        $id = (int)$_POST['id'];
        $old_category = $this->cat_repo->findById($id);
        if (! $old_category) {
            abort(404);
        }

        // validate
        $validator = new Validator($_POST);
        $validator->required('name');
        $validator->min('name', 3);
        if ($validator->fails()) {
            flash('errors', $validator->errors());
            flash('old_input', $_POST);
            view('admin/category/edit', ['category' => $old_category]);
            exit();
        }

        $name = ucwords(trim($_POST['name']));
        
        // update category
        $deleted = $this->cat_repo->findDeletedByName($name);
        if ($deleted) {
            flash('errors', ['name' => ['a category with the same name exist in the trash, you can restore it']]);
            flash('old_input', $_POST);
            view('admin/category/edit', ['category' => $old_category]);
            exit();
        }
        $exist = $this->cat_repo->findByName($name);
        if ($exist && $exist['id'] !== $id) {
            flash('errors', ['name' => ['this category name already exist']]);
            flash('old_input', $_POST);
            view('admin/category/edit', ['category' => $old_category]);
            exit();
        }

        $updated = $this->cat_repo->update($id, $name);
        if (! $updated) {
            abort(500, 'internal server error');
        }
        
        // flash success
        flash('success', 'category updates successfully');

        // redirect
        redirect('/admin/categories');
    }

    public function destroy () {
        $id = (int)$_POST['id'];

        $category = $this->cat_repo->findById($id);
        if (! $category) {
            abort(404);
        }
        $can_delete_this_category = $this->cat_repo->canDelete($id); 
        if (! $can_delete_this_category) {
            $deleted = $this->cat_repo->softDeleteConnectedProducts($id);
            if(!$deleted) {
                abort(500, 'internal server error');
            }
        }

        $deleted = $this->cat_repo->softDelete($id);
        if (! $deleted) {
            $this->cat_repo->restoreDeletedConnectedProducts($id);
            abort(500, 'internal server error');
        }
        redirect('/admin/categories');
    }

    public function trash () {
        $deleted_categories = $this->cat_repo->findAllDeleted();
        
        view('admin/category/trash', ['deleted_categories' => $deleted_categories]);
    }

    public function restore () {
        $id = (int)$_POST['id'];
        $deleted_category = $this->cat_repo->findDeletedById($id);
        if (! $deleted_category) {
            abort(404);
        }
        $restored = $this->cat_repo->restore($id);
        if (! $restored) {
            abort(500, 'internal server error');
        }
        flash('success', 'category restored successfully');
        redirect('/admin/categories/trash');
    }
    
}