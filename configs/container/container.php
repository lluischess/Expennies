<?php

declare(strict_types=1);

/*
 * Helper to create and configure a Container.
 * With the default options, the container created is appropriate for the development environment.
 */
use DI\ContainerBuilder;

// Crear una nueva instancia del constructor del contenedor
$containerBuilder = new ContainerBuilder();

/*
 * Es utilizada para añadir definiciones al contenedor creado con DI\ContainerBuilder
 * Las definiciones en este contexto se refieren a la configuración y dependencias que el contenedor debe inyectar en las clases que lo utilizan.
 * Por ejemplo, si una clase necesita una instancia de otra clase para funcionar, se pueden definir las dependencias en el contenedor y el contenedor se encargará de inyectar la instancia correspondiente cuando se instancie la clase.
 * En este Caso la definicion es un archivo con todas las dependencias que inyectamos en el proyecto en un array
 */
$containerBuilder->addDefinitions(__DIR__ . '/container_bindings.php');

/*
 * El método build() es utilizado para crear una instancia del contenedor con la configuración definida previamente mediante métodos como useAutowiring(), useAnnotations() y addDefinitions().
 * Una vez que se ha llamado al método build(), el contenedor está listo para ser utilizado y se puede obtener objetos o instancias de clases que han sido configurados como dependencias dentro del contenedor.
 */
return $containerBuilder->build();
