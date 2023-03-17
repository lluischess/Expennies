<?php

declare(strict_types = 1);

// Usamos el framework Slim
use Slim\App;

// Guardamos en el container algunas cosas que usaremos en el proyecto
$container    = require __DIR__ . '/../bootstrap.php';

//Utiliza el contenedor de inyección de dependencias ($container) para obtener una instancia de la clase App. La clase App suele ser la clase principal de una aplicación web, responsable de manejar el enrutamiento, la configuración, el middleware y otros aspectos esenciales de la aplicación.
//Llama al método run() en la instancia de la clase App. El método run() se encarga de iniciar y ejecutar la aplicación. Por lo general, esto implica procesar la solicitud HTTP entrante, pasarla a través de los middleware y enrutadores configurados, y finalmente generar y enviar la respuesta HTTP al cliente.
$container->get(App::class)->run();
