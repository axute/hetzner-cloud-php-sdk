<?php /** @noinspection ALL */

namespace LKDev\HetznerCloud;

use Exception;
use Throwable;

class APIException extends Exception
{

    public function __construct(public APIResponse $response, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $code, $previous);
    }

    public function getApiResponse(): APIResponse
    {
        return $this->response;
    }
}
