<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Command;

use Ibexa\Bundle\Core\Command\BackwardCompatibleCommand;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CompileAssetsCommand extends Command implements BackwardCompatibleCommand
{
    public const COMMAND_NAME = 'ibexa:encore:compile';

    public const COMMAND_DEFAULT_TIMEOUT = 300;

    protected static $defaultName = self::COMMAND_NAME;

    protected static $defaultDescription = 'Compiles all assets using WebPack Encore';

    private int $timeout;

    public function __construct(int $timeout = self::COMMAND_DEFAULT_TIMEOUT)
    {
        $this->timeout = $timeout;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setAliases([
                'ezplatform:encore:compile',
            ])
            ->addOption(
                'timeout',
                't',
                InputOption::VALUE_REQUIRED,
                'Timeout in seconds',
                $this->timeout
            )
            ->addOption(
                'config-name',
                'c',
                InputOption::VALUE_REQUIRED,
                'Config name passed to webpack encore',
                null
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $timeout = $input->getOption('timeout');

        if (!is_numeric($timeout)) {
            throw new InvalidArgumentException('Timeout value has to be an integer.');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timeout = (float)$input->getOption('timeout');
        $env = $input->getOption('env');
        $configName = $input->getOption('config-name');

        $output->writeln(sprintf('Compiling all <comment>%s</comment> assets.', $env));
        $output->writeln('');

        $encoreEnv = $env === 'prod' ? 'prod' : 'dev';
        $yarnEncoreCommand = "yarn encore {$encoreEnv}";

        if (!empty($configName)) {
            $yarnEncoreCommand .= " --config-name {$configName}";
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

        $process->run(static function ($type, $buffer) use ($output, $debugFormatter, $process) {
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

    /**
     * @return string[]
     */
    public function getDeprecatedAliases(): array
    {
        return ['ezplatform:encore:compile'];
    }
}
