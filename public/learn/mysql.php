<?php
require_once '../../vendor/autoload.php';

use dflydev\markdown\MarkdownParser;

$parser = new MarkdownParser();

$content = <<<'EOD'

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
                    <li><a href="/sketch/learn/php.php"><i class="icon-chevron-right"></i> PHP</a></li>
                    <li class="active"><a href="/sketch/learn/mysql.php"><i class="icon-chevron-right"></i> MySQL</a></li>
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