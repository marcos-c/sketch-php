<?php
require_once '../vendor/autoload.php';

use \Michelf\Markdown;

// TODO Añadir información con relación al Post Redirect Get (PRG)
$content_left = <<<'EOD'
# ¿Qué es Sketch?

Sketch es una librería de utilidades gratuita, de código abierto y orientada a objetos para el desarrollo de
aplicaciones y páginas Web en PHP.

También es una infraestructura digital basada en una versión simplificada del modelo
<abbr title="Model View Controller">MVC</abbr>.

Desarrollar aplicaciones y páginas Web con Sketch es fácil gracias a que ofrece soluciones integradas para la
persitencia de datos (<abbr title="Create, Read, Update and Delete">CRUD</abbr>), internacionalización
(<abbr title="Internationalization">i18n</abbr>) y localización (<abbr title="Localization">i10n</abbr>) de contenidos,
plantillas Web para separarlos de la presentación y listas de control de acceso
(<abbr title="Access Control List">ACL</abbr>) para controlar su visibilidad.

Para desplegar una aplicación normal desarrollada con Sketch sólo necesitas acceso por <abbr title="File Transfer Protocol">FTP</abbr> al servidor. Y son fáciles de
mantener y optimizar para buscadores (<abbr title="Search Engine Optimization">SEO</abbr>) porque la infraestructura que
hemos desarrollado da prioridad a la accesibilidad y legibilidad de las vistas y plantillas. Para que no sólo el
programador con años de experiencia se beneficie de su utilización si no también puedan hacerlo otros miembros
de tú equipo con menos o ninguna experiencia en Sketch.
EOD;
$content_left_eli = <<<'EOD'
# ¿Qué es Sketch?

Sketch es una librería de utilidades open source totalmente gratuita orientada a objetos para el desarrollo de
aplicaciones y páginas Web en PHP.

También es una infraestructura digital basada en una versión simplificada del modelo
<abbr title="Model View Controller">MVC</abbr>.

Desarrollar aplicaciones y páginas Web con Sketch es completamente FACIL gracias a que ofrece soluciones integradas
para la persitencia de datos (CRUD), internacionalización (i18n) y localización (i10n) de contenidos.

Posee tambien plantillas Web para separarlos de la presentación y listas de control de acceso (ACL) para un mayor control de visibilidad.

Para desarrollar una aplicación con Sketch tan sólo necesitas tener acceso por FTP al servidor.
EOD;
$content_right = <<<'EOD'
## Requerimientos

Mínimos:

Servidor Linux con Apache y PHP 5.3.10 con los módulos allow\_url\_fopen y libxml.

Recomendados:

Servidor Linux con Apache y PHP 5.3.10 con los módulos allow\_url\_fopen, libxml y gd.

MySQL 5.5.29 o PostgreSQL 8.4.5.

## Licencia

Sketch se distribuye bajo la licencia [GNU Lesser General Public License v2.1](http://opensource.org/licenses/lgpl-2.1.php).

El contenido de este sitio Web esta publicado bajo la [licencia MIT](/sketch/license.php).

## Descargar

Próximamente disponible desde [Composer](http://getcomposer.org/).
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
                    <li class="active"><a href="/sketch/index.php"><i class="icon-home"></i> Inicio</a></li>
                    <li><a href="/sketch/learn/index.php">Aprende</a></li>
                </ul>
                <a href="http://releasepad.com" class="by-releasepad"><span class="by">por</span><span class="release">Release</span><span class="pad">Pad</span></a>
            </div>
        </div>
        <div class="body row">
            <div class="span6">
                <?php print Markdown::defaultTransform($content_left); ?>
            </div>
            <div class="span6">
                <?php print Markdown::defaultTransform($content_right); ?>
                <? /* <label>Name:</label>
                <input type="text" ng-model="yourName" placeholder="Enter a name here">
                <hr>
                <h1>Hello {{yourName}}!</h1> */ ?>
            </div>
        </div>
    </div>
</body>
</html>