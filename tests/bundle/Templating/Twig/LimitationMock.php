<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Twig;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation;

final class LimitationMock extends Limitation
{
    /**
     * @param array<mixed> $limitationValues
     */
    public function __construct(private readonly string $identifier, array $limitationValues)
    {
        parent::__construct([
            'limitationValues' => $limitationValues,
        ]);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
