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
     * @param mixed $json The JSON data.
     * @param bool $mustEncode Should the string be JSON-encoded before being sent ?
     * @param int $status The status code.
     * @param array $headers
     */
    public function __construct($json, bool $mustEncode = true, int $status = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        parent::__construct($status, $headers, $mustEncode ? json_encode($json): $json);
    }
}
