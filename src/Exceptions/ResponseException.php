<?php

namespace codechap\ai\Exceptions;

/**
 * Custom exception class for errors related to API responses.
 *
 * This allows for more specific error handling for issues encountered
 * during or after an API request, such as unexpected HTTP status codes
 * or problems decoding the response body.
 */
class ResponseException extends \Exception
{
    /**
     * Constructor for ResponseException.
     *
     * @param string $message The Exception message to throw.
     * @param int $code The Exception code.
     * @param \Throwable|null $previous The previous throwable used for the exception chaining.
     */
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        // Call the parent Exception constructor
        parent::__construct($message, $code, $previous);
    }

    /**
     * String representation of the exception.
     *
     * @return string The string representation of the exception.
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    // You can add more specific methods or properties here later if needed.
    // For example, you might want to store the HTTP status code or response body.
}
