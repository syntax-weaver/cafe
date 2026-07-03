<?php 

require base_path('app/validations/validator.php');
require base_path('app/repositories/UserRepository.php');

class RegisterController {
    public function index (): void {
        view('auth/register');
    }
    public function store (): void {
        // validate
        $validator = new Validator($_POST);
        $validator->required('name');
        $validator->required('email');
        $validator->required('password');
        $validator->required('confirm_password');
        $validator->email('email');
        $validator->min('name', 3);
        $validator->min('password', 8);
        $validator->password('password');
        $validator->confirmPassword('confirm_password');
        if ($validator->fails()) {
            back_with_errors($validator->errors(), $_POST, '/register');
        }

        // check email uniqueness
        $user_repository = new UserRepository(db());
        $user = $user_repository->findByEmail(trim($_POST['email'])); // should return null
        if($user) {
            back_with_errors(['email' => ['email already exist']], $_POST, '/register');
        }

        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'client';
        
        // save user to the database
        $new_user_id = $user_repository->create($name, $email, $hashed_password, $role);
        if(! $new_user_id) {
            abort(500, 'Internal Server Error');
        }

        // login 
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $new_user_id, 
            'name' => $name, 
            'email' => $email, 
            'role' => $role
        ];

        
        redirect('/');
        
        

    }
}