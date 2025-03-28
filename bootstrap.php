<?php

use Essentio\Core\Application;
use Essentio\Core\Container;
use Essentio\Core\Environment;
use Essentio\Database\Query;

app(Environment::class)->load(Application::fromBase(".env"));

/**
 * ============================================================================
 *
 * BINDINGS
 *
 * ============================================================================
 */

bind(PDO::class, function (): PDO {
    $path = env("DB_DATABASE", "storage/database.sqlite");
    $realpath = Application::fromBase($path);
    return new PDO(sprintf("sqlite:%s", $realpath));
})->once = true;

bind(Query::class, function (Container $c): Query {
    $pdo = $c->get(PDO::class);
    return new Query($pdo);
});
