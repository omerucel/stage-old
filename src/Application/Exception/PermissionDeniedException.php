<?php

namespace Application\Exception;

use Exception;

class PermissionDeniedException extends HttpException
{
    /**
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message = 'Permission denied!', $code = 3, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setHttpStatusCode(403);
    }
}
