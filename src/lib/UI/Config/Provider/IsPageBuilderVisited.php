<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Exception;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\Repository\UserPreferenceService;

final class IsPageBuilderVisited implements ProviderInterface
{
    private UserPreferenceService $userPreferenceService;

    public function __construct(UserPreferenceService $userPreferenceService)
    {
        $this->userPreferenceService = $userPreferenceService;
    }

    public function getConfig(): string
    {
        try {
            return $this->userPreferenceService
                ->getUserPreference('page_builder_visited')
                ->value;
        } catch (Exception $e) {
            return 'false';
        }
    }
}
