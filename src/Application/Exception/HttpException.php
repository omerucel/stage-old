<?php

namespace Application\Exception;

use Exception;

abstract class HttpException extends \Exception
{
    /**
     * @var int
     */
    protected $httpStatusCode;

    /**
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message = 'An error occurred!', $code = 1, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * @param int $httpStatusCode
     */
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;
    }
}
