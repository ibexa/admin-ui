<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Security\Authentication;

use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Ibexa\Core\MVC\Symfony\Security\Authentication\DetermineTargetUrlEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class RedirectToDashboardListener implements EventSubscriberInterface
{
    use TargetPathTrait;

    /** @var array<string, array<int, string>> */
    private array $siteAccessGroups;

    /**
     * @param array<string, array<int, string>> $siteAccessGroups
     */
    public function __construct(
        array $siteAccessGroups
    ) {
        $this->siteAccessGroups = $siteAccessGroups;
    }

    public static function getSubscribedEvents(): array
    {
        return [DetermineTargetUrlEvent::class => 'determineTargetUrl'];
    }

    public function determineTargetUrl(DetermineTargetUrlEvent $event): void
    {
        $request = $event->getRequest();

        if (!(new IsAdmin($this->siteAccessGroups))->isSatisfiedBy($request->attributes->get('siteaccess'))) {
            return;
        }

        $target = $this->getTargetPath($request->getSession(), $event->getFirewallName());
        if ($this->isSingleSegmentPath($target)) {
            $this->saveTargetPath(
                $request->getSession(),
                $event->getFirewallName(),
                $event->getOptions()['default_target_path']
            );
        }
    }

    private function isSingleSegmentPath(?string $path): bool
    {
        if (null === $path) {
            return false;
        }

        $pathSegments = explode('/', trim(parse_url($path)['path'], '/'));

        return count($pathSegments) === 1;
    }
}
