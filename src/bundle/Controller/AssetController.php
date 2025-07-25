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
use Ibexa\Core\FieldType\ImageAsset\AssetMapper;
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

class AssetController extends Controller
{
    public const CSRF_TOKEN_HEADER = 'X-CSRF-Token';

    public const LANGUAGE_CODE_KEY = 'languageCode';
    public const FILE_KEY = 'file';

    private ValidatorInterface $validator;

    private CsrfTokenManagerInterface $csrfTokenManager;

    private AssetMapper $imageAssetMapper;

    private TranslatorInterface $translator;

    /**
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface $csrfTokenManager
     * @param \Ibexa\Core\FieldType\ImageAsset\AssetMapper $imageAssetMapper
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     */
    public function __construct(
        ValidatorInterface $validator,
        CsrfTokenManagerInterface $csrfTokenManager,
        ImageAssetMapper $imageAssetMapper,
        TranslatorInterface $translator
    ) {
        $this->validator = $validator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->imageAssetMapper = $imageAssetMapper;
        $this->translator = $translator;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentType
     */
    public function uploadImageAction(Request $request): Response
    {
        if ($this->isValidCsrfToken($request)) {
            $data = new ImageAssetUploadData(
                $request->files->get(self::FILE_KEY),
                $request->request->get(self::LANGUAGE_CODE_KEY)
            );

            $errors = $this->validator->validate($data);
            if ($errors->count() === 0) {
                try {
                    $file = $data->getFile();

                    $content = $this->imageAssetMapper->createAsset(
                        $file->getClientOriginalName(),
                        new ImageValue([
                            'inputUri' => $file->getRealPath(),
                            'fileSize' => $file->getSize(),
                            'fileName' => $file->getClientOriginalName(),
                            'alternativeText' => $file->getClientOriginalName(),
                        ]),
                        $data->getLanguageCode()
                    );

                    return new JsonResponse([
                        'destinationContent' => [
                            'id' => $content->contentInfo->id,
                            'name' => $content->getName(),
                            'locationId' => $content->contentInfo->mainLocationId,
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

        return $this->createInvalidCsrfResponse();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
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

    /**
     * @param \Symfony\Component\Validator\ConstraintViolationListInterface $errors
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    private function createInvalidInputResponse(ConstraintViolationListInterface $errors): JsonResponse
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $this->createGenericErrorResponse(implode(', ', $errorMessages));
    }

    /**
     * @param string $errorMessage
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
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

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    private function isValidCsrfToken(Request $request): bool
    {
        $csrfTokenValue = $request->headers->get(self::CSRF_TOKEN_HEADER);

        return $this->csrfTokenManager->isTokenValid(
            new CsrfToken('authenticate', $csrfTokenValue)
        );
    }
}
