<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

final class LanguageChoiceLoader extends ConfiguredLanguagesChoiceLoader
{
    public function getChoiceList(): array
    {
        $languages = parent::getChoiceList();
        $enabledLanguages = [];

        foreach ($languages as $language) {
            if ($language->isEnabled()) {
                $enabledLanguages[] = $language;
            }
        }

        return $enabledLanguages;
    }
}
