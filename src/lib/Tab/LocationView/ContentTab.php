<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Util\FieldDefinitionGroupsUtil;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ContentTab extends AbstractEventDispatchingTab implements OrderedTabInterface
{
    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        private readonly FieldDefinitionGroupsUtil $fieldDefinitionGroupsUtil,
        private readonly LanguageService $languageService,
        EventDispatcherInterface $eventDispatcher,
        private readonly ConfigResolverInterface $configResolver
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);
   }

    public function getIdentifier(): string
    {
        return 'content';
    }

    public function getName(): string
    {
        return $this->translator->trans(/** @Desc("Fields") */ 'tab.name.data', [], 'ibexa_locationview');
    }

    public function getOrder(): int
    {
        return 100;
    }

    public function getTemplate(): string
    {
        return '@ibexadesign/content/tab/content.html.twig';
    }

    public function getTemplateParameters(array $contextParameters = []): array
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $contextParameters['content'];
        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType */
        $contentType = $contextParameters['contentType'];
        $fieldDefinitions = $contentType->getFieldDefinitions();
        $fieldDefinitionsByGroup = $this->fieldDefinitionGroupsUtil->groupFieldDefinitions($fieldDefinitions);

        $languages = $this->loadContentLanguages($content);

        return array_replace($contextParameters, [
            'content' => $content,
            'field_definitions_by_group' => $fieldDefinitionsByGroup,
            'languages' => $languages,
            'location' => $contextParameters['location'],
        ]);
    }

    /**
     * @return list<\Ibexa\Contracts\Core\Repository\Values\Content\Language>
     */
    public function loadContentLanguages(Content $content): array
    {
        $contentLanguages = $content->getVersionInfo()->getLanguageCodes();

        $filter = static function (Language $language) use ($contentLanguages): bool {
            return $language->isEnabled() && in_array($language->getLanguageCode(), $contentLanguages, true);
        };

        $languagesByCode = [];
        $languages = iterator_to_array($this->languageService->loadLanguages());

        foreach (array_filter($languages, $filter) as $language) {
            $languagesByCode[$language->languageCode] = $language;
        }

        $saLanguages = [];
        foreach ($this->configResolver->getParameter('languages') as $languageCode) {
            if (!isset($languagesByCode[$languageCode])) {
                continue;
            }
            $saLanguages[] = $languagesByCode[$languageCode];
            unset($languagesByCode[$languageCode]);
        }

        return array_merge($saLanguages, array_values($languagesByCode));
    }
}
