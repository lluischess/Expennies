<?php

declare(strict_types = 1);

use App\Config;
use App\Middleware\ValidationExceptionMiddleware;
use App\Middleware\StartSessionsMiddleware;
use App\Middleware\ValidationErrorsMiddleware;
use App\Middleware\OldFormDataMiddleware;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

return function (App $app) {
    $container = $app->getContainer();
    $config    = $container->get(Config::class);
    // NOTA IMPORTANTE: Los middleware se ejecutan siempre de arriba a bajo por orden
    // Twig
    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));

    // Validacion de campos formularios
    $app->add(\App\Middleware\ValidationExceptionMiddleware::class);
    // Añade los errores al global de twig para poder extraerlos en el front
    $app->add(\App\Middleware\ValidationErrorsMiddleware::class);
    // Errores antiguis por si se equivoca el usuario que recupere la ultima información añadida
    $app->add(\App\Middleware\OldFormDataMiddleware::class);

    // Inicio de Session
    $app->add(\App\Middleware\StartSessionsMiddleware::class);



    // Logger
    $app->addErrorMiddleware(
        (bool) $config->get('display_error_details'),
        (bool) $config->get('log_errors'),
        (bool) $config->get('log_error_details')
    );
};
