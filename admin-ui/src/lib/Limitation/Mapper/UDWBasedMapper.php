<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Form\DataTransformer\UDWBasedValueModelTransformer;
use Ibexa\AdminUi\Form\DataTransformer\UDWBasedValueViewTransformer;
use Ibexa\AdminUi\Limitation\LimitationFormMapperInterface;
use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Ancestor;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Path;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;

/**
 * Base class for mappers based on Universal Discovery Widget.
 */
class UDWBasedMapper implements LimitationFormMapperInterface, LimitationValueMapperInterface, TranslationContainerInterface
{
    protected LocationService $locationService;

    protected SearchService $searchService;

    /**
     * Form template to use.
     *
     * @var string
     */
    private $template;

    private PermissionResolver $permissionResolver;

    private Repository $repository;

    public function __construct(
        LocationService $locationService,
        SearchService $searchService,
        PermissionResolver $permissionResolver,
        Repository $repository
    ) {
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->permissionResolver = $permissionResolver;
        $this->repository = $repository;
    }

    public function setFormTemplate($template): void
    {
        $this->template = $template;
    }

    public function getFormTemplate()
    {
        return $this->template;
    }

    public function mapLimitationForm(FormInterface $form, Limitation $data): void
    {
        $form->add(
            // Creating from FormBuilder as we need to add a DataTransformer.
            $form->getConfig()->getFormFactory()
                ->createBuilder()
                ->create('limitationValues', HiddenType::class, [
                    'required' => false,
                    'label' => LimitationIdentifierToLabelConverter::convert($data->getIdentifier()),
                ])
                ->addViewTransformer(new UDWBasedValueViewTransformer($this->locationService))
                ->addModelTransformer(
                    new UDWBasedValueModelTransformer(
                        $this->locationService,
                        $this->permissionResolver,
                        $this->repository
                    )
                )
                // Deactivate auto-initialize as we're not on the root form.
                ->setAutoInitialize(false)->getForm()
        );
    }

    public function filterLimitationValues(Limitation $limitation)
    {
    }

    /**
     * @phpstan-return list<\Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo[]>
     */
    public function mapLimitationValue(Limitation $limitation): array
    {
        $values = [];

        foreach ($limitation->limitationValues as $id) {
            $location = $this->locationService->loadLocation($id);

            $query = new LocationQuery([
                'filter' => new Ancestor($location->getPathString()),
                'sortClauses' => [new Path()],
            ]);

            $path = [];
            foreach ($this->searchService->findLocations($query)->searchHits as $hit) {
                $path[] = $hit->valueObject->getContentInfo();
            }

            $values[] = $path;
        }

        return $values;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                LimitationIdentifierToLabelConverter::convert('node'),
                'ibexa_content_forms_policies'
            )->setDesc('Location'),
        ];
    }
}
