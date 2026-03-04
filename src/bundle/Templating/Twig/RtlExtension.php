<?php
namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;

class RtlExtension extends AbstractExtension implements GlobalsInterface
{
    private Packages $assets;
    private RequestStack $requestStack;

    public function __construct(Packages $assets, RequestStack $requestStack)
    {
        $this->assets = $assets;
        $this->requestStack = $requestStack;
    }


    public function getGlobals(): array
    {
        return [
            'ibexa_rtl' => $this,
        ];
    }

    public function isRtl(): bool
    {
        return true;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('ibexa_encore_entry_link_tags', [$this, 'renderRtlEncoreEntryLinkTags'], ['is_safe' => ['html']]),
        ];
    }

    public function renderRtlEncoreEntryLinkTags(string $entryName, ?string $packageName = null, ?string $entrypointName = null, array $extraRtlEntries = [])
    {
        $fileSuffix = $this->isRtl() ? '-rtl.css' : '.css';
        $url = $this->assets->getUrl("assets/ibexa/build/{$entryName}{$fileSuffix}", $packageName);
        $tags = sprintf('<link rel="stylesheet" href="%s" />', htmlspecialchars($url, ENT_QUOTES));

        if ($this->isRtl()) {
            foreach ($extraRtlEntries as $extra) {
                $extraEntry = is_array($extra) ? $extra['entry'] : $extra;
                $extraPackage = is_array($extra) ? ($extra['package'] ?? null) : null;
                $extraUrl = $this->assets->getUrl("assets/ibexa/build/{$extraEntry}.css", $extraPackage);
                $tags .= "\n" . sprintf('<link rel="stylesheet" href="%s" />', htmlspecialchars($extraUrl, ENT_QUOTES));
            }
        }

        return $tags;
    }
}
