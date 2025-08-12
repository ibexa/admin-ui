<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\AdminUi\Behat\Component\Table\TableInterface;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use Ibexa\Contracts\Core\Repository\ContentTypeService;

final class ContentTypePage extends Page
{
    private ?string $expectedContentTypeName = null;

    private mixed $expectedContentTypeGroupId;

    private mixed $expectedContentTypeId;

    private TableInterface $fieldTable;

    public function __construct(
        readonly Session $session,
        readonly Router $router,
        private readonly ContentTypeService $contentTypeService,
        readonly TableBuilder $tableBuilder
    ) {
        parent::__construct($session, $router);

        $this->fieldTable = $tableBuilder
            ->newTable()
            ->withParentLocator($this->getLocator('contentFieldsTable'))
            ->build();
    }

    public function hasProperty(string $label, string $value): bool
    {
        return $this->getHTMLPage()
            ->findAll($this->getLocator('globalPropertiesItem'))
            ->getByCriterion(new ChildElementTextCriterion($this->getLocator('globalPropertiesLabel'), $label))
            ->find($this->getLocator('globalPropertiesValue'))
            ->getText() === $value;
    }

    /**
     * @param array<string, mixed> $fieldTypeData
     */
    public function hasFieldType(array $fieldTypeData): bool
    {
        return $this->fieldTable->hasElement($fieldTypeData);
    }

    public function getName(): string
    {
        return 'Content type';
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
                    $this->expectedContentTypeId = $contentType->id;
                    $this->expectedContentTypeGroupId = $group->id;

                    return;
                }
            }
        }
    }

    protected function getRoute(): string
    {
        return sprintf(
            '/contenttypegroup/%d/contenttype/%d',
            $this->expectedContentTypeGroupId,
            $this->expectedContentTypeId
        );
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('createButton', '.btn-icon .ibexa-icon--create'),
            new VisibleCSSLocator('pageTitle', '.ibexa-page-title h1'),
            new VisibleCSSLocator('contentTypeDataTable', '.ibexa-details .ibexa-table'),
            new VisibleCSSLocator('contentFieldsTable', 'section.ibexa-fieldgroup:nth-of-type(1)'),
            new VisibleCSSLocator('globalPropertiesItem', '.ibexa-details__item'),
            new VisibleCSSLocator('globalPropertiesLabel', '.ibexa-details__item-label'),
            new VisibleCSSLocator('globalPropertiesValue', '.ibexa-details__item-content'),
        ];
    }
}
