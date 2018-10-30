<?php

use Silly\Application;

/** @var \DI\Container $container */
$container = require __DIR__ . '/../bootstrap.php';

/** @var \Silly\Application $application */
$application = new Application();
$application->useContainer($container, true, true);

$application->command(
    'filter-checkstyle [-b|--base-path=] diff infile [outfile]',
    \Kamazee\PrFilter\Command\FilterCheckstyle::class
)->descriptions('Filter checkstyle.xml according to a diff', [
    'diff' => 'Path to diff file, e.g. ./build/pull_request.diff',
    'infile' => 'Path to checkstyle.xml to be filtered), ' .
        'e.g. ./build/checkstyle.xml',
    'outfile' => 'Path to filtered checkstyle.xml, e.g. ./build/checkstyle-filtered.xml.',
    '--base-path' => 'If set, will be removed from paths in checkstyle.xml, ' .
        'e.g. /var/www/src/MyClass.php with -b /var/www will be treated as src/MyClass.php'
]);

$application->run();
