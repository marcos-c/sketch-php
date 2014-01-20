<?php
require_once '../../vendor/autoload.php';

use dflydev\markdown\MarkdownParser;

$parser = new MarkdownParser();

$content = <<<'EOD'
##Instalación

Sketch es fácil de instalar. Los requerimientos mínimos son un servidor web apache con PHP 5.1.6 o superior con los
módulos allow_url_fopen, gd y libxml.

Tecnicamente no necesita un motor de base de datos, pero imaginamos que la mayoría de las aplicaciones usaran una.
Sketch soporta 2 motores de base de datos: MySQL y PostgreSQL.

##Permisos

Sketch utiliza la carpeta cache para almacenar varios archivos que se generan desde la librería.

Por lo tanto, en este directorio y sus subdirectorios Sketch tiene que tener permisos de escritura.

##Configuración

Para configurar Sketch usamos un archivo, generalmente context.xml, desde el cual se define el contexto de la
aplicación.

El contexto de la aplicación incluye los datos para la conexión a la base de datos, configuración de los diferentes
filtros, reglas para el mod_rewrite, etc.

El esqueleto propuesto para el desarrollo de aplicaciones con Sketch es tan sencillo como:

* cache
* config
    * context.xml
* library
    * Sketch
*index.php

Para mejorar la seguridad recomendamos definir la raiz de los documentos del servidor web por encima de cache, config y
library:

* cache
* config
    * context.xml
* public
    * index.php
* library
    * Sketch

Para que Sketch funcione tenemos que definir en index.php el APPLICATION_PATH, añadir al include path la carpeta library
y incluir el archivo Stub.php.

    <?php
        if (!defined('APPLICATION_PATH')) {
            define('APPLICATION_PATH', realpath(dirname(__FILE__)));
            set_include_path(realpath(APPLICATION_PATH.'/library').PATH_SEPARATOR.get_include_path());
        }

        /** @var $this SketchResponsePart */
        require_once 'Sketch/Stub.php';
    ?>

El archivo Stub.php se encarga de iniciar la aplicación. Concretamente se encarga de la inicialización del contexto, los
objetos con la petición, sesión, archivos de localización, herramientas para la depuración de la aplicación, conexión a
la base de datos y de finalmente imprimir la respuesta.

Si Sketch devuelve un error relacionado con la zona horaria es posible que su servidor no permita la asignación de una
zona horaria por defecto.

Sketch utiliza UCT como zona horaria para todos los calculos y se recomienda su utilización. Si el servidor esta en
dicha zona horaria pero sigue registrando el error puede comentar la siguiente linea en Application.php.

    date_default_timezone_set('UCT');

##Nuestra primera aplicación

Sketch facilita una base para su aplicación. Y se encarga de gestionar desde la petición inicial del usuario hasta la
presentación de la respuesta del servidor utilizando los principios del modelo vista controlador.

También recomienda una estructura para nombres de fichero, tablas en la base de datos para mejorar la consistencia y
lógica de la aplicación. Es un concepto simple pero que facilita la reutilización y comprensión del código fuente de la
aplicación.

La mejor forma de experimentar y aprender Sketch es sentarnos y construir algo. Para empezar construiremos una
aplicación de blog sencilla.

Este tutorial le mostrara como crear una aplicación simple de blog. Descargaremos Sketch y lo instalaremos, crearemos y
configuraremos la base de datos y lo suficiente para listar, añadir, editar y eliminar las entradas del blog.

Esto es lo que necesitaremos:

* Un servidor en funcionamiento con Apache.
* Un servidor de base de datos MySQL.
* Conocimientos básicos de PHP, programación orientada a objetos y del modelo vista controlador.
* Una copia de Sketch.

Una vez instalado Sketch el siguiente paso es definir la base de datos. Para lo que usaremos el siguiente script:

    CREATE TABLE post(
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(50),
        body TEXT,
        createdDateTime DATETIME DEFAULT NULL,
        modifiedDateTime DATETIME DEFAULT NULL
    );

    INSERT INTO post (title, body, createdDateTime ) VALUES ('El titular', 'El cuerpo del artículo.', NOW());
    INSERT INTO post (title, body, createdDateTime ) VALUES ('Otro titular', 'El cuerpo del artículo.', NOW());
    INSERT INTO post (title, body, createdDateTime ) VALUES ('El último titular', 'El cuerpo del artículo.', NOW());

El nombre de los campos no es arbitrario. Sketch aprovecha estructuras predefinidas en los nombres de los campos en la
base de datos para reducir el número de parámetros que tienen que ser configurados. Por ejemplo, el campo por defecto
que contiene el identificador de un registro se llama id. En el caso de claves externas corresponde a tabla_id. Las
variables se escriben en minúsculas y separadas por guiones bajos (i, count, step, from_date). Las clases con cada letra
inicial en mayúsculas y en singular (Rate, RateClass). Dentro de las clases también distinguimos contenedores,
iteradores, clases abstractas añadiendo como sufijos List, Iterator, Abstract, etc.

Para que la aplicación tenga acceso a la base de datos hay que añadir un context.xml dentro de la carpeta config.

<?xml version="1.0" encoding="UTF-8"?>
    <context name="blog" locale="es">
    <driver type="SketchConnectionDriver" class="MySQLConnectionDriver" source="MySQL.php">
        <host>localhost</host>
        <database></database>
        <user></user>
        <password></password>
    </driver>
</context>

Este archivo de configuración es el que define el contexto de la aplicación. Para cada contexto definimos un nombre
(name) y una localización (locale). El nombre nos permite diferenciar o integrar varias aplicaciones que comparten
sesiones. La localización nos permite definir un idioma o un idioma y país por defecto para traducciones y la
presentación de números y fechas.

Para conectar a la base de datos usamos uno de los controladores (driver) disponibles. En este caso estamos usando el
MySQLConnectionDriver. Los controladores están ubicados en la carpeta Sketch/Resource/Connection/Driver.

Una vez configurado el acceso a la base de datos vamos a definir nuestra primera clase modelo.

Dentro de la carpeta library crearemos la carpeta Blog y dentro de esta crearemos un fichero con extensión PHP que
llamaremos Post.php.

El contenido de Post.php será el siguiente:

    <?php
    require_once 'Sketch/Factory.php';
    require_once SketchFactory::scaffold('post');

    class Post extends AbstractPost {

    }

Luego crearemos una carpeta dentro de la carpeta Blog que llamaremos Blog y dentro crearemos un fichero con extensión
PHP que llamaremos List.php con el siguiente contenido:

    <?php
    class BlogList extends SketchObjectList {
        private $size;

        function getSize() {
            if ($this->size == null) {
                $connection = $this->getConnection();
                $this->size = intval($connection->queryFirst("SELECT count(*) FROM post"));
            }
            return $this->size;
        }

        function getIterator($parameters = null) {
            $connection = $this->getConnection();
            $order_by = $this->getOrderBy() ? 'ORDER BY '.$this->getOrderBy() : '';
            $offset = $this->getOffset() ? 'OFFSET '.$this->getOffset() : '';
            $limit = $this->getLimit() ? 'LIMIT '.$this->getLimit() : '';
            return new PostIterator(
                $connection->query("SELECT * FROM post WHERE $order_by $limit $offset"),
                $connection->queryFirst("SELECT count(*) FROM post")
            );
        }
    }
EOD;
?>
<!DOCTYPE html>
<html lang="es" ng-app>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Release Pad Sketch</title>
    <link href="/sketch/components/bootstrap-css/css/bootstrap.css" rel="stylesheet">
    <link href="/sketch/components/bootstrap-css/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="/sketch/components/rainbow/themes/github.css" rel="stylesheet">
    <link href="/sketch/css/application.css" rel="stylesheet">
    <script src="/sketch/components/angularjs/angular.js"></script>
    <script src="/sketch/components/rainbow/js/rainbow.js"></script>
    <script src="/sketch/components/rainbow/js/language/generic.js"></script>
    <script src="/sketch/components/rainbow/js/language/php.js"></script>
    <script type="text/javascript" src="//use.typekit.net/oeg0ugk.js"></script>
    <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
</head>
<body>
    <div class="container">
        <div class="header navbar navbar-static-top">
            <div class="navbar-inner">
                <a href="http://releasepad.com/sketch" class="brand">Sketch</a>
                <ul class="nav">
                    <li><a href="/sketch/index.php"><i class="icon-home"></i> Inicio</a></li>
                    <li class="active"><a href="/sketch/learn/index.php">Aprende</a></li>
                </ul>
                <a href="http://releasepad.com" class="by-releasepad"><span class="by">por</span><span class="release">Release</span><span class="pad">Pad</span></a>
            </div>
        </div>
        <div class="body row">
            <div class="span3">
                <ul class="nav nav-list">
                    <li><a href="/sketch/learn/index.php"><i class="icon-chevron-right"></i> Introducción</a></li>
                    <li><a href="/sketch/learn/html.php"><i class="icon-chevron-right"></i> HTML</a></li>
                    <li><a href="/sketch/learn/javascript.php"><i class="icon-chevron-right"></i> JavaScript</a></li>
                    <li><a href="/sketch/learn/php.php"><i class="icon-chevron-right"></i> PHP</a></li>
                    <li><a href="/sketch/learn/mysql.php"><i class="icon-chevron-right"></i> MySQL</a></li>
                    <li class="active">
                        <a href="/sketch/learn/sketch.php"><i class="icon-chevron-right"></i> Sketch</a>
                        <ul class="nav nav-list">
                            <li><a href="/sketch/learn/sketch.php"><i class="icon-chevron-right"></i> Instalación</a></li>
                            <li><a href="/sketch/learn/sketch.php"><i class="icon-chevron-right"></i> Nuestra primera aplicación</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="span9">
                <?php print $parser->transformMarkdown($content); ?>
            </div>
        </div>
    </div>
</body>
</html>