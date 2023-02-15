<?php

declare(strict_types=1);

namespace App\Contracts;

interface SessionInterface
{
    public function start(): void;

    public function save(): void;

    public function isActive(): bool;

    public function get(string $sessionName, mixed $default = null): mixed;

    public function put(string $sessionName, mixed $getId): void;

    public function regenerate(): bool;

    public function forget(string $sessionName): void;
}
