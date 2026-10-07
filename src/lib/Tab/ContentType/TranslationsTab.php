<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\ContentType;

use Ibexa\AdminUi\Form\Data\ContentType\Translation\TranslationAddData;
use Ibexa\AdminUi\Form\Data\ContentType\Translation\TranslationRemoveData;
use Ibexa\AdminUi\Form\Factory\ContentTypeFormFactory;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class TranslationsTab extends AbstractEventDispatchingTab implements OrderedTabInterface
{
    public const URI_FRAGMENT = 'ibexa-tab-content-type-view-translations';

    /** @var DatasetFactory */
    protected $datasetFactory;

    /** @var ContentTypeFormFactory */
    protected $formFactory;

    /**
     * @param Environment $twig
     * @param TranslatorInterface $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param DatasetFactory $datasetFactory
     * @param ContentTypeFormFactory $formFactory
     */
    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        DatasetFactory $datasetFactory,
        ContentTypeFormFactory $formFactory
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);

        $this->datasetFactory = $datasetFactory;
        $this->formFactory = $formFactory;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'translations';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        /** @Desc("Translations") */
        return $this->translator->trans('tab.name.translations', [], 'ibexa_content_type');
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 200;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return '@ibexadesign/content_type/tab/translations.html.twig';
    }

    /**
     * @param mixed[] $contextParameters
     *
     * @return mixed[]
     *
     * @throws NotFoundException
     */
    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var ContentType $contentType */
        $contentType = $contextParameters['content_type'];
        $contentTypeGroup = $contextParameters['content_type_group'];

        $translationsDataset = $this->datasetFactory->translations();
        $translationsDataset->loadFromContentType($contentType);

        $translationAddForm = $this->formFactory->addContentTypeTranslation(
            new TranslationAddData(
                $contentType,
                $contentTypeGroup
            )
        );

        $translationRemoveForm = $this->formFactory->removeContentTypeTranslation(
            new TranslationRemoveData(
                $contentType,
                $contentTypeGroup,
                array_fill_keys($translationsDataset->getLanguageCodes(), false)
            )
        );

        $viewParameters = [
            'can_translate' => $contextParameters['can_update'],
            'translations' => $translationsDataset->getTranslations(),
            'form_translation_add' => $translationAddForm->createView(),
            'form_translation_remove' => $translationRemoveForm->createView(),
            'main_translation_switch' => false,
        ];

        return array_replace($contextParameters, $viewParameters);
    }
}

class_alias(TranslationsTab::class, 'EzSystems\EzPlatformAdminUi\Tab\ContentType\TranslationsTab');
