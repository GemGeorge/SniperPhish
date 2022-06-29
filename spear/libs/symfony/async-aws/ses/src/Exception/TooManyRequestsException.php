<?php

namespace AsyncAws\Ses\Exception;

use AsyncAws\Core\Exception\Http\ClientException;

/**
 * Too many requests have been made to the operation.
 */
final class TooManyRequestsException extends ClientException
{
}
