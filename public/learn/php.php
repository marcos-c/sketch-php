<?php
require_once '../../vendor/autoload.php';

use dflydev\markdown\MarkdownParser;

$parser = new MarkdownParser();

$content = <<<'EOD'
# PHP

## Introducción

El PHP es un lenguaje de código abierto ampliamente utilizado en Internet y especialmente adecuado para el desarrollo
de páginas Web. Es un lenguaje interpretado que se puede integrar dentro de un documento HTML por medio de las
etiquetas `<?php` y `?>`.

En su configuración habitual el intérprete de PHP se instala en conjunto con un servidor Web. El servidor Web se
encarga de enviar los documentos que contienen PHP al interprete y de servir el resultado al usuario que inicio la
petición.

Por ejemplo, el siguiente documento HTML 5 contiene PHP que imprime el famoso ¡Hola Mundo!

    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>PHP Test</title>
    </head>
    <body>
        <?php echo "¡Hola Mundo!"; ?>
    </body>
    </html>

El servidor Web normalmente esta configurado para interpretar todos los archivos con la extensión `.php` como documentos
HTML con PHP integrado.

## Variables

En programación cuando queremos guardar un valor o el resultado de una operación usamos variables. Las variables por lo
tanto representan un espacio en la memoria del programa y el nombre que les damos es su identificador. En PHP las
variables van siempre precedidas del símbolo `$`.

Por ejemplo, el siguiente documento es una variación del anterior que imprime ¡Hola Marcos! en lugar de ¡Hola Mundo!

    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>PHP Test</title>
    </head>
    <body>
        <?php
            $nombre = "Marcos";
            echo "¡Hola $nombre!";
        ?>
    </body>
    </html>

El carácter `=` es el operador de asignación del PHP. Nos permite asignar un valor a una variable. En este caso estamos
asignando el valor `Marcos` a la variable `$nombre`.

Cualquier texto contenido entre `"` representa una cadena de carácteres en PHP. Las cadenas de carácteres, o strings,
representan uno de los tipos de variables que podemos usar en PHP. Otra forma de representar una cadena de carácteres
es entre `'`. La diferencia entre `"¡Hola $nombre!"` y `'¡Hola $nombre!'` es que en el primer caso la variable $nombre
se reemplaza por `Marcos` mientras que en el segundo no.

A parte de cadenas de texto las variables también pueden contener valores booleanos `$a = TRUE;`, números enteros
`$a = 38;`, números de punto flotante `$a = 1.5;`, arreglos y objetos.

Cuando una variable no esta definida decimos que contiene la constante `NULL`. `TRUE` y `NULL` son constantes
pre-definidas en el PHP. Por convenio las constantes se escriben en mayúsculas pero no es raro encontrarse las
constantes `true`, `false` y `null` en minúsculas.

Los arreglos y los objetos nos permiten definir estructuras de datos más complejas a partir de los tipos de variables
que ya hemos visto.

Por ejemplo, podemos definir un arreglo con los nombres de las cantantes más populares de la primera década del siglo
21.

    <?php
        $cantantes = array(
            "Britney Spears",
            "Christina Aguilera",
            "Beyoncé",
            "Shakira",
            "Jennifer López"
        );
    ?>

Para acceder a los elementos del arreglo usamos un índice. En el ejemplo anterior hemos creado el arreglo sin definir
ningún índice por lo que usa los índices por defecto. Por defecto el primer elemento del arreglo tiene el índice
0, el segundo 1 y asi sucesivamente. Para mostrar el primer elemento del arreglo por lo tanto usamos
`echo $cantantes[0];`.

Los arreglos del PHP no son como los arreglos de otros lenguajes de programación. En la práctica se parecen más a otro
tipo de estructura de datos que se llama mapa ordenado.

Esto es porque podemos poner prácticamente lo que queramos como índice.

    <?php
        $cantantes = array(
            "a" => "Britney Spears",
            "b" => "Christina Aguilera",
            "c" => "Beyoncé",
            "d" => "Shakira",
            "e" => "Jennifer López"
        );
    ?>

Y usar estos índices para mostrar el elemento del arreglo con índice `c` `echo $cantantes["c"]`.

Otro punto a tener en cuenta cuando utilizamos variables es su visibilidad.

Por ejemplo, el PHP nos permite incluir el contenido de un fichero en otro utilizando el operador include.

    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>PHP Test</title>
    </head>
    <body>
        <?php
            $nombre = "Marcos";
            include "documento_2.php";
        ?>
    </body>
    </html>

Donde documento_2.php contiene

    <?php echo "¡Hola $nombre!"; ?>

Como véis la variable `$nombre` definida en el primer documento es visible en el segundo.

Como veréis en el siguiente apartado, la visibilidad de una variable también depende de donde este definida.

## Funciones

Uno de los recursos que empleamos cuando queremos organizar y reutilizar el código que estamos desarrollando son las
funciones. Podemos definir, por ejemplo, una función que nos sume dos valores.

    function suma($a, $b)
    {
        return $a + $b;
    }

Para llamar a esta función usaremos su nombre `suma(10, 5);`. `$a` y `$b` son las variables de entrada de la función
y lo que hay a continuación del operador `return` es la salida de la función.
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
            <div class="span2">
                <ul class="nav nav-list">
                    <li><a href="/sketch/learn/index.php"><i class="icon-chevron-right"></i> Introducción</a></li>
                    <li><a href="/sketch/learn/html.php"><i class="icon-chevron-right"></i> HTML</a></li>
                    <li><a href="/sketch/learn/javascript.php"><i class="icon-chevron-right"></i> JavaScript</a></li>
                    <li class="active"><a href="/sketch/learn/php.php"><i class="icon-chevron-right"></i> PHP</a></li>
                    <li><a href="/sketch/learn/mysql.php"><i class="icon-chevron-right"></i> MySQL</a></li>
                    <li><a href="/sketch/learn/sketch.php"><i class="icon-chevron-right"></i> Sketch</a></li>
                </ul>
            </div>
            <div class="span10">
                <?php print $parser->transformMarkdown($content); ?>
            </div>
        </div>
    </div>
</body>
</html>