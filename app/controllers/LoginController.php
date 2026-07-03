<?php

require base_path('app/validations/Validator.php');
require base_path('app/repositories/UserRepository.php');

class LoginController {
    public function index() {
        view('auth/login');
    }
    public function store() {
        $default_error_array = [
                'email' => ['wrong email or password'], 
                'password' => ['wrong email or password']
            ];

        // validate the input
        $validator = new Validator($_POST);
        $validator->required('email');
        $validator->email('email');
        $validator->required('password');
        if ($validator->fails()) {
            flash('errors', $validator->errors());
            flash('old_input', $_POST);
            redirect('/login');
        }

        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // ask repository for the user
        $userRepository = new UserRepository(db());
        $user = $userRepository->findByEmail($email);
        if(! $user) {
            // create some response time
            $fake_hash = password_hash('abc123_de', PASSWORD_DEFAULT);
            password_verify($password, $fake_hash);

            back_with_errors($default_error_array, $_POST, '/login');
        }
        $hashed_password = $user['password'];
        
        // check soft delete
        if ($user['deleted_at'] !== null) {
            back_with_errors($default_error_array, $_POST, '/login');
        }

        // verify the password
        if (! password_verify($password, $hashed_password)) {
            back_with_errors($default_error_array, $_POST, '/login');
        }
        
        // regenerate session id
        session_regenerate_id(true);

        // store user data in session 
        $_SESSION['user'] = [
            'id' => $user['id'], 
            'name' => $user['name'], 
            'email' => $user['email'], 
            'role' => $user['role']
        ];

        // redirect by role
        // the role should be handled by and the middlewares and the router handle the middleware
        redirect('/products');

    }
}
