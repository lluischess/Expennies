# DOCUMENTATION PROJECT LIST
 THIS REPOSITORY IS A LIST OF BACKEND PHP TOOLS THAT ALL BACKEND DEVELOPER SHOULD HAVE DONE.

 This list of projects will be updated as improvements appear in PHP

# BACKEND uset on this repo:
 - PHP
 - SlimPHP
 - MySQL Doctrine ORM and Migrations
 - Symfony Components
 - PHP-DI

# FRONTEND uset on this repo:
 - Bootstrap
 - Some HTML-JS webpack
 - Twig



# List of Tools:

 1) Node JS https://nodejs.org/en/
 2) NPM https://docs.npmjs.com/downloading-and-installing-node-js-and-npm
 3) Webpack https://webpack.js.org/
 4) Symfony Webpack Encore https://symfony.com/doc/current/frontend.html
 5) Bootstrap https://getbootstrap.com/
 6) SASS https://sass-lang.com/

## Other Utilitys

 - https://drawsql.app/ (Mapas de Bases de datos)
 - https://crackstation.net/ (Revisar vulnerabilidades del hasing)
 - Herramienta de validación: https://github.com/vlucas/valitron

 # Estructura

 ### Carpeta "public"

 El index.php es el punto de entrada a la Aplicación.

 ### Archivo raiz "bootstrap.php"

 Crea algunas cosas como preparar las variables de entorno .env llama algunas clases del slimphp y carga el autoload y las constantes de las rutas del proyecto.
 Ademas cargara un contenedor de Dependencias que necesita el proyecto situado en la siguiente ruta: configs\container\container_bindings.php
 Tambien cargamos un Middleware para integrar a nuestra App Twig y un Logger

### Carpeta "configs"

1 - En ella aparecen 4 archivos importantes app.php(El archivo de configuración donde guardaremos los parametros de configuración de la app)
                                        middleware.php(Para unificar algunas herramientas como twig y el logger de slimphp con nuestra app)
                                        migrations.php(Contiene el array con los valores configurados para crear migraciones)
                                        path_constants.php(Guardamos las rutas mas usadas de la app en constantes)

2 - Carpeta "configs" ===> "commands":
    Contiene los commandos de Doctrine Basicos

3 - Carpeta "configs" ===> "container":
    Contiene un DI Dependency injection container de un conjunto de dependencias Importante(DoctrineORM, SlimPHP, SymfonyComponent y Twig)

    Cuando el container.php es llamado este llamara al container_bindings.php el cual crea una entrada de la class config y requeriendo la aplicación php config en el constructor.

    Despues comprobamos que para la entrada del Twig este la app en modo desarrollo o no y tambien añade algunas extensiones.

    Luego se añaden 2 webpacks para twig.

4 - Carpeta "configs" ===> "routes":
    Contiene el archivo web de las routes como un laravel o symfony etc

### Archivo "package.json"

Contiene todas las dependencias usadas en Frontend.

### Archivo "webpack.config.js"

Contiene la configuración establecida del Webpack de symfony componenet para trabajar con frontend

### Carpeta "resources"

1- Carpeta "css" contiene todo el diseño de la app. Usando scss

2- Carpeta "images" contiene todas las imagenes y videos del proyecto

3- Carpeta "js" contiene la funcionalidad del front

4- Carpeta "views" contiene las Plantillas de la app del front usando Twig

### Archivo "composer.json"

COntiene todas las herramientas que usaremos y necesita el proyecto para funcionar

### Archivo "composer.lock"

Contiene toda la estructura de herramientas que necesitamos y las versiónes requeridas

### Archivo ".env.example"

Es el archivo de variables de entorno, cuando se empiece un nuevo proecto hay que copiarlo y dejar las variables de tu entorno añadidas dejando como archivo final un .env



# Install Project:

## Pre Instalación:

1) Configurar el Servidor:
   1) Apache:  
      1) Revisar que los index.php se ejecuten lo primero de todo en el archivo /etc/httpd/conf/httpd.conf "DirectoryIndex index.php index.html"
      2) Si tienes htaccess con lo siguiente: -RewriteEngine On
                                              -RewriteCond %{REQUEST_FILENAME} !-f
                                              -RewriteRule ^ index.php [QSA,L]
      3) Por ultimo revisar que el vhost de tu dominio apunte a la carpeta "public"
   2) Nginx:
      1) Lo mismo revisar que se ejecuten los index.php en la ruta: /etc/nginx/nginx.conf (index index.php index.html;)
      2) Mirar de pulir la config de Nginx con buestros requisitos en la ruta: docker/nginx/nginx.conf
    
## Instalación

1) Clone Repo
2) Opcional: Usando Docker estaremos usando e instalando node npm para instalar bootstrap
3) Opcional: Sin Docker hay que instalar node y npm aparte
4) Rellenar el .env las variables de entorno con tu entorno
5) Opcional: Una vez añadidas vamos a ir a la carpeta docker por terminal y usaremos "docker-compose up -d --build"
6) Hacer un "Install composer"
7) Instalar Node.js https://www.cursosgis.com/como-instalar-node-js-y-npm-en-4-pasos/
8) Conectar BBDD al proyecto PHPStorm o VSC
9) Hacer "php expennies migrations:migrate"
10) Hacer "npm install" and "npm run dev"
11) Usando Laragon con virtual host Habilitado: http://expennies.test/
12) Creamos las app/Entitys los modelos con las clases etc
13) Una vez creadas hacemos la migración a la BBDD "php expennies diff"
14) Luego migramos la migración "php expennies migrations:migrate"



## Orden de Ejecución:

1) public/index.php
2) bootstrap.php
3) vendor/autoload.php
4) configs/path_constants.php
5) container/container.php
6) container/container_bindings.php
7) Se ejecuta el resto del proyecto

## Cosas a corregir y mejorar:

1) Añadir un poco de seguridad a las cookies de session // Pero antes añadimos algunos parametros de seguridad a la sessión:
   //session_set_cookie_params(['secure' => true, 'httponly' => true, 'samesite' => 'lax']);
   https://stackoverflow.com/questions/53172484/session-regenerate-id-and-security-attributes
2) 