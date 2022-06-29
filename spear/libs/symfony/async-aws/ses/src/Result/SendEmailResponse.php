<?php

namespace AsyncAws\Ses\Result;

use AsyncAws\Core\Response;
use AsyncAws\Core\Result;

/**
 * A unique message ID that you receive when an email is accepted for sending.
 */
class SendEmailResponse extends Result
{
    /**
     * A unique identifier for the message that is generated when the message is accepted.
     */
    private $messageId;

    public function getMessageId(): ?string
    {
        $this->initialize();

        return $this->messageId;
    }

    protected function populateResult(Response $response): void
    {
        $data = $response->toArray();

        $this->messageId = isset($data['MessageId']) ? (string) $data['MessageId'] : null;
    }
}
