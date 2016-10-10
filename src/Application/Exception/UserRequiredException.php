<?php

namespace Application\Exception;

use Exception;

class UserRequiredException extends HttpException
{
    /**
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message = 'User required!', $code = 2, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setHttpStatusCode(401);
    }
}
