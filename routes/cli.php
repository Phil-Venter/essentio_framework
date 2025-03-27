<?php

use Essentio\Core\Argument;
use Essentio\Core\Environment;

/**
 * ============================================================================
 *
 * LOCAL DEV SERVER
 *
 * ============================================================================
 */

command("dev:serve", function (Argument $argv) {
    $flags = [
        "opcache.enable_cli" => 1,
        "opcache.enable" => 1,
        "opcache.fast_shutdown" => 1,
        "opcache.interned_strings_buffer" => 16,
        "opcache.jit_buffer_size" => "128M",
        "opcache.jit" => "tracing",
        "opcache.max_accelerated_files" => 1000,
        "opcache.max_wasted_percentage" => 10,
        "opcache.memory_consumption" => 192,
        "opcache.revalidate_freq" => 0,
        "opcache.validate_timestamps" => 1,
    ];

    $compiled = implode(
        " ",
        array_map(fn($k, $v) => sprintf("-d%s=%s", $k, $v), array_keys($flags), array_values($flags))
    );

    $port = $argv->options["port"] ?? ($argv->flags["p"] ?? 8080);
    $host = $argv->options["host"] ?? ($argv->flags["h"] ?? "localhost");

    shell_exec(sprintf("php %s -S %s:%s -t public", $compiled, $host, $port));
});

/**
 * ============================================================================
 *
 * LOG VIEW HELPERS
 *
 * ============================================================================
 */

function getLogFiles(Argument $argv): array
{
    $files = $argv->positional;

    if (empty($files)) {
        foreach (app(Environment::class)->data as $key => $value) {
            if (str_contains($key, "_LOG_FILE")) {
                $files[] = $value;
            }
        }
    }

    if (empty($files)) {
        $files[] = Application::fromBase("app.log");
    }

    return $files;
}

command("log:clear", function (Argument $argv) {
    foreach (getLogFiles($argv) as $file) {
        file_put_contents($file, "");
    }
});

command("log:watch", function (Argument $argv) {
    set_time_limit(0);

    $positions = [];
    $len = 0;

    foreach (getLogFiles($argv) as $file) {
        if (!file_exists($file)) {
            touch($file);
        }

        $handle = fopen($file, "r");

        if (!$handle) {
            continue;
        }

        $filename = basename($file);
        $len = max($len, strlen($filename));
        fseek($handle, 0, SEEK_END);

        $positions[$file] = [
            "name" => $filename,
            "handle" => $handle,
            "position" => ftell($handle),
        ];
    }

    while (true) {
        foreach ($positions as $file => &$info) {
            clearstatcache(true, $file);
            $currentSize = filesize($file);

            // File was truncated
            if ($currentSize < $info["position"]) {
                fseek($info["handle"], 0, SEEK_SET);
                $info["position"] = 0;
            }
            // New data in file
            elseif ($currentSize > $info["position"]) {
                fseek($info["handle"], $info["position"]);

                while (($line = fgets($info["handle"])) !== false) {
                    log_cli("[ %{$len}s ] %s", $info["name"], rtrim($line, "\n"));
                }

                $info["position"] = ftell($info["handle"]);
            }
        }

        usleep(100_000);
    }
});
