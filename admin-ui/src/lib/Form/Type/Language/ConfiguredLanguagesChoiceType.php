<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Language;

use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\ConfiguredLanguagesChoiceLoader;

/**
 * Form Type allowing to select from all configured (also not enabled) Languages.
 */
class ConfiguredLanguagesChoiceType extends AbstractLanguageChoiceType
{
    /**
     * @param \Ibexa\AdminUi\Form\Type\ChoiceList\Loader\ConfiguredLanguagesChoiceLoader $languageChoiceLoader
     */
    public function __construct(ConfiguredLanguagesChoiceLoader $languageChoiceLoader)
    {
        parent::__construct($languageChoiceLoader);
    }
}
