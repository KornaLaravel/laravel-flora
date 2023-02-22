<?php

namespace Qruto\Power\Contracts;

interface Chain
{
    public function set(string $environment, callable $callback);

    public function get(string $environment);
}
