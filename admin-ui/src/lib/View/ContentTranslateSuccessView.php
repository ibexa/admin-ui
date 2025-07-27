<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\View;

use Ibexa\Core\MVC\Symfony\View\BaseView;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class ContentTranslateSuccessView extends BaseView
{
    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentType
     */
    public function __construct(Response $response)
    {
        parent::__construct('@ibexadesign/ui/no_content.html.twig');

        $this->setResponse($response);
        $this->setControllerReference(new ControllerReference('Ibexa\Bundle\AdminUi\Controller\ContentEditController::translationSuccessAction'));
    }
}
