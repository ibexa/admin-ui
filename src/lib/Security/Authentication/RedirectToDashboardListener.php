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

class RedirectToDashboardListener implements EventSubscriberInterface
{
    use TargetPathTrait;

    /** @var array */
    private array $siteAccessGroups;

    public function __construct(
        array $siteAccessGroups
    ) {
        $this->siteAccessGroups = $siteAccessGroups;
    }

    public static function getSubscribedEvents(): array
    {
        return [DetermineTargetUrlEvent::class => 'determineTargetUrl'];
    }

    public function determineTargetUrl(DetermineTargetUrlEvent $event)
    {
        $request = $event->getRequest();

        if ((new IsAdmin($this->siteAccessGroups))->isSatisfiedBy($request->attributes->get('siteaccess'))) {
            $target = $this->getTargetPath($request->getSession(), $event->getFirewallName());
            if (null !== $target && 1 === count(explode('/', trim(parse_url($target)['path'], '/')))) {
                $this->saveTargetPath(
                    $request->getSession(),
                    $event->getFirewallName(),
                    $event->getOptions()['default_target_path']
                );
            }
        }
    }
}
