<?php

namespace AsyncAws\Ses\ValueObject;

/**
 * The template to use for the email message.
 */
final class Template
{
    /**
     * The name of the template. You will refer to this name when you send email using the `SendTemplatedEmail` or
     * `SendBulkTemplatedEmail` operations.
     */
    private $templateName;

    /**
     * The Amazon Resource Name (ARN) of the template.
     */
    private $templateArn;

    /**
     * An object that defines the values to use for message variables in the template. This object is a set of key-value
     * pairs. Each key defines a message variable in the template. The corresponding value defines the value to use for that
     * variable.
     */
    private $templateData;

    /**
     * @param array{
     *   TemplateName?: null|string,
     *   TemplateArn?: null|string,
     *   TemplateData?: null|string,
     * } $input
     */
    public function __construct(array $input)
    {
        $this->templateName = $input['TemplateName'] ?? null;
        $this->templateArn = $input['TemplateArn'] ?? null;
        $this->templateData = $input['TemplateData'] ?? null;
    }

    public static function create($input): self
    {
        return $input instanceof self ? $input : new self($input);
    }

    public function getTemplateArn(): ?string
    {
        return $this->templateArn;
    }

    public function getTemplateData(): ?string
    {
        return $this->templateData;
    }

    public function getTemplateName(): ?string
    {
        return $this->templateName;
    }

    /**
     * @internal
     */
    public function requestBody(): array
    {
        $payload = [];
        if (null !== $v = $this->templateName) {
            $payload['TemplateName'] = $v;
        }
        if (null !== $v = $this->templateArn) {
            $payload['TemplateArn'] = $v;
        }
        if (null !== $v = $this->templateData) {
            $payload['TemplateData'] = $v;
        }

        return $payload;
    }
}
