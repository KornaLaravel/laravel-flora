<?php

use Qruto\Power\Discovers\Instruction;
use Qruto\Power\Enums\Environment;
use Qruto\Power\Enums\PowerType;

it('saves install instruction for one environment', function () {
    $callback = fn () => null;

    $instruction = new Instruction(
        install: [
            'production' => $callback,
        ]
    );

    $this->assertEquals($callback, $instruction->get(PowerType::Install, Environment::Production));
});

it('saves install instruction for two environments', function () {
    $callback = fn () => null;
    $callback2 = fn () => null;

    $instruction = new Instruction(
        install: [
            'production' => $callback,
            'local' => $callback2,
        ]
    );

    $this->assertEquals($callback, $instruction->get(PowerType::Install));
    $this->assertEquals($callback2, $instruction->get(PowerType::Install, Environment::Local));
});

it('saves install and update instruction for two environments', function () {
    $installCallback = fn () => null;
    $installCallback2 = fn () => null;
    $updateCallback = fn () => null;
    $updateCallback2 = fn () => null;

    $instruction = new Instruction(
        install: [
            'production' => $installCallback,
            'local' => $installCallback2,
        ],
        update: [
            'production' => $updateCallback,
            'local' => $updateCallback2,
        ]
    );

    $this->assertEquals($installCallback, $instruction->get(PowerType::Install, Environment::Production));
    $this->assertEquals($installCallback2, $instruction->get(PowerType::Install, Environment::Local));

    $this->assertEquals($updateCallback, $instruction->get(PowerType::Update, Environment::Production));
    $this->assertEquals($updateCallback2, $instruction->get(PowerType::Update, Environment::Local));
});

it('returns empty function if no instruction found', function () {
    $instruction = new Instruction();

    $this->assertEquals(null, $instruction->get(PowerType::Install)());
});

it('returns empty function if no instruction found for the environment', function () {
    $instruction = new Instruction(
        install: [
            'production' => fn () => null,
        ]
    );

    $this->assertEquals(null, $instruction->get(PowerType::Install, Environment::Local)());
});

it('returns one instruction for all environments', function () {
    $callback = fn () => null;

    $instruction = new Instruction(
        install: $callback
    );

    $this->assertEquals($callback, $instruction->get(PowerType::Install));
});
