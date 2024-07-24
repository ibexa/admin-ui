<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Form\Data\Role;

use Ibexa\AdminUi\Form\Data\Role\RoleAssignmentCreateData;
use Ibexa\AdminUi\Form\Type\Content\LocationType;
use Ibexa\AdminUi\Form\Type\Role\RoleAssignmentCreateType;
use Ibexa\AdminUi\Form\Type\Section\SectionChoiceType;
use Ibexa\AdminUi\Form\Type\User\UserCollectionType;
use Ibexa\AdminUi\Form\Type\User\UserGroupCollectionType;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Tests\AdminUi\Form\Data\AbstractFormDataValidationTestCase;
use Ibexa\Tests\AdminUi\Form\Data\FormErrorDataTestWrapper;
use Symfony\Component\Form\FormInterface;

/**
 * @covers \Ibexa\AdminUi\Form\Data\Role\RoleAssignmentCreateData
 * @covers \Ibexa\AdminUi\Form\Type\Role\RoleAssignmentCreateType
 */
final class RoleAssignmentCreateDataValidationTest extends AbstractFormDataValidationTestCase
{
    public static function getDataForTestFormSubmitValidation(): iterable
    {
        yield 'Empty data' => [
            [],
            [
                new FormErrorDataTestWrapper('validator.assign_users_or_groups', [], 'data'),
                new FormErrorDataTestWrapper(
                    'This value should not be null.',
                    ['{{ value }}' => 'null'],
                    'data.limitationType'
                ),
            ],
        ];

        yield 'Unsupported limitation type' => [
            [
                'users' => '14,10',
                'limitation_type' => 'foo',
            ],
            [
                new FormErrorDataTestWrapper(
                    'This value is not valid.',
                    ['{{ value }}' => 'foo'],
                    'children[limitation_type]'
                ),
            ],
        ];

        yield 'Sections not defined' => [
            [
                'users' => '13',
                'limitation_type' => RoleAssignmentCreateData::LIMITATION_TYPE_SECTION,
            ],
            [
                new FormErrorDataTestWrapper(
                    'validator.define_subtree_or_section_limitation',
                    ['{{ value }}' => 'array'],
                    'data.sections'
                ),
            ],
        ];

        yield 'Subtree Location not defined' => [
            [
                'users' => '14,10',
                'limitation_type' => RoleAssignmentCreateData::LIMITATION_TYPE_LOCATION,
            ],
            [
                new FormErrorDataTestWrapper(
                    'validator.define_subtree_or_section_limitation',
                    ['{{ value }}' => 'array'],
                    'data.locations'
                ),
            ],
        ];

        yield 'valid data - sections' => [
            [
                'users' => '10',
                'limitation_type' => RoleAssignmentCreateData::LIMITATION_TYPE_SECTION,
                'sections' => ['1'],
            ],
            [],
        ];

        yield 'valid data - locations' => [
            [
                'users' => '10',
                'limitation_type' => RoleAssignmentCreateData::LIMITATION_TYPE_LOCATION,
                'locations' => '1,2',
            ],
            [],
        ];
    }

    /**
     * @return array<string, \Symfony\Component\Form\FormTypeInterface>
     */
    protected function getTypes(): array
    {
        $sectionServiceMock = $this->createMock(SectionService::class);
        $sectionServiceMock->method('loadSections')->willReturn(
            [
                new Section(['id' => 1, 'identifier' => 'standard', 'name' => 'Standard']),
            ]
        );

        $locationServiceMock = $this->createMock(LocationService::class);
        $locationServiceMock->method('loadLocation')->willReturn($this->createMock(Location::class));

        return [
            UserGroupCollectionType::class => new UserGroupCollectionType($this->createMock(UserService::class)),
            UserCollectionType::class => new UserCollectionType($this->createMock(UserService::class)),
            SectionChoiceType::class => new SectionChoiceType($sectionServiceMock),
            LocationType::class => new LocationType($locationServiceMock),
        ];
    }

    protected function getForm(): FormInterface
    {
        return $this->factory->create(RoleAssignmentCreateType::class, new RoleAssignmentCreateData());
    }
}
