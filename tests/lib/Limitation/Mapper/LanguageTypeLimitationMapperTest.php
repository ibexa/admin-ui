<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\LanguageLimitationMapper;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\LanguageLimitation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LanguageTypeLimitationMapperTest extends TestCase
{
    private LanguageService&MockObject $languageService;

    private LoggerInterface&MockObject $logger;

    private LanguageLimitationMapper $mapper;

    protected function setUp(): void
    {
        $this->languageService = $this->createMock(LanguageService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->mapper = new LanguageLimitationMapper($this->languageService);
        $this->mapper->setLogger($this->logger);
    }

    public function testMapLimitationValue(): void
    {
        $values = ['en_GB', 'en_US', 'pl_PL'];

        $expected = [
            $this->createMock(Language::class),
            $this->createMock(Language::class),
            $this->createMock(Language::class),
        ];

        $languagesByCode = [];
        foreach ($values as $i => $value) {
            $languagesByCode[$value] = $expected[$i];
        }

        $this->languageService
            ->expects(self::exactly(count($values)))
            ->method('loadLanguage')
            ->willReturnCallback(static function (string $languageCode) use ($languagesByCode): Language {
                self::assertArrayHasKey($languageCode, $languagesByCode);

                return $languagesByCode[$languageCode];
            });

        $result = $this->mapper->mapLimitationValue(new LanguageLimitation([
            'limitationValues' => $values,
        ]));

        self::assertEquals($expected, $result);
    }

    public function testMapLimitationValueWithNotExistingContentType(): void
    {
        $values = ['foo'];

        $this->languageService
            ->expects(self::once())
            ->method('loadLanguage')
            ->with($values[0])
            ->willThrowException($this->createMock(NotFoundException::class));

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with('Could not map the Limitation value: could not find a language with code foo');

        $actual = $this->mapper->mapLimitationValue(new LanguageLimitation([
            'limitationValues' => $values,
        ]));

        self::assertEmpty($actual);
    }
}
