<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Command;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: self::COMMAND_NAME,
    description: 'Compiles all assets using Webpack Encore'
)]
class CompileAssetsCommand extends Command
{
    public const COMMAND_NAME = 'ibexa:encore:compile';
    public const COMMAND_DEFAULT_TIMEOUT = 300;

    private int $timeout;

    public function __construct(int $timeout = self::COMMAND_DEFAULT_TIMEOUT)
    {
        $this->timeout = $timeout;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'watch',
                'w',
                InputOption::VALUE_NONE,
                'Watch mode rebuilds on file change'
            )
            ->addOption(
                'timeout',
                't',
                InputOption::VALUE_REQUIRED,
                "Timeout in seconds (default timeout is {$this->timeout}s when this option isn't used and not in watch mode)",
                null
            )
            ->addOption(
                'config-name',
                'c',
                InputOption::VALUE_REQUIRED,
                'Config name passed to webpack encore',
                null
            )
            ->addOption(
                'frontend-configs-name',
                'fcn',
                InputOption::VALUE_REQUIRED,
                'Frontend configs name passed to webpack encore',
                null
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $watch = $input->getOption('watch');
        $timeout = $input->getOption('timeout');

        if (null !== $timeout) {
            if ($watch) {
                throw new InvalidArgumentException('Watch mode can\'t be used with a timeout.');
            }
            if (!is_numeric($timeout)) {
                throw new InvalidArgumentException('Timeout value has to be an integer.');
            }
        }
    }

    protected function getFrontendConfigPath(string $configName): string
    {
        return "./node_modules/@ibexa/frontend-config/ibexa.webpack.{$configName}.configs.js";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $watch = $input->getOption('watch');
        $timeout = $watch ? null : (float)($input->getOption('timeout')??$this->timeout);
        $env = $input->getOption('env');
        $configName = $input->getOption('config-name');
        $frontendConfigsName = $input->getOption('frontend-configs-name');

        $output->writeln(sprintf('Compiling all <comment>%s</comment> assets.', $env));
        $output->writeln('');

        $encoreEnv = $env === 'prod' ? 'prod' : 'dev';
        $yarnBaseEncoreCommand = "yarn encore {$encoreEnv}";
        $yarnEncoreCommand = $yarnBaseEncoreCommand;

        if ($watch) {
            $yarnEncoreCommand = "{$yarnBaseEncoreCommand} --watch";
        }

        if (!empty($configName)) {
            $yarnEncoreCommand = "{$yarnBaseEncoreCommand} --config-name {$configName}";
        }

        if (!empty($frontendConfigsName)) {
            $frontendConfigsNameArr = explode(',', $frontendConfigsName);
            $yarnEncoreCommand = implode(' && ', array_map(
                fn (string $configName) => "{$yarnBaseEncoreCommand} --config {$this->getFrontendConfigPath($configName)}",
                $frontendConfigsNameArr
            ));
        }

        $debugFormatter = $this->getHelper('debug_formatter');

        $process = Process::fromShellCommandline(
            $yarnEncoreCommand,
            null,
            null,
            null,
            $timeout
        );

        $output->writeln($debugFormatter->start(
            spl_object_hash($process),
            sprintf('Evaluating command <comment>%s</comment>', $yarnEncoreCommand)
        ));

        $process->run(static function ($type, $buffer) use ($output, $debugFormatter, $process): void {
            $output->write(
                $debugFormatter->progress(
                    spl_object_hash($process),
                    $buffer,
                    Process::ERR === $type
                )
            );
        });

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf("An error occurred when executing the \"%s\" command:\n\n%s\n\n%s", $yarnEncoreCommand, $process->getOutput(), $process->getErrorOutput()));
        }

        $output->writeln(
            $debugFormatter->stop(
                spl_object_hash($process),
                'Command finished',
                $process->isSuccessful()
            )
        );

        return $process->getExitCode();
    }
}
