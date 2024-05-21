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

class AddLanguageFieldBasedOnContentListener
{
    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    private $languageService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     */
    public function __construct(ContentService $contentService, LanguageService $languageService)
    {
        $this->contentService = $contentService;
        $this->languageService = $languageService;
    }

    /**
     * @param \Symfony\Component\Form\FormEvent $event
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function onPreSetData(FormEvent $event)
    {
        /** @var \Ibexa\AdminUi\Form\Data\Content\CustomUrl\CustomUrlAddData $data */
        $data = $event->getData();
        $location = $data->getLocation();
        if (null === $location) {
            return;
        }
        $contentInfo = $location->getContentInfo();
        $versionInfo = $this->contentService->loadVersionInfo($contentInfo);
        $contentLanguages = $versionInfo->languageCodes;

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
     * @param array $contentLanguages
     *
     * @return callable
     */
    protected function getCallableFilter(array $contentLanguages): callable
    {
        return function () use ($contentLanguages) {
            return $this->filterLanguages($contentLanguages);
        };
    }

    /**
     * @param array $contentLanguages
     *
     * @return array
     */
    protected function filterLanguages(array $contentLanguages): array
    {
        return array_filter(
            $this->languageService->loadLanguages(),
            static function (Language $language) use ($contentLanguages) {
                return in_array($language->languageCode, $contentLanguages, true);
            }
        );
    }
}
