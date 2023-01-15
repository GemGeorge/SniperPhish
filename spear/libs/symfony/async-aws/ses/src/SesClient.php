<?php

namespace AsyncAws\Ses;

use AsyncAws\Core\AbstractApi;
use AsyncAws\Core\AwsError\AwsErrorFactoryInterface;
use AsyncAws\Core\AwsError\JsonRestAwsErrorFactory;
use AsyncAws\Core\Configuration;
use AsyncAws\Core\RequestContext;
use AsyncAws\Ses\Exception\AccountSuspendedException;
use AsyncAws\Ses\Exception\BadRequestException;
use AsyncAws\Ses\Exception\LimitExceededException;
use AsyncAws\Ses\Exception\MailFromDomainNotVerifiedException;
use AsyncAws\Ses\Exception\MessageRejectedException;
use AsyncAws\Ses\Exception\NotFoundException;
use AsyncAws\Ses\Exception\SendingPausedException;
use AsyncAws\Ses\Exception\TooManyRequestsException;
use AsyncAws\Ses\Input\SendEmailRequest;
use AsyncAws\Ses\Result\SendEmailResponse;
use AsyncAws\Ses\ValueObject\Destination;
use AsyncAws\Ses\ValueObject\EmailContent;
use AsyncAws\Ses\ValueObject\ListManagementOptions;
use AsyncAws\Ses\ValueObject\MessageTag;

class SesClient extends AbstractApi
{
    /**
     * Sends an email message. You can use the Amazon SES API v2 to send the following types of messages:.
     *
     * @see https://docs.aws.amazon.com/ses/latest/APIReference/API_SendEmail.html
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-email-2019-09-27.html#sendemail
     *
     * @param array{
     *   FromEmailAddress?: string,
     *   FromEmailAddressIdentityArn?: string,
     *   Destination?: Destination|array,
     *   ReplyToAddresses?: string[],
     *   FeedbackForwardingEmailAddress?: string,
     *   FeedbackForwardingEmailAddressIdentityArn?: string,
     *   Content: EmailContent|array,
     *   EmailTags?: MessageTag[],
     *   ConfigurationSetName?: string,
     *   ListManagementOptions?: ListManagementOptions|array,
     *   @region?: string,
     * }|SendEmailRequest $input
     *
     * @throws TooManyRequestsException
     * @throws LimitExceededException
     * @throws AccountSuspendedException
     * @throws SendingPausedException
     * @throws MessageRejectedException
     * @throws MailFromDomainNotVerifiedException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function sendEmail($input): SendEmailResponse
    {
        $input = SendEmailRequest::create($input);
        $response = $this->getResponse($input->request(), new RequestContext(['operation' => 'SendEmail', 'region' => $input->getRegion(), 'exceptionMapping' => [
            'TooManyRequestsException' => TooManyRequestsException::class,
            'LimitExceededException' => LimitExceededException::class,
            'AccountSuspendedException' => AccountSuspendedException::class,
            'SendingPausedException' => SendingPausedException::class,
            'MessageRejected' => MessageRejectedException::class,
            'MailFromDomainNotVerifiedException' => MailFromDomainNotVerifiedException::class,
            'NotFoundException' => NotFoundException::class,
            'BadRequestException' => BadRequestException::class,
        ]]));

        return new SendEmailResponse($response);
    }

    protected function getAwsErrorFactory(): AwsErrorFactoryInterface
    {
        return new JsonRestAwsErrorFactory();
    }

    protected function getEndpointMetadata(?string $region): array
    {
        if (null === $region) {
            $region = Configuration::DEFAULT_REGION;
        }

        switch ($region) {
            case 'fips-us-east-1':
                return [
                    'endpoint' => 'https://email-fips.us-east-1.amazonaws.com',
                    'signRegion' => 'us-east-1',
                    'signService' => 'ses',
                    'signVersions' => ['v4'],
                ];
            case 'fips-us-west-2':
                return [
                    'endpoint' => 'https://email-fips.us-west-2.amazonaws.com',
                    'signRegion' => 'us-west-2',
                    'signService' => 'ses',
                    'signVersions' => ['v4'],
                ];
            case 'fips-us-gov-west-1':
                return [
                    'endpoint' => 'https://email-fips.us-gov-west-1.amazonaws.com',
                    'signRegion' => 'us-gov-west-1',
                    'signService' => 'ses',
                    'signVersions' => ['v4'],
                ];
        }

        return [
            'endpoint' => "https://email.$region.amazonaws.com",
            'signRegion' => $region,
            'signService' => 'ses',
            'signVersions' => ['v4'],
        ];
    }

    protected function getServiceCode(): string
    {
        @trigger_error('Using the client with an old version of Core is deprecated. Run "composer update async-aws/core".', \E_USER_DEPRECATED);

        return 'email';
    }

    protected function getSignatureScopeName(): string
    {
        @trigger_error('Using the client with an old version of Core is deprecated. Run "composer update async-aws/core".', \E_USER_DEPRECATED);

        return 'ses';
    }

    protected function getSignatureVersion(): string
    {
        @trigger_error('Using the client with an old version of Core is deprecated. Run "composer update async-aws/core".', \E_USER_DEPRECATED);

        return 'v4';
    }
}
