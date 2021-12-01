<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Trash\ChoiceList\Loader;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\ContentTypeChoiceLoader;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;

class SearchContentTypeChoiceLoader extends ContentTypeChoiceLoader
{
    /** @var \Ibexa\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct(
        ContentTypeService $contentTypeService,
        UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        ConfigResolverInterface $configResolver
    ) {
        parent::__construct($contentTypeService, $userLanguagePreferenceProvider);

        $this->configResolver = $configResolver;
    }

    /**
     * @inheritdoc
     */
    public function loadChoiceList($value = null): ChoiceListInterface
    {
        $contentTypesGroups = $this->getChoiceList();
        $userContentTypeIdentifier = $this->configResolver->getParameter('user_content_type_identifier');

        foreach ($contentTypesGroups as $group => $contentTypes) {
            $contentTypesGroups[$group] = array_filter(
                $contentTypes,
                static function (ContentType $contentType) use ($userContentTypeIdentifier) {
                    $contentTypeIsUser = new ContentTypeIsUser($userContentTypeIdentifier);

                    return false === $contentTypeIsUser->isSatisfiedBy($contentType);
                }
            );
        }

        return new ArrayChoiceList($contentTypesGroups, $value);
    }
}

class_alias(SearchContentTypeChoiceLoader::class, 'EzSystems\EzPlatformAdminUi\Form\Type\Trash\ChoiceList\Loader\SearchContentTypeChoiceLoader');
