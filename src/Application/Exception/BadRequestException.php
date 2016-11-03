<?php

namespace Application\Exception;

use Exception;

class BadRequestException extends HttpException
{
    /**
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message = 'Bad request!', $code = 4, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setHttpStatusCode(400);
    }
}
