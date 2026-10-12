<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\Data\Section;

use Ibexa\AdminUi\Form\Data\Section\SectionCreateData;
use Ibexa\AdminUi\Form\Type\Section\SectionCreateType;
use Ibexa\AdminUi\Form\Type\Section\SectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @covers \Ibexa\AdminUi\Form\Type\Section\SectionCreateType
 * @covers \Ibexa\AdminUi\Form\Data\Section\SectionCreateData
 */
final class SectionCreateDataValidationTest extends AbstractSectionMutationDataValidationTestCase
{
    /**
     * @return array<string, FormTypeInterface<mixed>>
     */
    protected function getTypes(): array
    {
        return [
            SectionCreateType::class => new SectionCreateType(new SectionType()),
        ];
    }

    protected function getForm(): FormInterface
    {
        return $this->factory->create(SectionCreateType::class, new SectionCreateData());
    }
}
