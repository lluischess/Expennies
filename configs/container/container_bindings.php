<?php

declare(strict_types = 1);

use App\Config;
use App\Auth;
use App\Csrf;
use App\Session;
use App\Enum\AppEnvironment;
use App\Enum\StorageDriver;
use App\Contracts\AuthInterface;
use App\Contracts\UserProviderServiceInterface;
use App\Contracts\SessionInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use App\Services\UserProviderService;
use App\RequestValidators\RequestValidatorFactory;
use Doctrine\ORM\EntityManager;
use League\Flysystem\Filesystem;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
use Twig\Extra\Intl\IntlExtension;

/*
 * La función create es utilizada para crear una instancia de una clase y resolver sus dependencias mediante el contenedor de inyección de dependencias de PHP-DI.
 * Es una forma más concisa y sencilla de crear instancias de objetos que tienen dependencias y que necesitan ser inyectados en el constructor.
 */
use function DI\create;

// Este container tiene todas las dependencias que usamos en el proyecto
/* Array por indices:
 * 1) Entonces a la class App que es la classe principal de slim le asignamos el valor de una funcion que se ejecutara cuando se resuelva la dependencia.
 * Usamos el ContainerInterface del Psr que es una Interfaz para preparar lo que añadiremos en el contenedor y lo que instanciaremos.
 * Preparamos la instancia de AppFactory que proporciona metodos del framework slim y le seteamos el container.
 * Despues le assiganaremos a una variable el codigo de los middleware.php y a la otra las rutas web del proyecto (En este caso, el archivo middleware.php debe definir una función que tome una instancia de la aplicación Slim y agregue los middleware necesarios a esa instancia.)
 * Una vez creada la instancia con create() de la aplicación, se pueden agregar rutas, middleware, controladores y otras configuraciones necesarias para la aplicación.
 * Entonces $router($app); ejecuta la funcion del archivo de las rutas con la app que hemos creado y lo mismo con los middleware y retornamos la app con todo ejecutado.
 *
 * 2) La segunda clave es la Config::class que será la clase de las configuraciónes
 * Primero crea una instancia de la clase Config.
 *  La función create es un método de la clase Slim\CallableResolver, que se utiliza para crear objetos de clases a través de la inyección de dependencias
 * Y usamos el metodo constructor de slim para añadir el parametro al constructor de la class Config.
 * El parametro es un archivo llamado app.php que se ejecuta al instanciar la class config.
 *
 *
 *
 */
return [
    App::class => function(ContainerInterface $container) {
        AppFactory::setContainer($container);

        $addMiddlewares = require CONFIG_PATH . '/middleware.php';
        $router = require CONFIG_PATH . '/routes/web.php';

        $app = AppFactory::create();
        $router($app);
        $addMiddlewares($app);

        return $app;
    },

    Config::class                 => create(Config::class)->constructor(require CONFIG_PATH . '/app.php'),
    EntityManager::class          => fn(Config $config) => EntityManager::create(
        $config->get('doctrine.connection'),
        ORMSetup::createAttributeMetadataConfiguration(
            $config->get('doctrine.entity_dir'),
            $config->get('doctrine.dev_mode')
        )
    ),
    Twig::class                   => function (Config $config, ContainerInterface $container) {
        $twig = Twig::create(VIEW_PATH, [
            'cache'       => STORAGE_PATH . '/cache/templates',
            'auto_reload' => AppEnvironment::isDevelopment($config->get('app_environment')),
        ]);

        $twig->addExtension(new IntlExtension());
        $twig->addExtension(new EntryFilesTwigExtension($container));
        $twig->addExtension(new AssetExtension($container->get('webpack_encore.packages')));

        return $twig;
    },
    /**
     * The following two bindings are needed for EntryFilesTwigExtension & AssetExtension to work for Twig
     */
    'webpack_encore.packages'     => fn() => new Packages(
        new Package(new JsonManifestVersionStrategy(BUILD_PATH . '/manifest.json'))
    ),
    'webpack_encore.tag_renderer' => fn(ContainerInterface $container) => new TagRenderer(
        new EntrypointLookup(BUILD_PATH . '/entrypoints.json'),
        $container->get('webpack_encore.packages')
    ),
    ResponseFactoryInterface::class => fn(App $app) => $app->getResponseFactory(),
    AuthInterface::class => fn(ContainerInterface $container) => $container->get(Auth::class),
    UserProviderServiceInterface::class => fn(ContainerInterface $container) => $container->get(
        UserProviderService::class
    ),
    SessionInterface::class => fn() => new Session(),
    RequestValidatorFactoryInterface::class => fn(ContainerInterface $container) => $container->get(RequestValidatorFactory::class),
    'csrf' => fn(ResponseFactoryInterface $responseFactory, Csrf $csrf) => new Guard($responseFactory, persistentTokenMode: true, failureHandler: $csrf->failureHandler() ),
    Filesystem::class => function(Config $config) {
        $adapter = match($config->get('storage.driver')){
            StorageDriver::Local => new \League\Flysystem\Local\LocalFilesystemAdapter(STORAGE_PATH),
        };

        return new \League\Flysystem\Filesystem($adapter);
    }
];
