<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\Dialog;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use Ibexa\Contracts\Core\Repository\Repository;
use PHPUnit\Framework\Assert;

class LanguagePage extends Page
{
    /** @var string */
    private $expectedLanguageName;

    /** @var \Ibexa\AdminUi\Behat\Component\Table\Table */
    private $table;

    /** @var \Ibexa\AdminUi\Behat\Component\Dialog */
    private $dialog;

    /** @var int */
    private $expectedLanguageId;

    /** @var \Ibexa\Contracts\Core\Repository\Repository */
    private $repository;

    public function __construct(Session $session, Router $router, TableBuilder $tableBuilder, Dialog $dialog, Repository $repository)
    {
        parent::__construct($session, $router);
        $this->table = $tableBuilder->newTable()->build();
        $this->dialog = $dialog;
        $this->repository = $repository;
    }

    public function delete()
    {
        $this->getHTMLPage()
            ->findAll($this->getLocator('button'))
            ->getByCriterion(new ElementTextCriterion('Delete'))
            ->click();
        $this->dialog->verifyIsLoaded();
        $this->dialog->confirm();
    }

    public function hasProperties(array $languageProperties): bool
    {
        $hasExpectedEnabledFieldValue = true;
        if (array_key_exists('Enabled', $languageProperties)) {
            // Table does not handle returning non-string values
            $hasEnabledField = $this->getHTMLPage()->find($this->getLocator('enabledField'))->getValue() === 'on';
            $shouldHaveEnabledField = 'true' === $languageProperties['Enabled'];
            $hasExpectedEnabledFieldValue = $hasEnabledField === $shouldHaveEnabledField;
            unset($languageProperties['Enabled']);
        }

        if (!$hasExpectedEnabledFieldValue) {
            return false;
        }

        foreach ($languageProperties as $label => $value) {
            $isExpectedValuePresent = $this->getHTMLPage()
                    ->findAll($this->getLocator('languagePropertiesItem'))
                    ->getByCriterion(new ChildElementTextCriterion($this->getLocator('languagePropertiesLabel'), $label))
                    ->find($this->getLocator('languagePropertiesValue'))
                    ->getText() === $value;

            if (!$isExpectedValuePresent) {
                return false;
            }
        }

        return true;
    }

    public function edit()
    {
        $this->getHTMLPage()
            ->findAll($this->getLocator('button'))
            ->getByCriterion(new ElementTextCriterion('Edit'))
            ->click();
    }

    public function getName(): string
    {
        return 'Language';
    }

    public function setExpectedLanguageName(string $languageName)
    {
        $this->expectedLanguageName = $languageName;

        $languages = $this->repository->sudo(static function (Repository $repository) {
            return $repository->getContentLanguageService()->loadLanguages();
        });

        foreach ($languages as $language) {
            if ($language->name === $languageName) {
                $this->expectedLanguageId = $language->id;

                return;
            }
        }
    }

    public function verifyIsLoaded(): void
    {
        Assert::assertEquals(
            sprintf('Language "%s"', $this->expectedLanguageName),
            $this->getHTMLPage()->find($this->getLocator('pageTitle'))->getText()
        );
    }

    protected function getRoute(): string
    {
        return sprintf('/language/view/%d', $this->expectedLanguageId);
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('pageTitle', '.ibexa-page-title h1'),
            new VisibleCSSLocator('button', '.ibexa-btn'),
            new VisibleCSSLocator('enabledField', '.ibexa-input--checkbox'),
            new VisibleCSSLocator('languagePropertiesItem', '.ibexa-details__item'),
            new VisibleCSSLocator('languagePropertiesLabel', '.ibexa-details__item-label'),
            new VisibleCSSLocator('languagePropertiesValue', '.ibexa-details__item-content'),
        ];
    }
}
