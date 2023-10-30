<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use ArrayObject;
use Ibexa\AdminUi\Specification\UserExists;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class AuthorsTab extends AbstractEventDispatchingTab implements OrderedTabInterface
{
    public const URI_FRAGMENT = 'ibexa-tab-location-view-authors';

    protected UserService $userService;

    protected SectionService $sectionService;

    protected DatasetFactory $datasetFactory;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        SectionService $sectionService,
        UserService $userService,
        DatasetFactory $datasetFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);

        $this->sectionService = $sectionService;
        $this->userService = $userService;
        $this->datasetFactory = $datasetFactory;
    }

    public function getIdentifier(): string
    {
        return 'authors';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        /** @Desc("authors") */
        return $this->translator->trans('tab.name.authors', [], 'ibexa_locationview');
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 200;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/authors.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $contextParameters['content'];

        $versionInfo = $content->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();

        $viewParameters = new ArrayObject([
            'content_info' => $contentInfo,
            'version_info' => $versionInfo,
        ]);

        $this->supplyCreator($viewParameters, $contentInfo);
        $this->supplyLastContributor($viewParameters, $versionInfo);

        return array_replace($contextParameters, $viewParameters->getArrayCopy());
    }

    /**
     * @param \ArrayObject $parameters
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $versionInfo
     */
    private function supplyLastContributor(ArrayObject $parameters, VersionInfo $versionInfo): void
    {
        $parameters['last_contributor'] = null;
        if ((new UserExists($this->userService))->isSatisfiedBy($versionInfo->creatorId)) {
            $parameters['last_contributor'] = $this->userService->loadUser($versionInfo->creatorId);
        }
    }

    /**
     * @param \ArrayObject $parameters
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo
     */
    private function supplyCreator(ArrayObject $parameters, ContentInfo $contentInfo): void
    {
        $parameters['creator'] = null;
        if ((new UserExists($this->userService))->isSatisfiedBy($contentInfo->ownerId)) {
            $parameters['creator'] = $this->userService->loadUser($contentInfo->ownerId);
        }
    }
}
