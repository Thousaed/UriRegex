<?php

namespace Thousaed\UriRegex\Exceptions;
use Exception;

class UnclosedScopeException extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null) {
      parent::__construct($message, $code, $previous);
    }
} 