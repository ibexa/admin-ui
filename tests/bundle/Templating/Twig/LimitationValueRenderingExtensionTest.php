<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Twig;

use Exception;
use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\AdminUi\Limitation\LimitationValueMapperRegistryInterface;
use Ibexa\AdminUi\Limitation\Templating\LimitationBlockRenderer;
use Ibexa\Bundle\AdminUi\Templating\Twig\LimitationValueRenderingExtension;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Tests\Core\MVC\Symfony\Templating\Twig\Extension\FileSystemTwigIntegrationTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

class LimitationValueRenderingExtensionTest extends FileSystemTwigIntegrationTestCase
{
    public function getExtensions(Environment $twig = null): array
    {
        $limitationBlockRenderer = new LimitationBlockRenderer(
            $this->createLimitationValueMapperRegistryMock(),
            $twig,
            $this->createConfigResolverMock()
        );

        return [
            new LimitationValueRenderingExtension($limitationBlockRenderer),
        ];
    }

    private function createLimitationValueMapperRegistryMock(): MockObject
    {
        $mapperMock = $this->createMock(LimitationValueMapperInterface::class);
        $mapperMock
            ->expects(self::atLeastOnce())
            ->method('mapLimitationValue')
            ->willReturnCallback(static function (Limitation $limitation) {
                return $limitation->limitationValues;
            });

        $registryMock = $this->createMock(LimitationValueMapperRegistryInterface::class);
        $registryMock
            ->expects(self::atLeastOnce())
            ->method('getMapper')
            ->willReturn($mapperMock);

        return $registryMock;
    }

    public function getLimitation($identifier, array $values): LimitationMock
    {
        return new LimitationMock($identifier, $values);
    }

    /**
     * @see \Ibexa\Tests\Core\MVC\Symfony\Templating\Twig\Extension\FileSystemTwigIntegrationTestCase::doIntegrationTest
     *
     * @throws \Twig\Error\Error
     */
    protected function doIntegrationTest($file, $message, $condition, $templates, $exception, $outputs, $deprecation = ''): void
    {
        if (!$outputs) {
            self::markTestSkipped('no legacy tests to run');
        }

        if ($condition) {
            eval('$ret = ' . $condition . ';');
            if (!$ret) {
                self::markTestSkipped($condition);
            }
        }

        $loader = new ChainLoader([
            new ArrayLoader($templates),
            new FilesystemLoader(self::getFixturesDirectory()),
        ]);

        foreach ($outputs as $i => $match) {
            $config = array_merge([
                'cache' => false,
                'strict_variables' => true,
            ], $match[2] ? eval($match[2] . ';') : []);

            $twig = new Environment($loader, $config);
            $twig->addGlobal('global', 'global');
            // (!) Twig\Environment is dependency of LimitationBlockRenderer
            foreach ($this->getExtensions($twig) as $extension) {
                $twig->addExtension($extension);
            }

            foreach ($this->getTwigFilters() as $filter) {
                $twig->addFilter($filter);
            }

            foreach ($this->getTwigTests() as $test) {
                $twig->addTest($test);
            }

            foreach ($this->getTwigFunctions() as $function) {
                $twig->addFunction($function);
            }

            try {
                $template = $twig->load('index.twig');
            } catch (Exception $e) {
                if (false !== $exception) {
                    $message = $e->getMessage();
                    self::assertSame(trim($exception), trim(sprintf('%s: %s', \get_class($e), $message)));
                    $last = substr($message, \strlen($message) - 1);
                    self::assertTrue('.' === $last || '?' === $last, $message, 'Exception message must end with a dot or a question mark.');

                    return;
                }

                throw $this->buildTwigErrorFromException($e, $file);
            }

            try {
                $output = trim($template->render(eval($match[1] . ';')), "\n ");
            } catch (Exception $e) {
                if (false !== $exception) {
                    self::assertSame(trim($exception), trim(sprintf('%s: %s', \get_class($e), $e->getMessage())));

                    return;
                }

                $e = $this->buildTwigErrorFromException($e, $file);

                $output = trim(sprintf('%s: %s', \get_class($e), $e->getMessage()));
            }

            if (false !== $exception) {
                [$class] = explode(':', $exception);
                self::assertThat(null, new \PHPUnit\Framework\Constraint\Exception($class));
            }

            $expected = trim($match[3], "\n ");

            if ($expected !== $output) {
                printf("Compiled templates that failed on case %d:\n", $i + 1);

                foreach (array_keys($templates) as $name) {
                    echo "Template: $name\n";
                    echo $twig->compile($twig->parse($twig->tokenize($twig->getLoader()->getSourceContext($name))));
                }
            }
            self::assertEquals($expected, $output, $message . ' (in ' . $file . ')');
        }
    }

    protected static function getFixturesDirectory(): string
    {
        return __DIR__ . '/_fixtures/render_limitation_value/';
    }

    private function createConfigResolverMock(): ConfigResolverInterface
    {
        $mock = $this->createMock(ConfigResolverInterface::class);
        $mock
            ->method('getParameter')
            ->willReturn([
                [
                    'template' => 'templates/limitation_value_1.html.twig',
                    'priority' => 10,
                ],
                [
                    'template' => 'templates/limitation_value_2.html.twig',
                    'priority' => 0,
                ],
                [
                    'template' => 'templates/limitation_value_3.html.twig',
                    'priority' => 20,
                ],
            ])
        ;

        return $mock;
    }
}
