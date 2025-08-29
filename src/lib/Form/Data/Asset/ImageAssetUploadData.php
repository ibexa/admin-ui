<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Asset;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageAssetUploadData
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Image(detectCorrupted: true)]
        private ?UploadedFile $file = null,
        #[Assert\NotBlank]
        private ?string $languageCode = null
    ) {
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    public function setLanguageCode(?string $languageCode): self
    {
        $this->languageCode = $languageCode;

        return $this;
    }
}
