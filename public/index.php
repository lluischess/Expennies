<?php

declare(strict_types = 1);

// Usamos el framework Slim
use Slim\App;

// Guardamos en el container algunas cosas que usaremos en el proyecto
$container    = require __DIR__ . '/../bootstrap.php';

$container->get(App::class)->run();
