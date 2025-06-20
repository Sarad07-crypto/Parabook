<?php

$basePath = '/ParaBook'; // Set this to your subdirectory
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

$uri = rtrim($uri, '/') ?: '/';

const LOG_SIGN =  'Web/php/login_signup';
const PHP = 'Web/php/';

$routes = [

    '/' => LOG_SIGN . '/login.php',
    '/login' => LOG_SIGN . '/login.php',
    '/logincheck' => LOG_SIGN . '/loginCheck.php',
    '/signup' => LOG_SIGN . '/signup.php',
    '/signcheck' => LOG_SIGN . '/signCheck.php',
    '/verify' => LOG_SIGN . '/verify.php',
    '/logout' => LOG_SIGN . '/logout.php',
   
    
    '/home' => PHP . 'views/home.php',
    '/auth/google' => PHP . 'google/auth_provider.php',
    '/google/callback' => PHP . 'google/controller.php',
    '/facebook/callback' => PHP . 'facebook/f-callback.php',

    '/servicePost' => PHP . 'formDatabase/serviceForm.data.php',

    '/accountSelection' => PHP . 'views/accountSelection.php',

    '/serviceDescription' => PHP . 'views/serviceDesc.php',
    '/bookingpassenger' => PHP . 'views/booking.php',
];

function routeToController($uri, $routes) {
    try {
        $uri = $uri === '' ? '/' : $uri;

        if (array_key_exists($uri, $routes)) {
            $controllerPath = $routes[$uri];
            
            $fullPath = $controllerPath;
            if (file_exists($fullPath)) {                
                require_once $fullPath; // Use require_once to avoid double-inclusion
            } else {
                throw new Exception("Controller file not found: " . $fullPath);
            }
        } else {
            abort(); // Define a custom abort() for 404 or redirect
        }
    } catch (Exception $e) {
        abort(500, $e->getMessage()); // You can show a custom error page
    }
}

function abort($code = 404, $message = '') {
    http_response_code($code);
    
    // Error pages
    $errorPages = [
        404 => 'Web/php/Error/404.html',
        500 => 'Web/php/Error/403.html',
    ];
    
    if (isset($errorPages[$code]) && file_exists($errorPages[$code])) {
        require $errorPages[$code];
    } else {
        
        $errorMessages = [
            404 => 'Page Not Found',
            500 => 'Internal Server Error: ' . $message,
        ];
        die("<h1>{$code} - {$errorMessages[$code]}</h1>");
    }
    die();
}

routeToController($uri, $routes);