<?php 

class AdminMiddleware {
    public function handle(): void {
        if (! auth() || user()['role'] !== 'admin') {
            redirect('/');
        }
    }
}