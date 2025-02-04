<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Event;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class FormActionEvent extends FormEvent
{
    /**
     * Name of the button used to submit the form.
     */
    private ?string $clickedButton;

    /**
     * Hash of options.
     *
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * Response to return after form post-processing. Typically, a RedirectResponse.
     */
    private ?Response $response;

    /**
     * Additional payload populated for event listeners next in priority.
     *
     * @var array<mixed>
     */
    private array $payloads;

    /**
     * @param array<string, mixed> $options
     * @param array<mixed> $payloads
     */
    public function __construct(
        FormInterface $form,
        mixed $data,
        ?string $clickedButton,
        array $options = [],
        array $payloads = []
    ) {
        parent::__construct($form, $data);
        $this->clickedButton = $clickedButton;
        $this->options = $options;
        $this->payloads = $payloads;
    }

    public function getClickedButton(): ?string
    {
        return $this->clickedButton;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $optionName The option name
     * @param mixed $defaultValue default value to return if option is not set
     */
    public function getOption(string $optionName, mixed $defaultValue = null): mixed
    {
        return $this->options[$optionName] ?? $defaultValue;
    }

    public function hasOption(string $optionName): bool
    {
        return isset($this->options[$optionName]);
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    /**
     * @return array<mixed>
     */
    public function getPayloads(): array
    {
        return $this->payloads;
    }

    /**
     * @param array<mixed> $payloads
     */
    public function setPayloads(array $payloads): void
    {
        $this->payloads = $payloads;
    }

    public function hasPayload(string $name): bool
    {
        return isset($this->payloads[$name]);
    }

    public function getPayload(string $name): mixed
    {
        return $this->payloads[$name];
    }

    public function setPayload(string $name, mixed $payload): void
    {
        $this->payloads[$name] = $payload;
    }
}
