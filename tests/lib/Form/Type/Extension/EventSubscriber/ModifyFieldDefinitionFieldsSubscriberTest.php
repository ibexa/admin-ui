<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\Type\Extension\EventSubscriber;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\AdminUi\Form\Type\Extension\EventSubscriber\ModifyFieldDefinitionFieldsSubscriber;
use Ibexa\AdminUi\Form\Type\FieldDefinition\FieldDefinitionType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

/**
 * @covers \Ibexa\AdminUi\Form\Type\Extension\EventSubscriber\ModifyFieldDefinitionFieldsSubscriber
 */
final class ModifyFieldDefinitionFieldsSubscriberTest extends TestCase
{
    private const FIELD_TYPE_IDENTIFIER = 'foo';
    private const MODIFIED_OPTIONS = [
        'disable_identifier_field' => true,
        'disable_required_field' => true,
        'disable_translatable_field' => true,
        'disable_remove' => true,
    ];

    private ModifyFieldDefinitionFieldsSubscriber $modifyFieldDefinitionFieldsSubscriber;

    /** @var \Symfony\Component\Form\FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private FormInterface $form;

    private FormBuilderInterface $formBuilder;

    protected function setUp(): void
    {
        $this->form = $this->createMock(FormInterface::class);
        $this->formBuilder = $this->createMock(FormBuilderInterface::class);
        $this->modifyFieldDefinitionFieldsSubscriber = new ModifyFieldDefinitionFieldsSubscriber(
            self::FIELD_TYPE_IDENTIFIER,
            self::MODIFIED_OPTIONS
        );
    }

    public function testDisableFields(): void
    {
        $fieldIdentifier = 'field_123456789';
        $data = $this->getFormData($fieldIdentifier);
        $event = new FormEvent($this->form, $data);
        $options = [
            'required' => true,
            'label' => 'foo',
            'csrf_protection' => 'foo',
            'label_format' => null,
        ];

        $this->mockFormGet($fieldIdentifier);
        $this->mockFormGetName($fieldIdentifier);
        $this->mockFormGetConfig();
        $this->mockFormBuilderGetOptions($options);
        $this->mockFormRemove($fieldIdentifier);

        $this->mockFormAdd(
            $fieldIdentifier,
            array_merge(
                $options,
                self::MODIFIED_OPTIONS
            )
        );

        $this->modifyFieldDefinitionFieldsSubscriber->onPreSetData($event);
    }

    private function getFormData(string $identifier): array
    {
        return [
            $identifier => new FieldDefinitionData(
                [
                    'identifier' => $identifier,
                    'isRequired' => true,
                    'isThumbnail' => false,
                    'isInfoCollector' => false,
                    'fieldDefinition' => new FieldDefinition(
                        [
                            'identifier' => $identifier,
                            'fieldTypeIdentifier' => self::FIELD_TYPE_IDENTIFIER,
                        ]
                    ),
                ]
            ),
        ];
    }

    private function mockFormGet(string $identifier): void
    {
        $this->form
            ->expects(self::once())
            ->method('has')
            ->with($identifier)
            ->willReturn(true);

        $this->form
            ->expects(self::once())
            ->method('get')
            ->with($identifier)
            ->willReturn($this->form);
    }

    private function mockFormGetName(string $identifier): void
    {
        $this->form
            ->expects(self::once())
            ->method('getName')
            ->willReturn($identifier);
    }

    private function mockFormGetConfig(): void
    {
        $this->form
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn($this->formBuilder);
    }

    /**
     * @param array<string, scalar|bool> $options
     */
    private function mockFormBuilderGetOptions(array $options): void
    {
        $this->formBuilder
            ->expects(self::once())
            ->method('getOptions')
            ->willReturn($options);
    }

    private function mockFormRemove(string $identifier): void
    {
        $this->form
            ->expects(self::once())
            ->method('remove')
            ->with($identifier)
            ->willReturn($this->form);
    }

    /**
     * @param array<string, scalar|bool> $options
     */
    private function mockFormAdd(string $identifier, array $options): void
    {
        $this->form
            ->expects(self::once())
            ->method('add')
            ->with(
                $identifier,
                FieldDefinitionType::class,
                $options,
            )
            ->willReturn($this->form);
    }
}
