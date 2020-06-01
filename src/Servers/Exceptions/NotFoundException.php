<?php

namespace Yoke\Servers\Exceptions;

use Symfony\Component\Console\Exception\RuntimeException;

/**
 * Class NotFoundException.
 *
 * Exception when a given resource is not found or there are no resources into a
 * given collection.
 */
class NotFoundException extends RuntimeException
{
    //
}
