<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Behat\Component\Fields;

use Ibexa\Behat\Browser\Element\Condition\ElementExistsCondition;
use Ibexa\Behat\Browser\Element\Mapper\ElementTextMapper;
use Ibexa\Behat\Browser\Locator\CSSLocatorBuilder;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use PHPUnit\Framework\Assert;

class User extends FieldTypeComponent
{
    public function getFieldTypeIdentifier(): string
    {
        return 'ibexa_user';
    }

    public function setValue(array $parameters): void
    {
        $this->setSpecificFieldValue('username', $parameters['Username']);
        $this->setSpecificFieldValue('password', $parameters['Password']);
        $this->setSpecificFieldValue('confirmPassword', $parameters['Confirm password']);
        $this->setSpecificFieldValue('email', $parameters['Email']);
        $this->setEnabledField(true);
    }

    public function setSpecificFieldValue(string $fieldName, string $value): void
    {
        $this->getHTMLPage()->find($this->parentLocator)->find($this->getLocator($fieldName))->setValue($value);
    }

    public function getValue(): array
    {
        return [
            'username' => $this->getSpecificFieldValue('username'),
            'email' => $this->getSpecificFieldValue('email'),
        ];
    }

    public function getSpecificFieldValue(string $fieldName): string
    {
        return $this->getHTMLPage()->find($this->parentLocator)->find($this->getLocator($fieldName))->getValue();
    }

    public function verifyValue(array $value): void
    {
        Assert::assertEquals(
            $value['username'],
            $this->getValue()['username'],
            sprintf('Field %s has wrong value', $value['label'])
        );
        Assert::assertEquals(
            $value['email'],
            $this->getValue()['email'],
            sprintf('Field %s has wrong value', $value['label'])
        );
    }

    public function verifyValueInItemView(array $values): void
    {
        $locator = CSSLocatorBuilder::base($this->parentLocator)
            ->withDescendant(new VisibleCSSLocator('userViewField', 'tr td:nth-of-type(2)'))
            ->build();

        [$actualUsername, $actualEmail, $actualEnabled] = $this->getHTMLPage()
            ->findAll($locator)
            ->mapBy(new ElementTextMapper());

        Assert::assertEquals($values['Username'], $actualUsername, sprintf('Expected: %s Actual: %s', $values['Username'], $actualUsername));
        Assert::assertEquals($values['Email'], $actualEmail, sprintf('Expected: %s Actual: %s', $values['Email'], $actualEmail));
        Assert::assertEquals($values['Enabled'], $actualEnabled, sprintf('Expected: %s Actual: %s', $values['Enabled'], $actualEnabled));
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('firstname', '#ezplatform_content_forms_user_create_fieldsData_first_name_value,#ezplatform_content_forms_user_update_fieldsData_first_name_value'),
            new VisibleCSSLocator('lastname', '#ezplatform_content_forms_user_create_fieldsData_last_name_value,#ezplatform_content_forms_user_update_fieldsData_last_name_value'),
            new VisibleCSSLocator('username', '#ezplatform_content_forms_user_create_fieldsData_user_account_value_username,#ezplatform_content_forms_user_update_fieldsData_user_account_value_username'),
            new VisibleCSSLocator('password', '#ezplatform_content_forms_user_create_fieldsData_user_account_value_password_first,#ezplatform_content_forms_user_update_fieldsData_user_account_value_password_first'),
            new VisibleCSSLocator('confirmPassword', '#ezplatform_content_forms_user_create_fieldsData_user_account_value_password_second,#ezplatform_content_forms_user_update_fieldsData_user_account_value_password_second'),
            new VisibleCSSLocator('email', '#ezplatform_content_forms_user_create_fieldsData_user_account_value_email,#ezplatform_content_forms_user_update_fieldsData_user_account_value_email'),
            new VisibleCSSLocator('buttonEnabled', '.ibexa-toggle--checkbox'),
            new VisibleCSSLocator('buttonEnabledToggleConfirmation', '.ibexa-toggle--is-checked'),
        ];
    }

    private function setEnabledField(bool $enabled): void
    {
        $isCurrentlyEnabled = $this->getHTMLPage()->find($this->parentLocator)->find($this->getLocator('buttonEnabled'))->getText() === 'On';
        if ($isCurrentlyEnabled !== $enabled) {
            $script = sprintf("document.querySelector('%s %s').click()", $this->parentLocator->getSelector(), $this->getLocator('buttonEnabled')->getSelector());
            $this->getHTMLPage()->executeJavaScript($script);
            $this->getHTMLPage()
                ->setTimeout(10)
                ->waitUntilCondition(new ElementExistsCondition($this->getHTMLPage(), $this->getLocator('buttonEnabledToggleConfirmation')));
        }
    }
}
