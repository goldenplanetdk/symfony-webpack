#!/usr/bin/env php
<?php

require_once __DIR__ . './bootstrap.php.cache';
require_once __DIR__ . './TestKernel.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

$input = new ArgvInput();
$env = $input->getParameterOption(['--env', '-e'], 'test');
$config = $input->getParameterOption(['--conf', '-c'], 'customized');

$kernel = new TestKernel($env, false);
$kernel->setConfigFile($config);
$kernel->boot();

$application = new Application($kernel);
$application->run($input);
