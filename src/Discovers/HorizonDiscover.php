<?php

namespace Qruto\Power\Discovers;

use Illuminate\Container\Container;
use Qruto\Power\Actions\Artisan;
use Qruto\Power\Run;

class HorizonDiscover implements PackageDiscover
{
    public function exists(): bool
    {
        return Container::getInstance()->has('Laravel\Horizon\Console\WorkCommand');
    }

    public function instruction(): Instruction
    {
        return new Instruction(
            update: [
                'production' => function (Run $run) {
                    $run->internal->replace(
                        fn (Artisan $action) => $action->name() === 'queue:restart',
                        fn (Run $run) => $run->command('horizon:terminate'),
                    );
                },
            ],
        );
    }
}
