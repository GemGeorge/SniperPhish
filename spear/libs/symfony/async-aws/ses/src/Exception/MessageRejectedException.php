<?php

namespace AsyncAws\Ses\Exception;

use AsyncAws\Core\Exception\Http\ClientException;

/**
 * The message can't be sent because it contains invalid content.
 */
final class MessageRejectedException extends ClientException
{
}
