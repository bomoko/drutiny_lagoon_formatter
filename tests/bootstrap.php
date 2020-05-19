<?php

/**
 * @file
 * Custom bootstrap file to get Annotations to work in PHPunit.
 *
 * @see https://github.com/Codeception/Codeception/issues/3537#issuecomment-254868365
 */

use Doctrine\Common\Annotations\AnnotationRegistry;

const DRUTINY_LIB = '.';

error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);

$loader = require __DIR__ . '/../vendor/autoload.php';
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
return $loader;