<?php

namespace AsyncAws\Ses\ValueObject;

/**
 * An object that contains the recipients of the email message.
 */
final class Destination
{
    /**
     * An array that contains the email addresses of the "To" recipients for the email.
     */
    private $toAddresses;

    /**
     * An array that contains the email addresses of the "CC" (carbon copy) recipients for the email.
     */
    private $ccAddresses;

    /**
     * An array that contains the email addresses of the "BCC" (blind carbon copy) recipients for the email.
     */
    private $bccAddresses;

    /**
     * @param array{
     *   ToAddresses?: null|string[],
     *   CcAddresses?: null|string[],
     *   BccAddresses?: null|string[],
     * } $input
     */
    public function __construct(array $input)
    {
        $this->toAddresses = $input['ToAddresses'] ?? null;
        $this->ccAddresses = $input['CcAddresses'] ?? null;
        $this->bccAddresses = $input['BccAddresses'] ?? null;
    }

    public static function create($input): self
    {
        return $input instanceof self ? $input : new self($input);
    }

    /**
     * @return string[]
     */
    public function getBccAddresses(): array
    {
        return $this->bccAddresses ?? [];
    }

    /**
     * @return string[]
     */
    public function getCcAddresses(): array
    {
        return $this->ccAddresses ?? [];
    }

    /**
     * @return string[]
     */
    public function getToAddresses(): array
    {
        return $this->toAddresses ?? [];
    }

    /**
     * @internal
     */
    public function requestBody(): array
    {
        $payload = [];
        if (null !== $v = $this->toAddresses) {
            $index = -1;
            $payload['ToAddresses'] = [];
            foreach ($v as $listValue) {
                ++$index;
                $payload['ToAddresses'][$index] = $listValue;
            }
        }
        if (null !== $v = $this->ccAddresses) {
            $index = -1;
            $payload['CcAddresses'] = [];
            foreach ($v as $listValue) {
                ++$index;
                $payload['CcAddresses'][$index] = $listValue;
            }
        }
        if (null !== $v = $this->bccAddresses) {
            $index = -1;
            $payload['BccAddresses'] = [];
            foreach ($v as $listValue) {
                ++$index;
                $payload['BccAddresses'][$index] = $listValue;
            }
        }

        return $payload;
    }
}
