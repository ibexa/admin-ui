<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Language;

use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\LanguageChoiceLoader;

/**
 * Form Type allowing to select Language.
 *
 * @todo: This can replace LanguageType in the future but it'd require some changes in the frontend as well.
 */
class LanguageChoiceType extends AbstractLanguageChoiceType
{
    /**
     * @param \Ibexa\AdminUi\Form\Type\ChoiceList\Loader\LanguageChoiceLoader $languageChoiceLoader
     */
    public function __construct(LanguageChoiceLoader $languageChoiceLoader)
    {
        parent::__construct($languageChoiceLoader);
    }
}
