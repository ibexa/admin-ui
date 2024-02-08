<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\AdminUi\Event\FilterContentTypesEvent;
use Ibexa\AdminUi\UI\Service\ContentTypeIconResolver;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @phpstan-type TContentTypeData array{
 *      id: int,
 *      identifier: string,
 *      name: string|null,
 *      isContainer: bool,
 *      thumbnail: string,
 *      href: string,
 *  }
 */
class ContentTypes implements ProviderInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $userLanguagePreferenceProvider;

    /** @var \Ibexa\AdminUi\UI\Service\ContentTypeIconResolver */
    private $contentTypeIconResolver;

    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    private $urlGenerator;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider
     * @param \Ibexa\AdminUi\UI\Service\ContentTypeIconResolver $contentTypeIconResolver
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        ContentTypeService $contentTypeService,
        UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        ContentTypeIconResolver $contentTypeIconResolver,
        UrlGeneratorInterface $urlGenerator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->userLanguagePreferenceProvider = $userLanguagePreferenceProvider;
        $this->contentTypeIconResolver = $contentTypeIconResolver;
        $this->urlGenerator = $urlGenerator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @phpstan-return array<string, array<TContentTypeData>>
     */
    public function getConfig()
    {
        $contentTypeGroups = [];

        $preferredLanguages = $this->userLanguagePreferenceProvider->getPreferredLanguages();
        $loadedContentTypeGroups = $this->contentTypeService->loadContentTypeGroups(
            $preferredLanguages
        );
        foreach ($loadedContentTypeGroups as $contentTypeGroup) {
            $contentTypes = $this->contentTypeService->loadContentTypes(
                $contentTypeGroup,
                $preferredLanguages
            );

            usort($contentTypes, static function (ContentType $contentType1, ContentType $contentType2) {
                return strnatcasecmp($contentType1->getName(), $contentType2->getName());
            });

            foreach ($contentTypes as $contentType) {
                $contentTypeGroups[$contentTypeGroup->identifier][] = $this->getContentTypeData($contentType);
            }
        }

        /** @var \Ibexa\AdminUi\Event\FilterContentTypesEvent $event */
        $event = $this->eventDispatcher->dispatch(new FilterContentTypesEvent($contentTypeGroups));

        return $event->getContentTypeGroups();
    }

    /**
     * @phpstan-return TContentTypeData
     */
    private function getContentTypeData(ContentType $contentType): array
    {
        return [
            'id' => $contentType->id,
            'identifier' => $contentType->identifier,
            'name' => $contentType->getName(),
            'isContainer' => $contentType->isContainer,
            'thumbnail' => $this->contentTypeIconResolver->getContentTypeIcon($contentType->identifier),
            'href' => $this->urlGenerator->generate('ibexa.rest.load_content_type', [
                'contentTypeId' => $contentType->id,
            ]),
        ];
    }
}

class_alias(ContentTypes::class, 'EzSystems\EzPlatformAdminUi\UI\Config\Provider\ContentTypes');
