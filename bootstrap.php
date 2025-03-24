<?php

use Essentio\Core\Application;
use Essentio\Core\Container;
use Essentio\Core\Environment;
use Essentio\Database\Query;

app(Environment::class)->load(Application::fromBase('.env'));

/**
 * ============================================================================
 *
 * BINDINGS
 *
 * ============================================================================
 */

bind(PDO::class, fn() => new PDO(sprintf('sqlite:%s', env('DB_DATABASE', 'database.sqlite'))))->once = true;
bind(Query::class, fn(Container $c) => new Query($c->get(PDO::class)));
