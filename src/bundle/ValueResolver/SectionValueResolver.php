<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;

/**
 * @phpstan-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\Content\Section>
 */
final class SectionValueResolver extends AbstractValueResolver
{
    private const string ATTRIBUTE_SECTION_ID = 'sectionId';

    public function __construct(
        private readonly SectionService $sectionService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [self::ATTRIBUTE_SECTION_ID];
    }

    protected function getClass(): string
    {
        return Section::class;
    }

    protected function validateValue(string $value): bool
    {
        return is_numeric($value);
    }

    protected function load(array $key): object
    {
        return $this->sectionService->loadSection(
            (int)$key[self::ATTRIBUTE_SECTION_ID]
        );
    }
}
