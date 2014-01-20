<?php
$a = 1;
$b = 2;
function foo() {
    global $a;
    $a = 2;
    $b = 3;
}
foo();
echo $a + $b;