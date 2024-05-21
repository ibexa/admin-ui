<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUserGroup;
use Ibexa\AdminUi\Specification\OrSpecification;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\ConditionalTabInterface;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class PoliciesTab extends AbstractEventDispatchingTab implements OrderedTabInterface, ConditionalTabInterface
{
    public const URI_FRAGMENT = 'ibexa-tab-location-view-policies';

    /** @var \Ibexa\AdminUi\UI\Dataset\DatasetFactory */
    protected $datasetFactory;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    protected $permissionResolver;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    protected $configResolver;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        DatasetFactory $datasetFactory,
        PermissionResolver $permissionResolver,
        EventDispatcherInterface $eventDispatcher,
        ConfigResolverInterface $configResolver
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);

        $this->datasetFactory = $datasetFactory;
        $this->permissionResolver = $permissionResolver;
        $this->configResolver = $configResolver;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'policies';
    }

    /**
     * @return string
     *
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function getName(): string
    {
        /** @Desc("Policies") */
        return $this->translator->trans('tab.name.policies', [], 'ibexa_locationview');
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 900;
    }

    /**
     * Get information about tab presence.
     *
     * @param array $parameters
     *
     * @return bool
     *
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

        $isUser = new ContentTypeIsUser($this->configResolver->getParameter('user_content_type_identifier'));
        $isUserGroup = new ContentTypeIsUserGroup($this->configResolver->getParameter('user_group_content_type_identifier'));
        $isUserOrUserGroup = (new OrSpecification($isUser, $isUserGroup))->isSatisfiedBy($contentType);

        return $isUserOrUserGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/policies/tab.html.twig';
    }

    /**
     * {@inheritdoc}
     */
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
