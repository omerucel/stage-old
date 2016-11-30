<?php

namespace Application\Notification;

class Context
{
    const FAILED = 'failed';
    const STARTED = 'started';
    const COMPLETED = 'completed';

    protected $action;
    protected $message;
    protected $status;

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return boolean
     */
    public function isFailed()
    {
        return $this->status == static::FAILED;
    }

    /**
     * @return boolean
     */
    public function isStarted()
    {
        return $this->status == static::STARTED;
    }

    /**
     * @return boolean
     */
    public function isCompleted()
    {
        return $this->status == static::COMPLETED;
    }
}
