<?php

require_once dirname(__DIR__).'/vendor/autoload.php';


$name = 'App\Resource\GoldiUA';
$name = strtolower(preg_replace(['/.+\\\(\w+)$/', '/([a-z])([A-Z])/'], ['\1', '\1_\2'], $name));

echo $name, PHP_EOL;

