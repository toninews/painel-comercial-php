<?php
date_default_timezone_set('America/Sao_Paulo');

if (version_compare(PHP_VERSION, '8.1.0') === -1)
{
    die('A versao minima do PHP para rodar esta aplicacao e: 8.1.0');
}

require_once 'Lib/Nexa/Core/ClassLoader.php';
$frameworkLoader = new Nexa\Core\ClassLoader;
$frameworkLoader->addNamespace('Nexa', 'Lib/Nexa');
$frameworkLoader->register();

require_once 'Lib/Nexa/Core/AppLoader.php';
$appLoader = new Nexa\Core\AppLoader;
$appLoader->addDirectory('App/Control');
$appLoader->addDirectory('App/Model');
$appLoader->addDirectory('App/Services');
$appLoader->register();

require 'vendor/autoload.php';

Nexa\Core\Env::load(__DIR__ . '/.env');
