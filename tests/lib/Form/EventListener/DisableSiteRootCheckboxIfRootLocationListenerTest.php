<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Form\EventListener;

use Ibexa\AdminUi\Form\Data\Content\CustomUrl\CustomUrlAddData;
use Ibexa\AdminUi\Form\EventListener\DisableSiteRootCheckboxIfRootLocationListener;
use Ibexa\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class DisableSiteRootCheckboxIfRootLocationListenerTest extends TestCase
{
    public function testAddsCheckboxForRootLocation(): void
    {
        $location = $this->createMock(Location::class);
        $location
            ->method('getDepth')
            ->willReturn(1);

        $data = new CustomUrlAddData($location);
        $form = $this->createMock(FormInterface::class);

        $form
            ->expects(self::once())
            ->method('add')
            ->with(
                'site_root',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => false,
                    'disabled' => true,
                ]
            );

        $event = $this->createMock(FormEvent::class);

        $event
            ->method('getData')
            ->willReturn($data);

        $event
            ->method('getForm')
            ->willReturn($form);

        $listener = new DisableSiteRootCheckboxIfRootLocationListener();

        $listener->onPreSetData($event);
    }

    public function testDoesNothingWhenLocationIsNull(): void
    {
        $data = new CustomUrlAddData();
        $form = $this->createMock(FormInterface::class);

        $form
            ->expects(self::never())
            ->method('add');

        $event = $this->createMock(FormEvent::class);

        $event
            ->method('getData')
            ->willReturn($data);

        $event
            ->method('getForm')
            ->willReturn($form);

        $listener = new DisableSiteRootCheckboxIfRootLocationListener();

        $listener->onPreSetData($event);

        $form = $event->getForm();

        self::assertNotTrue($form->has('site_root'));
    }

    public function testDoesNothingWhenLocationDepthIsGreaterThanOne(): void
    {
        $location = $this->createMock(Location::class);
        $location
            ->method('getDepth')
            ->willReturn(2);

        $data = new CustomUrlAddData($location);
              $form = $this->createMock(FormInterface::class);

        $form
            ->expects(self::never())
            ->method('add');

        $event = $this->createMock(FormEvent::class);

        $event
            ->method('getData')
            ->willReturn($data);

        $event
            ->method('getForm')
            ->willReturn($form);

        $listener = new DisableSiteRootCheckboxIfRootLocationListener();

        $listener->onPreSetData($event);

        self::assertNotTrue($form->has('site_root'));
    }
}
