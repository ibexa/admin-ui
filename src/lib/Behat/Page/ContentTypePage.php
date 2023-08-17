<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use Ibexa\Contracts\Core\Repository\ContentTypeService;

class ContentTypePage extends Page
{
    /** @var string */
    private $expectedContentTypeName;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var mixed */
    private $expectedContenTypeGroupId;

    /** @var mixed */
    private $expectedContenTypeId;

    /** @var \Ibexa\AdminUi\Behat\Component\Table\Table */
    private $fieldTable;

    public function __construct(
        Session $session,
        Router $router,
        ContentTypeService $contentTypeService,
        TableBuilder $tableBuilder
    ) {
        parent::__construct($session, $router);
        $this->contentTypeService = $contentTypeService;
        $this->fieldTable = $tableBuilder->newTable()->withParentLocator($this->getLocator('contentFieldsTable'))->build();
    }

    public function hasProperty($label, $value): bool
    {
        return $this->getHTMLPage()
            ->findAll($this->getLocator('globalPropertiesItem'))
            ->getByCriterion(new ChildElementTextCriterion($this->getLocator('globalPropertiesLabel'), $label))
            ->find($this->getLocator('globalPropertiesValue'))
            ->getText() === $value;
    }

    public function hasFieldType(array $fieldTypeData): bool
    {
        return $this->fieldTable->hasElement($fieldTypeData);
    }

    public function getName(): string
    {
        return 'Content Type';
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()
            ->find($this->getLocator('pageTitle'))
            ->assert()->textEquals($this->expectedContentTypeName);
    }

    public function setExpectedContentTypeName(string $contentTypeName): void
    {
        $this->expectedContentTypeName = $contentTypeName;

        foreach ($this->contentTypeService->loadContentTypeGroups() as $group) {
            foreach ($this->contentTypeService->loadContentTypes($group) as $contentType) {
                if ($contentType->getName() === $contentTypeName) {
                    $this->expectedContenTypeId = $contentType->id;
                    $this->expectedContenTypeGroupId = $group->id;

                    return;
                }
            }
        }
    }

    protected function getRoute(): string
    {
        return sprintf(
            '/contenttypegroup/%d/contenttype/%d',
            $this->expectedContenTypeGroupId,
            $this->expectedContenTypeId
        );
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('createButton', '.btn-icon .ibexa-icon--create'),
            new VisibleCSSLocator('pageTitle', '.ibexa-page-title h1'),
            new VisibleCSSLocator('contentTypeDataTable', '.ibexa-details .ibexa-table'),
            new VisibleCSSLocator('contentFieldsTable', 'div.ibexa-fieldgroup:nth-of-type(2)'),
            new VisibleCSSLocator('globalPropertiesItem', '.ibexa-details__item'),
            new VisibleCSSLocator('globalPropertiesLabel', '.ibexa-details__item-label'),
            new VisibleCSSLocator('globalPropertiesValue', '.ibexa-details__item-content'),
        ];
    }
}
