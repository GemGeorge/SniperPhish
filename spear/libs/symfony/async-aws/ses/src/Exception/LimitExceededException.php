<?php

namespace AsyncAws\Ses\Exception;

use AsyncAws\Core\Exception\Http\ClientException;

/**
 * There are too many instances of the specified resource type.
 */
final class LimitExceededException extends ClientException
{
}
