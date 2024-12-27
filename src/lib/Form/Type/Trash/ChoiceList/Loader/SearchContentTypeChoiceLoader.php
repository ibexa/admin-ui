<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Trash\ChoiceList\Loader;

use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\ContentTypeChoiceLoader;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;

class SearchContentTypeChoiceLoader extends ContentTypeChoiceLoader
{
    private ConfigResolverInterface $configResolver;

    public function __construct(
        ContentTypeService $contentTypeService,
        UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        ConfigResolverInterface $configResolver
    ) {
        parent::__construct($contentTypeService, $userLanguagePreferenceProvider);

        $this->configResolver = $configResolver;
    }

    public function loadChoiceList(?callable $value = null): ChoiceListInterface
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
