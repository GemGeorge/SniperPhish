<?php

namespace AsyncAws\Ses\ValueObject;

/**
 * An object that contains the body of the message. You can send either a Simple message Raw message or a template
 * Message.
 */
final class EmailContent
{
    /**
     * The simple email message. The message consists of a subject and a message body.
     */
    private $simple;

    /**
     * The raw email message. The message has to meet the following criteria:.
     */
    private $raw;

    /**
     * The template to use for the email message.
     */
    private $template;

    /**
     * @param array{
     *   Simple?: null|Message|array,
     *   Raw?: null|RawMessage|array,
     *   Template?: null|Template|array,
     * } $input
     */
    public function __construct(array $input)
    {
        $this->simple = isset($input['Simple']) ? Message::create($input['Simple']) : null;
        $this->raw = isset($input['Raw']) ? RawMessage::create($input['Raw']) : null;
        $this->template = isset($input['Template']) ? Template::create($input['Template']) : null;
    }

    public static function create($input): self
    {
        return $input instanceof self ? $input : new self($input);
    }

    public function getRaw(): ?RawMessage
    {
        return $this->raw;
    }

    public function getSimple(): ?Message
    {
        return $this->simple;
    }

    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    /**
     * @internal
     */
    public function requestBody(): array
    {
        $payload = [];
        if (null !== $v = $this->simple) {
            $payload['Simple'] = $v->requestBody();
        }
        if (null !== $v = $this->raw) {
            $payload['Raw'] = $v->requestBody();
        }
        if (null !== $v = $this->template) {
            $payload['Template'] = $v->requestBody();
        }

        return $payload;
    }
}
