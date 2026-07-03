<?php 


class GuestMiddleware {
    public function handle (): void {
        if (auth()) {
            redirect('/');
        }
    }
}