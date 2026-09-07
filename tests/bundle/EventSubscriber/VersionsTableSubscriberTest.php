<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\EventSubscriber;

use Ibexa\Bundle\AdminUi\EventSubscriber\VersionsTableSubscriber;
use Ibexa\Bundle\TwigComponents\Templating\Twig\Components\Table;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Event\PostMountEvent;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class VersionsTableSubscriberTest extends TestCase
{
    private VersionsTableSubscriber $subscriber;

    protected function setUp(): void
    {
        $blocks = [];
        foreach (['checkbox', 'version', 'modified_language', 'contributor', 'created', 'last_saved', 'actions'] as $column) {
            $blocks[] = sprintf('{%% block %1$s_header %%}%1$s header{%% endblock %%}', $column);
            $blocks[] = sprintf('{%% block %1$s_cell %%}%1$s cell{%% endblock %%}', $column);
        }

        $twig = new Environment(new ArrayLoader([
            '@ibexadesign/content/tab/versions/columns.html.twig' => implode('', $blocks),
        ]));

        $this->subscriber = new VersionsTableSubscriber($twig);
    }

    public function testIgnoresTablesOfOtherTypes(): void
    {
        $component = $this->createTable(type: 'products');

        $this->subscriber->onPostMount(new PostMountEvent($component, []));

        self::assertSame([], $component->getColumns());
    }

    public function testAddsCoreColumnsInPriorityOrderWithoutForm(): void
    {
        $component = $this->createTable(variant: VersionsTableSubscriber::VARIANT_PUBLISHED);

        $this->subscriber->onPostMount(new PostMountEvent($component, []));

        self::assertSame(
            ['version', 'modified_language', 'contributor', 'created', 'last_saved', 'actions'],
            array_keys($component->getColumns())
        );
    }

    public function testAddsCheckboxColumnFirstWhenFormIsPresent(): void
    {
        $component = $this->createTable(
            variant: VersionsTableSubscriber::VARIANT_DRAFT,
            parameters: ['form' => new FormView()]
        );

        $this->subscriber->onPostMount(new PostMountEvent($component, []));

        $columns = $component->getColumns();

        self::assertSame(
            ['checkbox', 'version', 'modified_language', 'contributor', 'created', 'last_saved', 'actions'],
            array_keys($columns)
        );
        self::assertSame(
            [
                'header_class' => 'ibexa-table__header-cell--checkbox',
                'cell_class' => 'ibexa-table__cell--has-checkbox',
            ],
            $columns['checkbox']->options
        );
        self::assertSame(
            [
                'header_class' => 'ibexa-table__header-cell--close-left',
                'cell_class' => 'ibexa-table__cell--close-left',
            ],
            $columns['version']->options
        );
    }

    public function testSkipsCreatedColumnForDraftConflictVariant(): void
    {
        $component = $this->createTable(variant: VersionsTableSubscriber::VARIANT_DRAFT_CONFLICT);

        $this->subscriber->onPostMount(new PostMountEvent($component, []));

        self::assertArrayNotHasKey('created', $component->getColumns());
    }

    public function testLeavesConsumerPriorityBandBetweenCoreColumns(): void
    {
        $component = $this->createTable(variant: VersionsTableSubscriber::VARIANT_DRAFT);

        $this->subscriber->onPostMount(new PostMountEvent($component, []));
        $component->addColumn(
            'translation_status',
            static fn (): string => 'header',
            static fn (): string => 'cell',
            VersionsTableSubscriber::PRIORITY_CONSUMER_MIN
        );

        self::assertSame(
            ['version', 'modified_language', 'translation_status', 'contributor', 'created', 'last_saved', 'actions'],
            array_keys($component->getColumns())
        );
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function createTable(
        string $type = VersionsTableSubscriber::TABLE_TYPE,
        ?string $variant = null,
        array $parameters = []
    ): Table {
        $component = new Table();
        $component->type = $type;
        $component->variant = $variant;
        $component->parameters = $parameters;
        $component->mount();

        return $component;
    }
}
