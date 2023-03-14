<?php

declare(strict_types = 1);

// Usamos la libreria para gestionar los archivos .env
use Dotenv\Dotenv;

/*
 * Usaremos la class AppFactory de slim que actúa como un contenedor de dependencias y proporciona métodos estáticos que permiten
 * configurar diferentes aspectos de la aplicación Slim, como el manejador de solicitudes, el manejador de errores, el middleware.
 *
 * Es una clase de la biblioteca Slim que proporciona una forma rápida y sencilla de crear una instancia de la aplicación Slim
 * y configurarla con los valores predeterminados.
 *
 */
use Slim\Factory\AppFactory;

/*
 * Es una instrucción de PHP que carga automáticamente todas las dependencias de un proyecto que se han especificado en el archivo composer.json.
 */
require __DIR__ . '/vendor/autoload.php';

/*
 * Es una configuración que se a añadido al proyecto para agregar todas las constantes importantes publicas
 */
require __DIR__ . '/configs/path_constants.php';

/*
 * crea una nueva instancia de la clase Dotenv en modo inmutable, lo que significa que una vez cargadas, las variables de entorno no se pueden cambiar durante la ejecución del script.
 * El parámetro __DIR__ especifica la ruta del directorio actual, que es donde se encuentra el archivo .env.
 */
$dotenv = Dotenv::createImmutable(__DIR__);

// Carga los archivos .env del entorno enviado anteriormente.
$dotenv->load();

// Carga el contenedor de herramientas instanciadas para el backend
return require CONFIG_PATH . '/container/container.php';

