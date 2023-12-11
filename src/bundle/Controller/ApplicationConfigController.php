<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\REST\Value\ApplicationConfig;
use Ibexa\AdminUi\UI\Config\Aggregator;
use Ibexa\Rest\Server\Controller;

final class ApplicationConfigController extends Controller
{
    private Aggregator $aggregator;

    public function __construct(Aggregator $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    public function loadConfigAction(): ApplicationConfig
    {
        return new ApplicationConfig($this->aggregator->getConfig());
    }
}
