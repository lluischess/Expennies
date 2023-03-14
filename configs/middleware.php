<?php

declare(strict_types = 1);

use App\Config;
use App\Enum\AppEnvironment;
use App\Middleware\CsrfFieldsMiddleware;
use App\Middleware\ValidationExceptionMiddleware;
use App\Middleware\StartSessionsMiddleware;
use App\Middleware\ValidationErrorsMiddleware;
use App\Middleware\OldFormDataMiddleware;
use App\Middleware\AuthenticateMiddleware;
use Slim\App;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Clockwork\Clockwork;
use Clockwork\Support\Slim\ClockworkMiddleware;

return function (App $app) {
    $container = $app->getContainer();
    $config    = $container->get(Config::class);
    // NOTA IMPORTANTE: Los middleware se ejecutan siempre de arriba a bajo por orden

    // Este Middleware hace que en las rutas accepte algo mas que no sea ni post ni get
    $app->add(MethodOverrideMiddleware::class);

    // Seguridad de token csrf y el middleware que pasa a los campos del twig lo necesario
    $app->add(CsrfFieldsMiddleware::class);
    $app->add('csrf');

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

    // este middleware es para ver los tiempos de las consultas con la app de chrome Clockwork
    if (AppEnvironment::isDevelopment($config->get('app_environment'))) {
        $app->add(new ClockworkMiddleware($app, $container->get(Clockwork::class)));
    }

    // Middleware para parsear archivos json y xml
    $app->addBodyParsingMiddleware();

    // Logger
    $app->addErrorMiddleware(
        (bool) $config->get('display_error_details'),
        (bool) $config->get('log_errors'),
        (bool) $config->get('log_error_details')
    );
};
