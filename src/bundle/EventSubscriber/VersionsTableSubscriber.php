<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\EventSubscriber;

use Ibexa\Bundle\TwigComponents\Templating\Twig\Components\Table;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Event\PostMountEvent;
use Twig\Environment;
use Twig\TemplateWrapper;

/**
 * Contributes the core columns of the content view Versions tab tables to the
 * `ibexa.Table` component. Third-party columns register their own PostMount
 * subscribers guarded on {@see self::TABLE_TYPE} and use priorities between
 * {@see self::PRIORITY_CONSUMER_MIN} and {@see self::PRIORITY_CONSUMER_MAX}
 * to land between the built-in data columns and the trailing metadata columns.
 */
final readonly class VersionsTableSubscriber implements EventSubscriberInterface
{
    public const string TABLE_TYPE = 'versions';

    public const string VARIANT_DRAFT = 'draft';
    public const string VARIANT_PUBLISHED = 'published';
    public const string VARIANT_ARCHIVED = 'archived';
    public const string VARIANT_DRAFT_CONFLICT = 'draft_conflict';

    public const int PRIORITY_CONSUMER_MIN = 50;
    public const int PRIORITY_CONSUMER_MAX = 89;

    private const int PRIORITY_CHECKBOX = 110;
    private const int PRIORITY_VERSION = 100;
    private const int PRIORITY_MODIFIED_LANGUAGE = 90;
    private const int PRIORITY_CONTRIBUTOR = 40;
    private const int PRIORITY_CREATED = 30;
    private const int PRIORITY_LAST_SAVED = 20;
    private const int PRIORITY_ACTIONS = 10;

    private const string COLUMNS_TEMPLATE = '@ibexadesign/content/tab/versions/columns.html.twig';

    public function __construct(
        private Environment $twig
    ) {
    }

    /**
     * @return array<class-string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [PostMountEvent::class => 'onPostMount'];
    }

    public function onPostMount(PostMountEvent $event): void
    {
        $component = $event->getComponent();
        if (!$component instanceof Table || $component->type !== self::TABLE_TYPE) {
            return;
        }

        $template = $this->twig->load(self::COLUMNS_TEMPLATE);
        $form = $component->parameters['form'] ?? null;
        $form = $form instanceof FormView ? $form : null;
        $location = $component->parameters['location'] ?? null;
        $isDraftConflict = $component->variant === self::VARIANT_DRAFT_CONFLICT;

        if ($form !== null) {
            $variant = $component->variant;
            $component->addColumn(
                'checkbox',
                static fn (): string => $template->renderBlock('checkbox_header', ['variant' => $variant]),
                static fn (mixed $version): string => $template->renderBlock('checkbox_cell', [
                    'form' => $form,
                    'version' => $version,
                ]),
                self::PRIORITY_CHECKBOX,
                [
                    'header_class' => 'ibexa-table__header-cell--checkbox',
                    'cell_class' => 'ibexa-table__cell--has-checkbox',
                ]
            );
        }

        $component->addColumn(
            'version',
            static fn (): string => $template->renderBlock('version_header'),
            static fn (mixed $version): string => $template->renderBlock('version_cell', ['version' => $version]),
            self::PRIORITY_VERSION,
            $form !== null
                ? [
                    'header_class' => 'ibexa-table__header-cell--close-left',
                    'cell_class' => 'ibexa-table__cell--close-left',
                ]
                : []
        );

        $this->addTextColumn($component, $template, 'modified_language', self::PRIORITY_MODIFIED_LANGUAGE);

        $this->addTextColumn($component, $template, 'contributor', self::PRIORITY_CONTRIBUTOR);

        if (!$isDraftConflict) {
            $this->addTextColumn($component, $template, 'created', self::PRIORITY_CREATED);
        }

        $this->addTextColumn($component, $template, 'last_saved', self::PRIORITY_LAST_SAVED);

        $component->addColumn(
            'actions',
            static fn (): string => $template->renderBlock('actions_header'),
            static fn (mixed $version): string => $template->renderBlock('actions_cell', [
                'version' => $version,
                'is_draft_conflict' => $isDraftConflict,
                'location' => $location,
            ]),
            self::PRIORITY_ACTIONS
        );
    }

    private function addTextColumn(Table $component, TemplateWrapper $template, string $name, int $priority): void
    {
        $component->addColumn(
            $name,
            static fn (): string => $template->renderBlock($name . '_header'),
            static fn (mixed $version): string => $template->renderBlock($name . '_cell', ['version' => $version]),
            $priority
        );
    }
}
