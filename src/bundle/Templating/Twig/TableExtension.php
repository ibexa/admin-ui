<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\Tab\Event\CollectCustomTableColumnsEvent;
use Ibexa\AdminUi\Tab\Event\CollectCustomTableHeadersEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TableExtension extends AbstractExtension
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ibexa_add_custom_table_headers', [$this, 'addCustomHeaders']),
            new TwigFunction('ibexa_add_custom_table_columns', [$this, 'addCustomColumns']),
        ];
    }

    public function addCustomHeaders(string $tableIdentifier, array $existingHeaders): array
    {
        $event = new CollectCustomTableHeadersEvent($tableIdentifier, $existingHeaders);
        $this->eventDispatcher->dispatch($event);

        return $event->getHeaders();
    }

    public function addCustomColumns(string $tableIdentifier, array $existingColumns, $row): array
    {
        $event = new CollectCustomTableColumnsEvent($tableIdentifier, $existingColumns, $row);
        $this->eventDispatcher->dispatch($event);

        return $event->getColumns();
    }
}
