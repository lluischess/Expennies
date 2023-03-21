<?php

declare(strict_types = 1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\CategoriesController;
use App\Controllers\TransactionController;
use App\Controllers\TransactionImporterController;
use App\Controllers\ReceiptController;
use App\Middleware\GuestMiddleware;
// Revisar si existe el usuario
use App\Middleware\AuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // Las rutas se ejecutan siempre que se accede al enlace de la web correspondiente y cada ruta ejecuta un controlador y el metodo que tiene que ejecutar para pocesar esa ruta
    // tambien podemos añadir Middlewares para hacer comprobaciónes y validaciónes.

    // Ejemplo esto '/' es la pagina principal, luego llamamos al controlador y a su metodo y por ultimo no obligatoria al middleware
    // Se ejecutara la request del controlador pero antes de devolver la response pasara por el middleware
    $app->get('/', [HomeController::class, 'index'])->add(AuthMiddleware::class);

    // Autentication
    // En Slim podemos agrupar rutas usando group
    $app->group('',function (RouteCollectorProxy $guest){
        $guest->get('/login', [AuthController::class, 'loginView']);
        $guest->get('/register', [AuthController::class, 'registerView']);
        $guest->post('/login', [AuthController::class, 'logIn']);
        $guest->post('/register', [AuthController::class, 'register']);
    })->add(GuestMiddleware::class);

        $app->post('/logout', [AuthController::class, 'logOut'])->add(AuthMiddleware::class);

    $app->group('/categories', function (RouteCollectorProxy $categories) {
        $categories->get('', [CategoriesController::class, 'index']);
        $categories->get('/load', [CategoriesController::class, 'load']);
        $categories->post('', [CategoriesController::class, 'store']);
        // Esto /{id:[0-9]+} indica que la ruta esta esperando un id que es un numero ej: /categories/6
        $categories->delete('/{id:[0-9]+}', [CategoriesController::class, 'delete']);
        $categories->get('/{id:[0-9]+}', [CategoriesController::class, 'get']);
        $categories->post('/{id:[0-9]+}', [CategoriesController::class, 'update']);
    })->add(AuthMiddleware::class);

    $app->group('/transactions', function (RouteCollectorProxy $transactions) {
        $transactions->get('', [TransactionController::class, 'index']);
        $transactions->get('/load', [TransactionController::class, 'load']);
        $transactions->post('', [TransactionController::class, 'store']);
        $transactions->post('/import', [TransactionImporterController::class, 'import']);
        $transactions->delete('/{id:[0-9]+}', [TransactionController::class, 'delete']);
        $transactions->get('/{id:[0-9]+}', [TransactionController::class, 'get']);
        $transactions->post('/{id:[0-9]+}', [TransactionController::class, 'update']);
        $transactions->post('/{id:[0-9]+}/receipts', [ReceiptController::class, 'store']);
        $transactions->get(
            '/{transactionId:[0-9]+}/receipts/{id:[0-9]+}',
            [ReceiptController::class, 'download']
        );
        $transactions->delete(
            '/{transactionId:[0-9]+}/receipts/{id:[0-9]+}',
            [ReceiptController::class, 'delete']
        );
    })->add(AuthMiddleware::class);
};
