<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Form\Data\Content\Translation\MainTranslationUpdateData;
use Ibexa\AdminUi\Form\Data\Content\Translation\TranslationAddData;
use Ibexa\AdminUi\Form\Data\Content\Translation\TranslationDeleteData;
use Ibexa\AdminUi\Form\Type\Content\Translation\MainTranslationUpdateType;
use Ibexa\AdminUi\Form\Type\Content\Translation\TranslationAddType;
use Ibexa\AdminUi\Form\Type\Content\Translation\TranslationDeleteType;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Limitation\Target;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class TranslationsTab extends AbstractEventDispatchingTab implements OrderedTabInterface
{
    public const string URI_FRAGMENT = 'ibexa-tab-location-view-translations';

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        protected readonly DatasetFactory $datasetFactory,
        protected readonly UrlGeneratorInterface $urlGenerator,
        EventDispatcherInterface $eventDispatcher,
        private readonly FormFactoryInterface $formFactory,
        private readonly PermissionResolver $permissionResolver,
        private readonly LanguageService $languageService
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);
    }

    public function getIdentifier(): string
    {
        return 'translations';
    }

    public function getName(): string
    {
        /** @Desc("Translations") */
        return $this->translator->trans('tab.name.translations', [], 'ibexa_locationview');
    }

    public function getOrder(): int
    {
        return 300;
    }

    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/translations/tab.html.twig';
    }

    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        $location = $contextParameters['location'];
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $contextParameters['content'];
        $versionInfo = $content->getVersionInfo();
        $translationsDataset = $this->datasetFactory->translations();
        $translationsDataset->load($versionInfo);

        $translationAddForm = $this->createTranslationAddForm($location);

        $translationDeleteForm = $this->createTranslationDeleteForm(
            $location,
            $translationsDataset->getLanguageCodes()
        );

        $mainTranslationUpdateForm = $this->createMainLanguageUpdateForm(
            $content,
            $versionInfo->getContentInfo()->getMainLanguageCode()
        );

        $languagesCodes = array_column(
            iterator_to_array($this->languageService->loadLanguages()),
            'languageCode'
        );

        $canTranslate = $this->permissionResolver->canUser(
            'content',
            'edit',
            $location->getContentInfo(),
            [
                (new Target\Builder\VersionBuilder())->translateToAnyLanguageOf($languagesCodes)->build(),
                $location,
            ]
        );

        $viewParameters = [
            'translations' => $translationsDataset->getTranslations(),
            'form_translation_add' => $translationAddForm->createView(),
            'form_translation_remove' => $translationDeleteForm->createView(),
            'form_main_translation_update' => $mainTranslationUpdateForm->createView(),
            'main_translation_switch' => true,
            'can_translate' => $canTranslate,
        ];

        return array_replace($contextParameters, $viewParameters);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<\Ibexa\AdminUi\Form\Data\Content\Translation\TranslationAddData>
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    private function createTranslationAddForm(Location $location): FormInterface
    {
        $data = new TranslationAddData($location);

        return $this->formFactory->createNamed(
            'add-translation',
            TranslationAddType::class,
            $data
        );
    }

    /**
     * @param mixed[] $languageCodes
     *
     * @return \Symfony\Component\Form\FormInterface<\Ibexa\AdminUi\Form\Data\Content\Translation\TranslationDeleteData>
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    private function createTranslationDeleteForm(Location $location, array $languageCodes): FormInterface
    {
        $data = new TranslationDeleteData(
            $location->getContentInfo(),
            array_combine($languageCodes, array_fill_keys($languageCodes, false))
        );

        return $this->formFactory->createNamed('delete-translations', TranslationDeleteType::class, $data);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<
     *   \Ibexa\AdminUi\Form\Data\Content\Translation\MainTranslationUpdateData
     * >
     */
    private function createMainLanguageUpdateForm(Content $content, string $languageCode): FormInterface
    {
        $data = new MainTranslationUpdateData($content, $languageCode);

        return $this->formFactory->create(MainTranslationUpdateType::class, $data);
    }
}
