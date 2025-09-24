<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\EventListener;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;

final readonly class AddLanguageFieldBasedOnContentListener
{
    public function __construct(
        private ContentService $contentService,
        private LanguageService $languageService
    ) {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function onPreSetData(FormEvent $event): void
    {
        /** @var \Ibexa\AdminUi\Form\Data\Content\CustomUrl\CustomUrlAddData $data */
        $data = $event->getData();
        $location = $data->getLocation();
        if (null === $location) {
            return;
        }
        $contentInfo = $location->getContentInfo();
        $versionInfo = $this->contentService->loadVersionInfo($contentInfo);
        $contentLanguages = $versionInfo->getLanguageCodes();

        $form = $event->getForm();

        $form->add(
            'language',
            ChoiceType::class,
            [
                'multiple' => false,
                'choice_loader' => new CallbackChoiceLoader($this->getCallableFilter($contentLanguages)),
                'choice_value' => 'languageCode',
                'choice_label' => 'name',
            ]
        );
    }

    /**
     * @param string[] $contentLanguages
     */
    private function getCallableFilter(array $contentLanguages): callable
    {
        return function () use ($contentLanguages): array {
            return $this->filterLanguages($contentLanguages);
        };
    }

    /**
     * @param string[] $contentLanguages
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    private function filterLanguages(array $contentLanguages): array
    {
        return array_filter(
            iterator_to_array($this->languageService->loadLanguages()),
            static function (Language $language) use ($contentLanguages): bool {
                return in_array($language->languageCode, $contentLanguages, true);
            }
        );
    }
}
