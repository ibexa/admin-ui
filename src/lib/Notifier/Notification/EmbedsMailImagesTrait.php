<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Notifier\Notification;

use InvalidArgumentException;
use Symfony\Bridge\Twig\Mime\NotificationEmail;

/**
 * @internal
 */
trait EmbedsMailImagesTrait
{
    private const string MAIL_IMAGE_DIRECTORY = '@IbexaAdminUiBundle/Resources/public/img/mail/';

    private const array MAIL_IMAGES = [
        'header_img_path' => 'header.png',
        'expiration_icon_path' => 'clock.png',
        'expiration_icon_dark_path' => 'clock-dark.png',
    ];

    /**
     * @return array<string, string>
     */
    private function embedMailImages(NotificationEmail $email): array
    {
        $context = [];
        foreach (self::MAIL_IMAGES as $contextKey => $fileName) {
            $imageResource = self::MAIL_IMAGE_DIRECTORY . $fileName;

            try {
                $imagePath = $this->kernel->locateResource($imageResource);
            } catch (InvalidArgumentException $e) {
                $this->logger->warning('Failed to load email image: ' . $e->getMessage(), [
                    'exception' => $e,
                    'image_path' => $imageResource,
                ]);

                continue;
            }

            $cid = pathinfo($fileName, PATHINFO_FILENAME) . '_' . uniqid() . '@ibexa';
            $email->embedFromPath($imagePath, $cid);
            $context[$contextKey] = 'cid:' . $cid;
        }

        return $context;
    }
}
