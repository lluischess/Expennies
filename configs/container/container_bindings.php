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
use Doctrine\DBAL\DriverManager;
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
use Clockwork\Clockwork;
use Clockwork\DataSource\DoctrineDataSource;
use Clockwork\Storage\FileStorage;

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
 * 3) La función anónima se ejecuta cuando se solicita una instancia de EntityManager::class desde el contenedor de inyección de dependencias.
 * al ejecutar la funcion cuando se instancia pasamos el parametro de la config.
 * Guardamos en la variable ormConfig la configuración de los metadatos utilizando la funcion del ORM de Doctrine createAttributeMetadataConfiguration
 * y le pasamos 2 argumentos de la config, las Entitys de Doctrine y la opcion en bool de que esta en entorno de desarrollo.
 * Retornamos la nueva instancia de EntityManager que es una clase central en Doctrine ORM que se utiliza para realizar operaciones de base de datos en objetos de entidad.
 * Para crear esta instancia, se pasan dos argumentos: una conexión a la base de datos y la configuración de metadatos de ORM.
 * Usando la funcion de Doctrine getConnection crearemos la conexion pasando los 2 argumentos la bbdd y la config de los metadatos.
 *
 * 4) Prepararemos la funcion Twig:class que servira para instanciar lo necesario para utilizar twig. La función anónima se ejecuta cuando se solicita una instancia de Twig::class.
 * Twig es un motor de plantillas PHP que permite generar contenido HTML dinámico de manera eficiente y segura.
 * Al instanciar ejecutara una funcion con 2 parametros: utilizaremos la class Config y la class ContainerInterface para configurar twig
 * Crearemos una variable $twig donde guardar el objeto instanciado y configurado, le pasaremos como primer parametro la dirección donde guardamos las plantillas Twig HTML.
 * y como segundo parametro para las configuraciónes del entorno, como la opcion: cache' indica el directorio donde se almacenarán los archivos de plantillas
 * y la otra opcion 'auto_reload' es un valor booleano que indica si Twig debe volver a compilar las plantillas cuando se detectan cambios en los archivos de plantillas. La función AppEnvironment::isDevelopment se utiliza para habilitar esta opción solo en el entorno de desarrollo.
 * Se añaden tres extensiones a la instancia de Twig:
 * - IntlExtension es una extensión que proporciona funciones y filtros relacionados con la internacionalización y la localización.
 * - EntryFilesTwigExtension es una extensión personalizada que probablemente se utiliza para proporcionar funciones o filtros relacionados con la inclusión de archivos de recursos (como archivos JavaScript o CSS) en las plantillas.
 * - AssetExtension es otra extensión personalizada que puede proporcionar funciones o filtros para manejar recursos, como imágenes o archivos estáticos. El contenedor de inyección de dependencias se utiliza para obtener una instancia de webpack_encore.packages, que se pasa como argumento al constructor de AssetExtension.
 *
 * 5) Este código define dos bindings en el contenedor de inyección de dependencias para configurar y utilizar la integración de Webpack Encore con Twig. Webpack Encore es una biblioteca que simplifica el trabajo con Webpack en aplicaciones PHP y ayuda a gestionar y optimizar los recursos estáticos, como archivos JavaScript y CSS.
 * la clave 'webpack_encore.packages' crea una instancia de Packages que se utiliza para configurar y obtener información sobre los paquetes de recursos estáticos.
 * utiliza el paquete que usa esta clase: JsonManifestVersionStrategy  con el archivo que esta en la siguiente dirección public/build/manifest.json
 * El webpack_encore.tag_renderer: crea una instancia de la clase TagRenderer, que se utiliza para generar etiquetas HTML para incluir recursos estáticos en las plantillas Twig. Para ello, se pasa una instancia de EntrypointLookup y la instancia de Packages creada en el binding anterior. La instancia de EntrypointLookup se crea utilizando la ruta al archivo entrypoints.json, que también se encuentra en el directorio BUILD_PATH. Este archivo contiene información sobre los puntos de entrada y los recursos asociados.
 *
 * 6) La siguiente clave de la tabla de dependencias ResponseFactoryInterface hace que cada vez que se instancie se instancia, se ejecutara
 * una funcion que usa como parametro el objeto app instanciado hace que app ejecute su funcion: getResponseFactory()
 * Esto basicamente devuelve un response cada vez que usamos ResponseFactoryInterface::class
 *
 * 7) La key AuthInterface::class cargara la clase Auth en el container
 *
 * 8) La key UserProviderServiceInterface::class cargara la clase UserProviderService en el container
 *
 * 9) La key SessionInterface::class cargara una función que creara la nueva Session()
 *
 * 10) La key RequestValidatorFactoryInterface::class cargara la clase RequestValidatorFactory en el container
 *
 * 11) La key csrf ejecutara una funcion que creara la instancia Guard que es una seguridad de csrf integrada en slim a la que hay que añadir los parametros de la responseFactory y nuestra clase csrf personalizada
 *
 * 12) La key Filesystem::class ejecutara una función (con parametro de Configuración) que guardara la configuración storage.driver la cual con el match con el que determinara si el valor es: StorageDriver::Local y creara una instancia del LocalFilesystemAdapter y devuelve la instancia del Filesystem.
 *
 * 13) La key Clockwork::class ejecutara una función (Con parametro entityManager) la cual creara una instancia de Clockwork: es una herramienta de desarrollo para PHP que ayuda en la depuración y el análisis de rendimiento de aplicaciones web. Proporciona información detallada sobre la ejecución de su aplicación, incluyendo consultas a bases de datos, tiempo de ejecución, uso de memoria, entre otros.
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
    EntityManager::class                    => function (Config $config) {
        $ormConfig = ORMSetup::createAttributeMetadataConfiguration(
            $config->get('doctrine.entity_dir'),
            $config->get('doctrine.dev_mode')
        );

        return new EntityManager(
            DriverManager::getConnection($config->get('doctrine.connection'), $ormConfig),
            $ormConfig
        );
    },
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
    },
    Clockwork::class => function(EntityManager $entityManager) {
        $clockwork = new Clockwork();

        $clockwork->storage(new FileStorage(STORAGE_PATH . '/clockwork'));
        $clockwork->addDataSource(new DoctrineDataSource($entityManager));

        return $clockwork;
    }
];
