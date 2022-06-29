<?php

namespace AsyncAws\Ses\Exception;

use AsyncAws\Core\Exception\Http\ClientException;

/**
 * The message can't be sent because the sending domain isn't verified.
 */
final class MailFromDomainNotVerifiedException extends ClientException
{
}
