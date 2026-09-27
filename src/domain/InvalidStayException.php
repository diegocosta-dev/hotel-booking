<?php

declare(strict_types=1);

namespace diegocostadev\hotelbooking\domain;

use InvalidArgumentException;

/**
 * Thrown when check-in / check-out dates don't form a valid stay.
 */
final class InvalidStayException extends InvalidArgumentException
{
}
