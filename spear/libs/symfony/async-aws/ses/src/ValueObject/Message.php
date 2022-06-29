<?php

namespace AsyncAws\Ses\ValueObject;

use AsyncAws\Core\Exception\InvalidArgument;

/**
 * The simple email message. The message consists of a subject and a message body.
 */
final class Message
{
    /**
     * The subject line of the email. The subject line can only contain 7-bit ASCII characters. However, you can specify
     * non-ASCII characters in the subject line by using encoded-word syntax, as described in RFC 2047.
     *
     * @see https://tools.ietf.org/html/rfc2047
     */
    private $subject;

    /**
     * The body of the message. You can specify an HTML version of the message, a text-only version of the message, or both.
     */
    private $body;

    /**
     * @param array{
     *   Subject: Content|array,
     *   Body: Body|array,
     * } $input
     */
    public function __construct(array $input)
    {
        $this->subject = isset($input['Subject']) ? Content::create($input['Subject']) : null;
        $this->body = isset($input['Body']) ? Body::create($input['Body']) : null;
    }

    public static function create($input): self
    {
        return $input instanceof self ? $input : new self($input);
    }

    public function getBody(): Body
    {
        return $this->body;
    }

    public function getSubject(): Content
    {
        return $this->subject;
    }

    /**
     * @internal
     */
    public function requestBody(): array
    {
        $payload = [];
        if (null === $v = $this->subject) {
            throw new InvalidArgument(sprintf('Missing parameter "Subject" for "%s". The value cannot be null.', __CLASS__));
        }
        $payload['Subject'] = $v->requestBody();
        if (null === $v = $this->body) {
            throw new InvalidArgument(sprintf('Missing parameter "Body" for "%s". The value cannot be null.', __CLASS__));
        }
        $payload['Body'] = $v->requestBody();

        return $payload;
    }
}
