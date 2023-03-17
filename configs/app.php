<?php

declare(strict_types = 1);
// Archivo de configuración de los parametros definidos para la app
use App\Enum\AppEnvironment;
use App\Enum\StorageDriver;

// Si existe la variable env nos quedamos con ese estado o sino como predeterminado estara en Producción
$appEnv = $_ENV['APP_ENV'] ?? AppEnvironment::Production->value;

return [
    'app_name'              => $_ENV['APP_NAME'],
    'app_version'           => $_ENV['APP_VERSION'] ?? '1.0',
    'app_environment'       => $appEnv,
    'display_error_details' => (bool) ($_ENV['APP_DEBUG'] ?? 0), // Un valor booleano que indica si se deben mostrar los detalles de los errores en la aplicación.
    'log_errors'            => true,
    'log_error_details'     => true,
    'doctrine'              => [
        'dev_mode'   => AppEnvironment::isDevelopment($appEnv),
        'cache_dir'  => STORAGE_PATH . '/cache/doctrine', // El directorio donde se almacenará el caché de Doctrine.
        'entity_dir' => [APP_PATH . '/Entity'], // Un array de directorios donde se encuentran las entidades de la aplicación.
        'connection' => [ // Conexión a la BBDD
            'driver'   => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
            'host'     => $_ENV['DB_HOST'] ?? 'localhost',
            'port'     => $_ENV['DB_PORT'] ?? 3306,
            'dbname'   => $_ENV['DB_NAME'],
            'user'     => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
        ],
    ],
    'storage' => [
        'driver' => StorageDriver::Local, // La libreria de almacenamiento que se usara
    ]
];
