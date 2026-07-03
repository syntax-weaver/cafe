<?php 


class AuthMiddleware {
    public function handle(): void {
        if (guest()) {
            redirect('/login');
        }
    }
}