<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\Data\Section;

use Ibexa\AdminUi\Form\Data\Section\SectionUpdateData;
use Ibexa\AdminUi\Form\Type\Section\SectionType;
use Ibexa\AdminUi\Form\Type\Section\SectionUpdateType;
use Symfony\Component\Form\FormInterface;

/**
 * @covers \Ibexa\AdminUi\Form\Type\Section\SectionUpdateType
 * @covers \Ibexa\AdminUi\Form\Data\Section\SectionUpdateData
 */
final class SectionUpdateDataValidationTest extends AbstractSectionMutationDataValidationTestCase
{
    /**
     * @return array<string, \Symfony\Component\Form\FormTypeInterface>
     */
    protected function getTypes(): array
    {
        return [
            SectionUpdateType::class => new SectionUpdateType(new SectionType()),
        ];
    }

    protected function getForm(): FormInterface
    {
        return $this->factory->create(SectionUpdateType::class, new SectionUpdateData());
    }
}
