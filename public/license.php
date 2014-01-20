<?php
require_once '../vendor/autoload.php';

use dflydev\markdown\MarkdownParser;

$parser = new MarkdownParser();

$content = <<<'EOD'
# Licencia MIT

<pre><code>Copyright (c) 2007 Marcos Cooper

Permission is hereby granted, free of charge, to any
person obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the
Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the
Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice
shall be included in all copies or substantial portions of
the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.</code></pre>
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
                    <li><a href="/sketch/learn/index.php">Aprende</a></li>
                </ul>
                <a href="http://releasepad.com" class="by-releasepad"><span class="by">por</span><span class="release">Release</span><span class="pad">Pad</span></a>
            </div>
        </div>
        <div class="body row">
            <div class="span12">
                <?php print $parser->transformMarkdown($content); ?>
            </div>
        </div>
    </div>
</body>
</html>