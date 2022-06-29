<?php

namespace AsyncAws\Ses\Input;

use AsyncAws\Core\Exception\InvalidArgument;
use AsyncAws\Core\Input;
use AsyncAws\Core\Request;
use AsyncAws\Core\Stream\StreamFactory;
use AsyncAws\Ses\ValueObject\Body;
use AsyncAws\Ses\ValueObject\Content;
use AsyncAws\Ses\ValueObject\Destination;
use AsyncAws\Ses\ValueObject\EmailContent;
use AsyncAws\Ses\ValueObject\ListManagementOptions;
use AsyncAws\Ses\ValueObject\Message;
use AsyncAws\Ses\ValueObject\MessageTag;
use AsyncAws\Ses\ValueObject\Template;

/**
 * Represents a request to send a single formatted email using Amazon SES. For more information, see the Amazon SES
 * Developer Guide.
 *
 * @see https://docs.aws.amazon.com/ses/latest/DeveloperGuide/send-email-formatted.html
 */
final class SendEmailRequest extends Input
{
    /**
     * The email address to use as the "From" address for the email. The address that you specify has to be verified.
     *
     * @var string|null
     */
    private $fromEmailAddress;

    /**
     * This parameter is used only for sending authorization. It is the ARN of the identity that is associated with the
     * sending authorization policy that permits you to use the email address specified in the `FromEmailAddress` parameter.
     *
     * @var string|null
     */
    private $fromEmailAddressIdentityArn;

    /**
     * An object that contains the recipients of the email message.
     *
     * @var Destination|null
     */
    private $destination;

    /**
     * The "Reply-to" email addresses for the message. When the recipient replies to the message, each Reply-to address
     * receives the reply.
     *
     * @var string[]|null
     */
    private $replyToAddresses;

    /**
     * The address that you want bounce and complaint notifications to be sent to.
     *
     * @var string|null
     */
    private $feedbackForwardingEmailAddress;

    /**
     * This parameter is used only for sending authorization. It is the ARN of the identity that is associated with the
     * sending authorization policy that permits you to use the email address specified in the
     * `FeedbackForwardingEmailAddress` parameter.
     *
     * @var string|null
     */
    private $feedbackForwardingEmailAddressIdentityArn;

    /**
     * An object that contains the body of the message. You can send either a Simple message Raw message or a template
     * Message.
     *
     * @required
     *
     * @var EmailContent|null
     */
    private $content;

    /**
     * A list of tags, in the form of name/value pairs, to apply to an email that you send using the `SendEmail` operation.
     * Tags correspond to characteristics of the email that you define, so that you can publish email sending events.
     *
     * @var MessageTag[]|null
     */
    private $emailTags;

    /**
     * The name of the configuration set to use when sending the email.
     *
     * @var string|null
     */
    private $configurationSetName;

    /**
     * An object used to specify a list or topic to which an email belongs, which will be used when a contact chooses to
     * unsubscribe.
     *
     * @var ListManagementOptions|null
     */
    private $listManagementOptions;

    /**
     * @param array{
     *   FromEmailAddress?: string,
     *   FromEmailAddressIdentityArn?: string,
     *   Destination?: Destination|array,
     *   ReplyToAddresses?: string[],
     *   FeedbackForwardingEmailAddress?: string,
     *   FeedbackForwardingEmailAddressIdentityArn?: string,
     *   Content?: EmailContent|array,
     *   EmailTags?: MessageTag[],
     *   ConfigurationSetName?: string,
     *   ListManagementOptions?: ListManagementOptions|array,
     *   @region?: string,
     * } $input
     */
    public function __construct(array $input = [])
    {
        $this->fromEmailAddress = $input['FromEmailAddress'] ?? null;
        $this->fromEmailAddressIdentityArn = $input['FromEmailAddressIdentityArn'] ?? null;
        $this->destination = isset($input['Destination']) ? Destination::create($input['Destination']) : null;
        $this->replyToAddresses = $input['ReplyToAddresses'] ?? null;
        $this->feedbackForwardingEmailAddress = $input['FeedbackForwardingEmailAddress'] ?? null;
        $this->feedbackForwardingEmailAddressIdentityArn = $input['FeedbackForwardingEmailAddressIdentityArn'] ?? null;
        $this->content = isset($input['Content']) ? EmailContent::create($input['Content']) : null;
        $this->emailTags = isset($input['EmailTags']) ? array_map([MessageTag::class, 'create'], $input['EmailTags']) : null;
        $this->configurationSetName = $input['ConfigurationSetName'] ?? null;
        $this->listManagementOptions = isset($input['ListManagementOptions']) ? ListManagementOptions::create($input['ListManagementOptions']) : null;
        parent::__construct($input);
    }

    public static function create($input): self
    {
        return $input instanceof self ? $input : new self($input);
    }

    public function getConfigurationSetName(): ?string
    {
        return $this->configurationSetName;
    }

    public function getContent(): ?EmailContent
    {
        return $this->content;
    }

    public function getDestination(): ?Destination
    {
        return $this->destination;
    }

    /**
     * @return MessageTag[]
     */
    public function getEmailTags(): array
    {
        return $this->emailTags ?? [];
    }

    public function getFeedbackForwardingEmailAddress(): ?string
    {
        return $this->feedbackForwardingEmailAddress;
    }

    public function getFeedbackForwardingEmailAddressIdentityArn(): ?string
    {
        return $this->feedbackForwardingEmailAddressIdentityArn;
    }

    public function getFromEmailAddress(): ?string
    {
        return $this->fromEmailAddress;
    }

    public function getFromEmailAddressIdentityArn(): ?string
    {
        return $this->fromEmailAddressIdentityArn;
    }

    public function getListManagementOptions(): ?ListManagementOptions
    {
        return $this->listManagementOptions;
    }

    /**
     * @return string[]
     */
    public function getReplyToAddresses(): array
    {
        return $this->replyToAddresses ?? [];
    }

    /**
     * @internal
     */
    public function request(): Request
    {
        // Prepare headers
        $headers = ['content-type' => 'application/json'];

        // Prepare query
        $query = [];

        // Prepare URI
        $uriString = '/v2/email/outbound-emails';

        // Prepare Body
        $bodyPayload = $this->requestBody();
        $body = empty($bodyPayload) ? '{}' : json_encode($bodyPayload, 4194304);

        // Return the Request
        return new Request('POST', $uriString, $query, $headers, StreamFactory::create($body));
    }

    public function setConfigurationSetName(?string $value): self
    {
        $this->configurationSetName = $value;

        return $this;
    }

    public function setContent(?EmailContent $value): self
    {
        $this->content = $value;

        return $this;
    }

    public function setDestination(?Destination $value): self
    {
        $this->destination = $value;

        return $this;
    }

    /**
     * @param MessageTag[] $value
     */
    public function setEmailTags(array $value): self
    {
        $this->emailTags = $value;

        return $this;
    }

    public function setFeedbackForwardingEmailAddress(?string $value): self
    {
        $this->feedbackForwardingEmailAddress = $value;

        return $this;
    }

    public function setFeedbackForwardingEmailAddressIdentityArn(?string $value): self
    {
        $this->feedbackForwardingEmailAddressIdentityArn = $value;

        return $this;
    }

    public function setFromEmailAddress(?string $value): self
    {
        $this->fromEmailAddress = $value;

        return $this;
    }

    public function setFromEmailAddressIdentityArn(?string $value): self
    {
        $this->fromEmailAddressIdentityArn = $value;

        return $this;
    }

    public function setListManagementOptions(?ListManagementOptions $value): self
    {
        $this->listManagementOptions = $value;

        return $this;
    }

    /**
     * @param string[] $value
     */
    public function setReplyToAddresses(array $value): self
    {
        $this->replyToAddresses = $value;

        return $this;
    }

    private function requestBody(): array
    {
        $payload = [];
        if (null !== $v = $this->fromEmailAddress) {
            $payload['FromEmailAddress'] = $v;
        }
        if (null !== $v = $this->fromEmailAddressIdentityArn) {
            $payload['FromEmailAddressIdentityArn'] = $v;
        }
        if (null !== $v = $this->destination) {
            $payload['Destination'] = $v->requestBody();
        }
        if (null !== $v = $this->replyToAddresses) {
            $index = -1;
            $payload['ReplyToAddresses'] = [];
            foreach ($v as $listValue) {
                ++$index;
                $payload['ReplyToAddresses'][$index] = $listValue;
            }
        }
        if (null !== $v = $this->feedbackForwardingEmailAddress) {
            $payload['FeedbackForwardingEmailAddress'] = $v;
        }
        if (null !== $v = $this->feedbackForwardingEmailAddressIdentityArn) {
            $payload['FeedbackForwardingEmailAddressIdentityArn'] = $v;
        }
        if (null === $v = $this->content) {
            throw new InvalidArgument(sprintf('Missing parameter "Content" for "%s". The value cannot be null.', __CLASS__));
        }
        $payload['Content'] = $v->requestBody();
        if (null !== $v = $this->emailTags) {
            $index = -1;
            $payload['EmailTags'] = [];
            foreach ($v as $listValue) {
                ++$index;
                $payload['EmailTags'][$index] = $listValue->requestBody();
            }
        }
        if (null !== $v = $this->configurationSetName) {
            $payload['ConfigurationSetName'] = $v;
        }
        if (null !== $v = $this->listManagementOptions) {
            $payload['ListManagementOptions'] = $v->requestBody();
        }

        return $payload;
    }
}
