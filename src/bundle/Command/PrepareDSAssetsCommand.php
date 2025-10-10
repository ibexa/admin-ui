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
    description: 'Prepares Design System assets for development environment.',
)]
class PrepareDSAssetsCommand extends Command
{
    public const string COMMAND_NAME = 'ibexa:ds-assets:prepare';
    public const int COMMAND_DEFAULT_TIMEOUT = 300;

    public function __construct(
        private readonly int $timeout = self::COMMAND_DEFAULT_TIMEOUT
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'timeout',
                't',
                InputOption::VALUE_REQUIRED,
                'Timeout in seconds',
                $this->timeout
            )
            ->addOption(
                'ds-version',
                'dsv',
                InputOption::VALUE_OPTIONAL,
                'Version of Design System',
                'main'
            )
            ->addOption(
                'ds-directory',
                'dsd',
                InputOption::VALUE_OPTIONAL,
                'Directory where custom version of Design System is located',
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
        $version = $input->getOption('ds-version');
        $directory = $input->getOption('ds-directory');

        if ($env === 'prod') {
            $output->writeln(sprintf('Production version detected. Omitting...'));
            $output->writeln('');

            return 0;
        }

        $output->writeln(sprintf('Linking dev (<comment>%s</comment>) version of Design System to assets.', $version));
        $output->writeln('');

        $yarnCommand = '';

        if (empty($directory)) {
            $directory = './var/design-system';
            $yarnCommand = "rm -rf {$directory} && git clone https://github.com/ibexa/design-system --branch={$version} {$directory} && ";
        }

        $filepath = rtrim($directory, '/') . '/bin/prepare_ds_symlinks.mjs';
        $yarnCommand .= "node {$filepath}";

        /** @var \Symfony\Component\Console\Helper\DebugFormatterHelper $debugFormatter */
        $debugFormatter = $this->getHelper('debug_formatter');

        $process = Process::fromShellCommandline(
            $yarnCommand,
            null,
            null,
            null,
            $timeout
        );

        $output->writeln($debugFormatter->start(
            spl_object_hash($process),
            sprintf('Evaluating command <comment>%s</comment>', $yarnCommand)
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
            throw new RuntimeException(sprintf("An error occurred when executing the \"%s\" command:\n\n%s\n\n%s", $yarnCommand, $process->getOutput(), $process->getErrorOutput()));
        }

        $output->writeln(
            $debugFormatter->stop(
                spl_object_hash($process),
                'Command finished',
                $process->isSuccessful()
            )
        );

        return $process->getExitCode() ?? Command::FAILURE;
    }
}
