<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Exception;
use Ibexa\AdminUi\Form\Data\Asset\ImageAssetUploadData;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Core\FieldType\Image\Value as ImageValue;
use Ibexa\Core\FieldType\ImageAsset\AssetMapper as ImageAssetMapper;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AssetController extends Controller
{
    public const string CSRF_TOKEN_HEADER = 'X-CSRF-Token';

    public const string LANGUAGE_CODE_KEY = 'languageCode';
    public const string FILE_KEY = 'file';

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly ImageAssetMapper $imageAssetMapper,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function uploadImageAction(Request $request): Response
    {
        if (!$this->isValidCsrfToken($request)) {
            return $this->createInvalidCsrfResponse();
        }

        $data = new ImageAssetUploadData(
            $request->files->get(self::FILE_KEY),
            $request->request->get(self::LANGUAGE_CODE_KEY)
        );

        $errors = $this->validator->validate($data);
        if ($errors->count() === 0) {
            try {
                $file = $data->getFile();
                if ($file === null) {
                    throw new Exception('File is missing in the request.');
                }

                $content = $this->imageAssetMapper->createAsset(
                    $file->getClientOriginalName(),
                    new ImageValue([
                        'inputUri' => $file->getRealPath(),
                        'fileSize' => $file->getSize(),
                        'fileName' => $file->getClientOriginalName(),
                        'alternativeText' => $file->getClientOriginalName(),
                    ]),
                    $data->getLanguageCode() ?? ''
                );

                $contentInfo = $content->getContentInfo();

                return new JsonResponse([
                    'destinationContent' => [
                        'id' => $contentInfo->getId(),
                        'name' => $content->getName(),
                        'locationId' => $contentInfo->getMainLocationId(),
                    ],
                    'value' => $this->imageAssetMapper->getAssetValue($content),
                ]);
            } catch (Exception $e) {
                return $this->createGenericErrorResponse($e->getMessage());
            }
        } else {
            return $this->createInvalidInputResponse($errors);
        }
    }

    private function createInvalidCsrfResponse(): JsonResponse
    {
        $errorMessage = $this->translator->trans(
            /** @Desc("Missing or invalid CSRF token") */
            'asset.upload.invalid_csrf',
            [],
            'ibexa_admin_ui'
        );

        return $this->createGenericErrorResponse($errorMessage);
    }

    private function createInvalidInputResponse(ConstraintViolationListInterface $errors): JsonResponse
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->createGenericErrorResponse(implode(', ', $errorMessages));
    }

    private function createGenericErrorResponse(string $errorMessage): JsonResponse
    {
        return new JsonResponse(
            [
                'status' => 'failed',
                'error' => $errorMessage,
                'errorMessage' => $errorMessage,
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    private function isValidCsrfToken(Request $request): bool
    {
        $csrfTokenValue = $request->headers->get(self::CSRF_TOKEN_HEADER);

        return $this->csrfTokenManager->isTokenValid(
            new CsrfToken('authenticate', $csrfTokenValue)
        );
    }
}
