<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SecurityExtension extends AbstractExtension
{
    public function __construct(
        private string $csrfTokenIntention,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_get_rest_csrf_token_intention',
                fn (): string => $this->csrfTokenIntention,
            ),
        ];
    }
}
