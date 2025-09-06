<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUserGroup;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\ConditionalTabInterface;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Core\Specification\OrSpecification;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class PoliciesTab extends AbstractEventDispatchingTab implements OrderedTabInterface, ConditionalTabInterface
{
    public const string URI_FRAGMENT = 'ibexa-tab-location-view-policies';

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        protected readonly DatasetFactory $datasetFactory,
        protected readonly PermissionResolver $permissionResolver,
        EventDispatcherInterface $eventDispatcher,
        protected readonly ConfigResolverInterface $configResolver
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);
    }

    public function getIdentifier(): string
    {
        return 'policies';
    }

    /**
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function getName(): string
    {
        /** @Desc("Policies") */
        return $this->translator->trans('tab.name.policies', [], 'ibexa_locationview');
    }

    public function getOrder(): int
    {
        return 900;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function evaluate(array $parameters): bool
    {
        if (false === $this->permissionResolver->canUser('role', 'read', $parameters['content'])) {
            return false;
        }

        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType */
        $contentType = $parameters['contentType'];

        $isUser = new ContentTypeIsUser(
            $this->configResolver->getParameter('user_content_type_identifier')
        );

        $isUserGroup = new ContentTypeIsUserGroup(
            $this->configResolver->getParameter('user_group_content_type_identifier')
        );

        return (new OrSpecification($isUser, $isUserGroup))->isSatisfiedBy($contentType);
    }

    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/policies/tab.html.twig';
    }

    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        $location = $contextParameters['location'];

        $policiesPaginationParams = $contextParameters['policies_pagination_params'];

        $policiesDataset = $this->datasetFactory->policies();
        $policiesDataset->load($location);

        $policiesPagerfanta = new Pagerfanta(
            new ArrayAdapter($policiesDataset->getPolicies())
        );

        $policiesPagerfanta->setMaxPerPage($policiesPaginationParams['limit']);
        $policiesPagerfanta->setCurrentPage(min($policiesPaginationParams['page'], $policiesPagerfanta->getNbPages()));

        $viewParameters = [
            'policies_pager' => $policiesPagerfanta,
            'policies_pagination_params' => $policiesPaginationParams,
        ];

        return array_replace($contextParameters, $viewParameters);
    }
}
