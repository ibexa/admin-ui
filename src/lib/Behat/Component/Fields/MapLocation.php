<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component\Fields;

use Ibexa\Behat\Browser\Locator\CSSLocatorBuilder;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use PHPUnit\Framework\Assert;
use RuntimeException;

final class MapLocation extends FieldTypeComponent
{
    private const int OPEN_STREET_MAP_TIMEOUT = 20;

    public function setValue(array $parameters): void
    {
        $this->setSpecificCoordinate('address', $parameters['address']);
        $this->getHTMLPage()->find($this->getLocator('searchButton'))->click();

        $expectedLongitude = $parameters['longitude'];
        $expectedLatitude = $parameters['latitude'];

        $this->getHTMLPage()->setTimeout(self::OPEN_STREET_MAP_TIMEOUT)->waitUntil(
            function () use ($expectedLatitude, $expectedLongitude): bool {
                $currentValue = $this->getValue();

                return $currentValue['latitude'] === $expectedLatitude && $currentValue['longitude'] === $expectedLongitude;
            },
            'Failed to verify OpenStreetMaps data.'
        );
    }

    public function getValue(): array
    {
        return [
            'latitude' => $this->formatToOneDecimalPlace($this->getSpecificCoordinate('latitude')),
            'longitude' => $this->formatToOneDecimalPlace($this->getSpecificCoordinate('longitude')),
            'address' => $this->getSpecificCoordinate('address'),
        ];
    }

    public function getSpecificCoordinate(string $coordinateName): string
    {
        $coordinateSelector = CSSLocatorBuilder::base($this->parentLocator)
            ->withDescendant($this->getLocator($coordinateName))
            ->build()
        ;

        return $this->getHTMLPage()->find($coordinateSelector)->getValue();
    }

    public function verifyValueInEditView(array $values): void
    {
        $expectedLatitude = $values['latitude'];
        $expectedLongitude = $values['longitude'];
        $expectedAddress = $values['address'];

        Assert::assertEquals(
            $expectedLatitude,
            $this->getValue()['latitude'],
            sprintf('Field %s has wrong latitude value', $values['label'])
        );
        Assert::assertEquals(
            $expectedLongitude,
            $this->getValue()['longitude'],
            sprintf('Field %s has wrong longitude value', $values['label'])
        );
        Assert::assertEquals(
            $expectedAddress,
            $this->getValue()['address'],
            sprintf('Field %s has wrong address value', $values['label'])
        );
    }

    public function verifyValueInItemView(array $values): void
    {
        $mapText = $this->getHTMLPage()->find($this->parentLocator)->getText();

        $matches = [];
        $pattern = '/Address: (.*) Latitude: (.*) Longitude: (.*)/';
        preg_match($pattern, $mapText, $matches);

        if (empty($matches)) {
            throw new RuntimeException(sprintf(
                'Cannot match results for pattern: "%s" and subject: "%s".',
                $pattern,
                $mapText
            ));
        }

        $actualAddress = $matches[1];
        $actualLatitude = $this->formatToOneDecimalPlace($matches[2]);
        $actualLongitude = $this->formatToOneDecimalPlace($matches[3]);

        Assert::assertEquals($values['address'], $actualAddress);
        Assert::assertEquals($values['latitude'], $actualLatitude);
        Assert::assertEquals($values['longitude'], $actualLongitude);
    }

    public function getFieldTypeIdentifier(): string
    {
        return 'ibexa_gmap_location';
    }

    public function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('latitude', '#ezplatform_content_forms_content_edit_fieldsData_ibexa_gmap_location_value_latitude'),
            new VisibleCSSLocator('longitude', '#ezplatform_content_forms_content_edit_fieldsData_ibexa_gmap_location_value_longitude'),
            new VisibleCSSLocator('address', '#ezplatform_content_forms_content_edit_fieldsData_ibexa_gmap_location_value_address'),
            new VisibleCSSLocator('searchButton', '.ibexa-btn--search-by-address'),
        ];
    }

    private function setSpecificCoordinate(string $coordinateName, string $value): void
    {
        $fieldSelector = CSSLocatorBuilder::base($this->parentLocator)
            ->withDescendant($this->getLocator($coordinateName))
            ->build()
        ;
        $this->getHTMLPage()->find($fieldSelector)->setValue($value);
    }

    private function formatToOneDecimalPlace(string $value): string
    {
        $number = (float) $value;
        $formattedNumber = number_format($number, 1);

        return sprintf('%.1f', $formattedNumber);
    }
}
