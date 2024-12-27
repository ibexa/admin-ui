<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use function in_array;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class ContentCreateLanguageChoiceLoader implements ChoiceLoaderInterface
{
    private LanguageChoiceLoader $languageChoiceLoader;

    /** @var string[] */
    private array $restrictedLanguagesCodes;

    /**
     * @param array<string> $restrictedLanguagesCodes
     */
    public function __construct(
        LanguageChoiceLoader $languageChoiceLoader,
        array $restrictedLanguagesCodes
    ) {
        $this->languageChoiceLoader = $languageChoiceLoader;
        $this->restrictedLanguagesCodes = $restrictedLanguagesCodes;
    }

    public function loadChoiceList(?callable $value = null): ChoiceListInterface
    {
        $languages = $this->languageChoiceLoader->getChoiceList();

        if (empty($this->restrictedLanguagesCodes)) {
            return new ArrayChoiceList($languages, $value);
        }

        $languages = array_filter($languages, function (Language $language) {
            return in_array($language->languageCode, $this->restrictedLanguagesCodes, true);
        });

        return new ArrayChoiceList($languages, $value);
    }

    /**
     * @return string[]
     */
    public function loadChoicesForValues(array $values, ?callable $value = null): array
    {
        // Optimize
        $values = array_filter($values);
        if (empty($values)) {
            return [];
        }

        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    /**
     * @return string[]
     */
    public function loadValuesForChoices(array $choices, ?callable $value = null): array
    {
        // Optimize
        $choices = array_filter($choices);
        if (empty($choices)) {
            return [];
        }

        // If no callable is set, choices are the same as values
        if (null === $value) {
            return $choices;
        }

        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }
}
