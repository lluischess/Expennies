<?php

declare(strict_types = 1);
// Configuración de las Migraciónes incrementales de Doctrine, esta es su configuración:
return [
    'table_storage'           => [
        'table_name'                 => 'migrations',
        'version_column_name'        => 'version',
        'version_column_length'      => 1024,
        'executed_at_column_name'    => 'executed_at',
        'execution_time_column_name' => 'execution_time',
    ],
    'migrations_paths'        => [
        'Migrations' => __DIR__ . '/../migrations',
    ],
    'all_or_nothing'          => true, // Si se establece en true, todas las migraciones se ejecutarán en una sola transacción. Si alguna migración falla, todas las migraciones en el grupo se revertirán.
    'transactional'           => true, // Si se establece en true, cada migración se ejecutará dentro de una transacción de base de datos.
    'check_database_platform' => true, // Si se establece en true, las migraciones verificarán la plataforma de base de datos antes de ejecutarse y se omitirán si la plataforma no es compatible.
    'organize_migrations'     => 'none', // Define cómo se deben organizar las migraciones en el sistema de archivos. Los valores posibles son 'none', 'year' y 'year_and_month'. En este caso, se utiliza 'none', lo que significa que no se aplicará ninguna organización adicional a las migraciones.
    'connection'              => null, // Una conexión de base de datos personalizada para utilizar con las migraciones. Si se establece en null, se utilizará la conexión de base de datos predeterminada.
    'em'                      => null, // Un administrador de entidades personalizado (EntityManager) para utilizar con las migraciones. Si se establece en null, se utilizará el administrador de entidades predeterminado.
];
