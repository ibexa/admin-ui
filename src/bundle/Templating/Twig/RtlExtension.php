<?php
namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\User\UserSetting\UserSettingService;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

class RtlExtension extends AbstractExtension implements GlobalsInterface
{
    private Packages $assets;
    private RequestStack $requestStack;
    private UserSettingService $userSettingService;
    private array $rtlLanguages;

    public function __construct(Packages $assets, RequestStack $requestStack, UserSettingService $userSettingService, array $rtlLanguages = [])
    {
        $this->assets = $assets;
        $this->requestStack = $requestStack;
        $this->userSettingService = $userSettingService;
        $this->rtlLanguages = $rtlLanguages;
    }

    public function getGlobals(): array
    {
        return [
            'ibexa_is_rtl' => $this->requestStack->getCurrentRequest() !== null && $this->isRtl(),
        ];
    }

    public function isRtl(): bool
    {
        $userLanguage = $this->userSettingService->getUserSetting('language')->value;

        return in_array($userLanguage, $this->rtlLanguages, true);
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
