<?php 

function dd (mixed $value): void {
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
    die();
} 

function update_logs (string $message): void {
    $file_name = base_path('/storage/logs/log_file.txt');
    
    if (file_exists($file_name) && filesize($file_name) >= 1024*1024) {
        rename($file_name, base_path('/storage/logs/app_' . time() . '.log'));
    }
    
    file_put_contents(
        $file_name, 
        "[" . date('Y-m-d H:i:s') . "]" . $message . PHP_EOL, 
        FILE_APPEND
        );
}

function abort (int $code=404, string $value = 'Page Not Found'): void {
    http_response_code($code);
    switch ($code) {
        case 404:
            $value = 'Page Not Found';
            break;
        case 500:
            $value = 'Internal Server Error';
            break;
    }
    die($value);
}

function base_path (string $path = ''): string {
    return dirname(__DIR__, 2) . '/' . ltrim($path, '/');
}

function view (string $view_name, array $data = []): void {
    
    $target_path = base_path('/views' . '/' . $view_name . '.view.php');
    if (! file_exists($target_path)) {
        abort(404);
    }
    extract($data, EXTR_SKIP);
    ob_start();
    require $target_path;  // the view
    $content = ob_get_clean();
    require base_path('views/layouts/app.view.php'); // the layout with the view within it
}

function partial (string $partial_name, array $data = []): void {
    $target_path = base_path('views/partials/' . $partial_name . '.view.php');
    if (! file_exists($target_path)) {
        abort(404); 
    }
    extract($data, EXTR_SKIP);
    require $target_path;
}

function redirect (string $path): void {
    header("Location: " . $path);
    exit();
}

function flash (string $key, mixed $value = null): mixed {
    if (func_num_args() === 2) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }
    return $_SESSION['flash'][$key] ?? null;
}

function clear_flash(): void {
    unset($_SESSION['flash']);
}

function old(string $key, mixed $default_value=''): array|string {
    $old_input = flash('old_input') ?? [];
    return $old_input[$key] ?? $default_value;
}

function error(string $key): array {
    $errors = flash('errors') ?? [];
    return ($errors[$key]) ?? [];
}

function auth(): bool {
    return isset($_SESSION['user']);
}

function user(): ?array {
    return $_SESSION['user'] ?? null;
}

function guest(): bool {
    return ! auth();
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $credentials = require base_path('app/config/db_credentials.php');
        $db = new Database($credentials);
        $pdo = $db->connect();
    }
    return $pdo;
}

function back_with_errors(array $errors, array $old_input, string $uri): void {
    flash('errors', $errors);
    flash('old_input', $old_input);
    redirect(trim($uri));
}