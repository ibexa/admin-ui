<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\TrashLocationOptionProvider;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Form\FormInterface;

final readonly class OptionsFactory
{
    /**
     * @param iterable<\Ibexa\AdminUi\Form\TrashLocationOptionProvider\TrashLocationOptionProvider> $providers
     */
    public function __construct(private iterable $providers)
    {
    }

    public function addOptions(FormInterface $form, ?Location $location = null): void
    {
        if (!$location) {
            return;
        }

        foreach ($this->providers as $strategy) {
            if ($strategy->supports($location)) {
                $strategy->addOptions($form, $location);
            }
        }
    }
}
