<?php

namespace Fram\Response;

use GuzzleHttp\Psr7\Response;

/**
 * Represents a JSON response.
 */
class JsonResponse extends Response
{
    /**
     * Constructor.
     *
     * @param mixed $encodedJson The JSON data to return.
     * @param int $status The status code.
     * @param array $headers
     */
    public function __construct($encodedJson, int $status = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        parent::__construct($status, $headers, $encodedJson);
    }
}
