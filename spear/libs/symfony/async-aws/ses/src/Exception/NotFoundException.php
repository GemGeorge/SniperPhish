<?php

namespace AsyncAws\Ses\Exception;

use AsyncAws\Core\Exception\Http\ClientException;

/**
 * The resource you attempted to access doesn't exist.
 */
final class NotFoundException extends ClientException
{
}
