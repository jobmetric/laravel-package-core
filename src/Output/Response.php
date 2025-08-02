<?php

namespace JobMetric\PackageCore\Output;

/**
 * Class Response
 *
 * A standardized response wrapper used across the application or packages
 * to unify the structure of API and internal service responses.
 *
 * This class provides a consistent structure for returning success/failure states,
 * messages, data payloads, HTTP-like status codes, and optional validation or logic errors.
 *
 * @package JobMetric\PackageCore
 */
class Response
{
    /**
     * Create a new Response instance.
     *
     * @param bool $ok Whether the response indicates success (`true`) or failure (`false`).
     * @param string $message A human-readable message, typically used for feedback.
     * @param mixed|null $data Optional payload, can be any type of data (array, model, collection, etc.).
     * @param int $status A status code to indicate state. Can align with HTTP codes or internal logic.
     * @param array $errors Optional list of validation or logic errors.
     */
    public function __construct(
        public bool   $ok,
        public string $message = '',
        public mixed  $data = null,
        public int    $status = 200,
        public array  $errors = []
    )
    {
    }

    /**
     * Create a new static instance of the Response class.
     *
     * @param bool $ok Whether the operation was successful.
     * @param string $message An optional response message.
     * @param mixed|null $data Additional data payload.
     * @param int $status Optional status code, defaults to 200.
     * @param array $errors Optional error list for additional context.
     *
     * @return Response A fully constructed response object.
     */
    public static function make(bool $ok, string $message = '', mixed $data = null, int $status = 200, array $errors = []): Response
    {
        return new self($ok, $message, $data, $status, $errors);
    }
}
