<?php

namespace AsyncAws\Ses\ValueObject;

use AsyncAws\Core\Exception\InvalidArgument;

/**
 * The subject line of the email. The subject line can only contain 7-bit ASCII characters. However, you can specify
 * non-ASCII characters in the subject line by using encoded-word syntax, as described in RFC 2047.
 *
 * @see https://tools.ietf.org/html/rfc2047
 */
final class Content
{
    /**
     * The content of the message itself.
     */
    private $data;

    /**
     * The character set for the content. Because of the constraints of the SMTP protocol, Amazon SES uses 7-bit ASCII by
     * default. If the text includes characters outside of the ASCII range, you have to specify a character set. For
     * example, you could specify `UTF-8`, `ISO-8859-1`, or `Shift_JIS`.
     */
    private $charset;

    /**
     * @param array{
     *   Data: string,
     *   Charset?: null|string,
     * } $input
     */
    public function __construct(array $input)
    {
        $this->data = $input['Data'] ?? null;
        $this->charset = $input['Charset'] ?? null;
    }

    public static function create($input): self
    {
        return $input instanceof self ? $input : new self($input);
    }

    public function getCharset(): ?string
    {
        return $this->charset;
    }

    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @internal
     */
    public function requestBody(): array
    {
        $payload = [];
        if (null === $v = $this->data) {
            throw new InvalidArgument(sprintf('Missing parameter "Data" for "%s". The value cannot be null.', __CLASS__));
        }
        $payload['Data'] = $v;
        if (null !== $v = $this->charset) {
            $payload['Charset'] = $v;
        }

        return $payload;
    }
}
