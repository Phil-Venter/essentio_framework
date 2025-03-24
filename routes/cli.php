<?php

use Essentio\Core\Argument;

/**
 * ============================================================================
 *
 * START DEV SERVER
 *
 * ============================================================================
 */

command('dev:serve', function (Argument $argv) {
    $flags = [
        'apc.enable_cli'                  => 1,
        'apc.enabled'                     => 1,
        'apc.shm_size'                    => '64M',
        'opcache.enable_cli'              => 1,
        'opcache.enable'                  => 1,
        'opcache.fast_shutdown'           => 1,
        'opcache.interned_strings_buffer' => 16,
        'opcache.jit_buffer_size'         => '128M',
        'opcache.jit'                     => 'tracing',
        'opcache.max_accelerated_files'   => 1000,
        'opcache.max_wasted_percentage'   => 10,
        'opcache.memory_consumption'      => 192,
        'opcache.revalidate_freq'         => 0,
        'opcache.validate_timestamps'     => 1,
    ];

    $compiled = implode(' ', array_map(
        fn($k, $v) => sprintf('-d%s=%s', $k, $v),
        array_keys($flags),
        array_values($flags)
    ));

    $port = $argv->options['port'] ?? $argv->flags['p'] ?? 8080;
    $host = $argv->options['host'] ?? $argv->flags['h'] ?? 'localhost';

    shell_exec(sprintf("php %s -S %s:%s -t public", $compiled, $host, $port));
});

/**
 * ============================================================================
 *
 * LOG VIEW HELPERS
 *
 * ============================================================================
 */

command('log:clear', function (Argument $argv) {
    foreach ($argv->positional as $file) {
        file_put_contents($file, '');
    }
});

command('log:watch', function (Argument $argv) {
    $descriptors = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']];
    $filenameLength = 0;

    foreach ($argv->positional as $file) {
        $escapedFile = escapeshellarg($file);
        $process = proc_open("tail -n 5 -f $escapedFile", $descriptors, $procPipes);

        if (is_resource($process)) {
            stream_set_blocking($procPipes[1], false);
            $processes[] = $process;
            $pipes[] = $procPipes[1];

            $filename = basename($file);
            $pipeToFile[(int)$procPipes[1]] = $filename;
            $filenameLength = max($filenameLength, strlen($filename));
        }
    }

    while (true) {
        $read = $pipes;

        if (stream_select($read, $write, $except, 0, 200000) > 0) {
            foreach ($read as $r) {
                $line = fgets($r);

                if ($line !== false) {
                    log_cli("[ %-{$filenameLength}s ] %s", $pipeToFile[(int)$r], trim($line));
                }
            }
        }
    }
});
