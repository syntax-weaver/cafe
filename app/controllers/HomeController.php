<?php 

class HomeController {
    public function index () {
        $title = 'Home'; 
        $msg = 'Hello';
        view('home', [
            'title' => $title, 
            'message' => $msg
        ]);
    }
}