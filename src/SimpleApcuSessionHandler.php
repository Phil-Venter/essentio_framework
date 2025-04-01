<?php

namespace Essentio\Framework;

use RuntimeException;
use SessionHandlerInterface;

class SimpleApcuSessionHandler implements SessionHandlerInterface
{
    private string $prefix;
    private int $ttl;

    public function __construct(string $prefix = "sess_", int $ttl = 3600)
    {
        if (!function_exists("apcu_enabled") || !apcu_enabled()) {
            throw new RuntimeException("APCu is not enabled.");
        }

        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($sessionId): string
    {
        $key = $this->prefix . $sessionId;
        $data = apcu_fetch($key);
        return is_string($data) ? $data : "";
    }

    public function write($sessionId, $data): bool
    {
        $key = $this->prefix . $sessionId;
        return apcu_store($key, $data, $this->ttl);
    }

    public function destroy($sessionId): bool
    {
        $key = $this->prefix . $sessionId;
        return apcu_delete($key);
    }

    public function gc($maxLifetime): int|false
    {
        return 0;
    }
}
