<?php 

class LogoutController {
    public function index (): void {
        view('auth/logout');
    }

    public function store (): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            // delete the cookie from the browser
            setcookie(session_name(), '', time()-42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        session_start();
        session_regenerate_id(true);
        redirect('/login');
    }

}