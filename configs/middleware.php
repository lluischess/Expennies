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

// Los middlewares en Slim se ejecutan de manera "en capas", donde el primer middleware registrado actúa como el middleware de entrada más externo y el último middleware registrado actúa como el middleware de salida más interno.
// Esto permite un flujo de control flexible a lo largo del ciclo de vida de la solicitud y la respuesta.

return function (App $app) {
    $container = $app->getContainer();
    $config    = $container->get(Config::class);
    // NOTA IMPORTANTE: Los middleware se ejecutan siempre de arriba a bajo por orden

    // Permite a los clientes simular métodos de solicitud HTTP distintos de los soportados por defecto por los navegadores web (GET y POST). Esto es especialmente útil para las API RESTful, donde se utilizan otros métodos como PUT, DELETE y PATCH.
    // Por ejemplo, si una solicitud POST incluye el parámetro _method=DELETE, el middleware cambiará el método de la solicitud a DELETE, permitiendo que la aplicación maneje la solicitud como si fuera una solicitud DELETE real.
    $app->add(MethodOverrideMiddleware::class);

    // Seguridad de token csrf y el middleware que pasa a los campos del twig lo necesario
    $app->add(CsrfFieldsMiddleware::class);
    // Este middleware ejecuta la funcion que definimos en el container de Dependencias con la clave csrf
    $app->add('csrf');

    // Twig
    // Es un middleware que se encarga de la integración de Twig (un motor de plantillas para PHP) con el framework Slim. Este middleware facilita el uso de Twig en la aplicación Slim y permite renderizar fácilmente plantillas Twig en tus controladores o rutas.
    // Proporcionar acceso al objeto Twig en los atributos de la solicitud (Request). De este modo, podrás acceder al motor de plantillas Twig directamente desde los controladores y rutas.
    // Configurar automáticamente variables globales de Twig, como la URL base, rutas, etc., para que puedas utilizarlas en las plantillas.
    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));

    // Validación de los campos de formularios
    $app->add(\App\Middleware\ValidationExceptionMiddleware::class);
    // Añadimos los errores despues de la validación y los pasamos a Twig para poder imprimirlos en HTML
    $app->add(\App\Middleware\ValidationErrorsMiddleware::class);
    // Recuperamos los datos del formulario que ha introducido el Usuario por si se equivoca y enviamos los errores que almenos no tenga que volver a introducir todo
    $app->add(\App\Middleware\OldFormDataMiddleware::class);

    // Despues de validar iniciamos la session
    $app->add(\App\Middleware\StartSessionsMiddleware::class);

    // Este middleware es para ver los tiempos de las consultas con la app de chrome Clockwork y su libreria intalada en php
    if (AppEnvironment::isDevelopment($config->get('app_environment'))) {
        $app->add(new ClockworkMiddleware($app, $container->get(Clockwork::class)));
    }

    // Middleware para parsear archivos json y xml
    // Es un middleware proporcionado por el framework Slim 4 para analizar el cuerpo de las solicitudes entrantes. Este middleware se encarga de analizar el contenido del cuerpo de la solicitud en función del tipo de contenido (Content-Type) y convertirlo en una estructura de datos adecuada
    $app->addBodyParsingMiddleware();

    // Logger de slim con nuestra configuración
    $app->addErrorMiddleware(
        (bool) $config->get('display_error_details'),
        (bool) $config->get('log_errors'),
        (bool) $config->get('log_error_details')
    );
};
