<?php

namespace Qruto\Initializer\Console\Commands;

use ErrorException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Qruto\Initializer\Contracts\Chain;
use Qruto\Initializer\Contracts\ChainVault;
use Qruto\Initializer\Contracts\Runner;
use Qruto\Initializer\Discovers\HorizonDiscover;
use Qruto\Initializer\Discovers\IdeHelperDiscover;
use Qruto\Initializer\Discovers\TelescopeDiscover;
use Qruto\Initializer\Enums\Environment;
use Qruto\Initializer\Enums\InitializerType;
use Qruto\Initializer\UndefinedInstructionException;

abstract class AbstractInitializeCommand extends Command
{
    protected InitializerType $type;

    public function handle(Container $container, Repository $config, ChainVault $vault, ExceptionHandler $exceptionHandler)
    {
        $autoInstruction = true;

        if ($customBuildExists = file_exists($build = base_path('routes/build.php'))) {
            $autoInstruction = false;

            require $build;
        } else {
            // TODO: base path from package config
            require __DIR__ . '/../../build.php';
        }

        $initializer = $this->getInitializer($vault);

        // TODO: respect env option
        $env = $config->get($config->get('initializer.env_config_key'));

        $runner = $container->make(Runner::class, [
            'application' => $this->getApplication(),
            'output' => $this->getOutput(),
        ]);

        $this->trap([SIGTERM, SIGINT], function () use ($runner) {
            if ($this->components->confirm('Installation stop confirm')) {
                $this->components->warn(ucfirst($this->title()) . ' aborted without completion');
                exit;
            }

            $runner->runLatestAction();
        });

        try {
            $container->call($initializer->get($env), ['run' => $runner]);
        } catch (UndefinedInstructionException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($autoInstruction) {
            $this->packageDiscovers($this->type, $env, $runner);
        }

        $this->components->alert('Application ' . $this->title());

        $runner->start();

        // TODO: root options

        $this->output->newLine();

        if ($runner->doneWithErrors()) {
            $exceptions = $runner->exceptions();

            $this->components->error($this->title(). ' occur errors');

            if (! empty($exceptions) && $this->components->confirm('Show errors?')) {
                foreach ($exceptions as $exception) {
                    $this->components->twoColumnDetail($exception['title']);

                    $exceptionHandler->renderForConsole($this->getOutput(), $exception['e']);
                    $exceptionHandler->report($exception['e']);

                    $this->output->newLine();
                    $this->output->newLine();
                }
            }

            $this->line('<fg=red>You could run command with <fg=cyan>-v</> flag to see more details</>');
        }

        $this->components->info($this->title().' done!');

        return self::SUCCESS;
    }

    protected function packageDiscovers(InitializerType $type, string $environment, Runner $runner)
    {
        // TODO: build assets in production config value

        $discovers = [
            new HorizonDiscover(),
            new TelescopeDiscover(),
            new IdeHelperDiscover(),
        ];

        foreach ($discovers as $discover) {
            if ($discover->exists()) {
                $discover->instruction()
                    ->get($type, Environment::tryFrom($environment))($runner);
            }
        }
    }

    /**
     * Returns initializer instance for current command.
     *
     * @return object
     */
    abstract protected function getInitializer(ChainVault $vault): Chain;

    abstract protected function title(): string;
}
