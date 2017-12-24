<?php

$possibleAutoloaders = [
    __DIR__ . '/../../autoload.php',
    __DIR__ . '/vendor/autoload.php',
];

foreach ($possibleAutoloaders as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

return \DI\ContainerBuilder::buildDevContainer();
