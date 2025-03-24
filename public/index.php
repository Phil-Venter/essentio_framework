<?php

require __DIR__ . '/../vendor/autoload.php';

use Essentio\Core\Application;

Application::http(__DIR__ . '/..');

require_once Application::fromBase('bootstrap.php');
require_once Application::fromBase('routes/web.php');

Application::run();
