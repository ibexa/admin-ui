<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\LanguageLimitation;
use Ibexa\AdminUi\Limitation\Mapper\LanguageLimitationMapper;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LanguageTypeLimitationMapperTest extends TestCase
{
    /** @var \Ibexa\Contracts\Core\Repository\LanguageService|\PHPUnit\Framework\MockObject\MockObject */
    private $languageService;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var \Ibexa\AdminUi\Limitation\Mapper\LanguageLimitationMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->languageService = $this->createMock(LanguageService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->mapper = new LanguageLimitationMapper($this->languageService);
        $this->mapper->setLogger($this->logger);
    }

    public function testMapLimitationValue()
    {
        $values = ['en_GB', 'en_US', 'pl_PL'];

        $expected = [
            $this->createMock(Language::class),
            $this->createMock(Language::class),
            $this->createMock(Language::class),
        ];

        foreach ($values as $i => $value) {
            $this->languageService
                ->expects($this->at($i))
                ->method('loadLanguage')
                ->with($value)
                ->willReturn($expected[$i]);
        }

        $result = $this->mapper->mapLimitationValue(new LanguageLimitation([
            'limitationValues' => $values,
        ]));

        $this->assertEquals($expected, $result);
    }

    public function testMapLimitationValueWithNotExistingContentType()
    {
        $values = ['foo'];

        $this->languageService
            ->expects($this->once())
            ->method('loadLanguage')
            ->with($values[0])
            ->willThrowException($this->createMock(NotFoundException::class));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Could not map the Limitation value: could not find a language with code foo');

        $actual = $this->mapper->mapLimitationValue(new LanguageLimitation([
            'limitationValues' => $values,
        ]));

        $this->assertEmpty($actual);
    }
}

class_alias(LanguageTypeLimitationMapperTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Limitation\Mapper\LanguageTypeLimitationMapperTest');
