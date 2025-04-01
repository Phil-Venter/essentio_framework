<?php

require __DIR__ . "/../vendor/autoload.php";

use Essentio\Core\Application;
use Essentio\Framework\SimpleApcuSessionHandler;

// session_set_save_handler(new SimpleApcuSessionHandler(), true);

Application::http(__DIR__ . "/..");

require_once Application::fromBase("bootstrap.php");
require_once Application::fromBase("routes/web.php");

Application::run();
