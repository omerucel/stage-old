<?php

namespace Application\Notification;

use Application\Model\ProjectNotification;
use Application\Model\ProjectNotificationSettings;

abstract class SenderAdapterAbstract
{
    /**
     * @var ProjectNotification
     */
    protected $notification;

    /**
     * @param ProjectNotification $notification
     */
    public function __construct(ProjectNotification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @param Context $context
     */
    abstract public function send(Context $context);

    /**
     * @return ProjectNotificationSettings
     */
    protected function getSettings()
    {
        return $this->notification->getSettings();
    }
}
