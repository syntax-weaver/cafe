<?php 


class Router {
    private array $routes = [];
    private ?string $uri = null;
    private ?string $method = null;
    private array $middlewares = [];
    private ?string $controller_path = null;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
        $this->uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    private function abort404() {
        http_response_code(404);
        die('404 Not Found');
    }

    public function resolve() {
        if (! array_key_exists($this->uri, $this->routes)) { $this->abort404(); }
        if (! array_key_exists($this->method, $this->routes[$this->uri])) { $this->abort404(); }

        $route = $this->routes[$this->uri][$this->method];

        $this->middlewares = $route['middlewares'] ?? [];
        
        foreach($this->middlewares as $middleware) {
            $middleware_name = ucfirst($middleware) . 'Middleware';
            $middleware_file_path = base_path('app/middlewares/' . $middleware_name . '.php');
            if (! file_exists($middleware_file_path)) {
                abort(500, 'Internal Server Error');
            }
            require_once $middleware_file_path;
            if (! class_exists($middleware_name)) {
                abort(500, 'Internal Server Error');
            }
            $middleware_object = new $middleware_name();
            if (! method_exists($middleware_object, 'handle')) {
                abort(500, 'Internal Server Error');
            }
            $middleware_object->handle();
        }
        
        $controller = $route['controller'];
        $method = $route['method'];
        $this->controller_path = base_path('app/controllers/' . $controller . '.php');

        // validate controller file exist
        if (! file_exists($this->controller_path)) { abort(500, 'Internal Server Error'); }
        // load the controller 
        require_once $this->controller_path; 

        // validate class exist
        if (! class_exists($controller)) { abort(500, 'Internal Server Error'); }
        // instantiate controller
        $controller_object = new $controller();

        // validate method exists
        if (! method_exists($controller_object, $method)) { abort(500, 'Internal Server Error'); }
        // execute the method
        $controller_object->$method();
    }
}