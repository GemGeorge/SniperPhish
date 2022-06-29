<?php

namespace AsyncAws\Ses\ValueObject;

use AsyncAws\Core\Exception\InvalidArgument;

/**
 * An object used to specify a list or topic to which an email belongs, which will be used when a contact chooses to
 * unsubscribe.
 */
final class ListManagementOptions
{
    /**
     * The name of the contact list.
     */
    private $contactListName;

    /**
     * The name of the topic.
     */
    private $topicName;

    /**
     * @param array{
     *   ContactListName: string,
     *   TopicName?: null|string,
     * } $input
     */
    public function __construct(array $input)
    {
        $this->contactListName = $input['ContactListName'] ?? null;
        $this->topicName = $input['TopicName'] ?? null;
    }

    public static function create($input): self
    {
        return $input instanceof self ? $input : new self($input);
    }

    public function getContactListName(): string
    {
        return $this->contactListName;
    }

    public function getTopicName(): ?string
    {
        return $this->topicName;
    }

    /**
     * @internal
     */
    public function requestBody(): array
    {
        $payload = [];
        if (null === $v = $this->contactListName) {
            throw new InvalidArgument(sprintf('Missing parameter "ContactListName" for "%s". The value cannot be null.', __CLASS__));
        }
        $payload['ContactListName'] = $v;
        if (null !== $v = $this->topicName) {
            $payload['TopicName'] = $v;
        }

        return $payload;
    }
}
