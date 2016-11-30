<?php

namespace Application\Notification;

use Application\Model\Project;
use Application\Model\ProjectNotification;
use Application\Model\ProjectNotificationTypes;
use Application\Notification\Slack\SenderAdapter;
use Psr\Log\LoggerInterface;

class Sender
{
    /**
     * @var Project
     */
    protected $project;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Project $project
     * @param LoggerInterface $logger
     */
    public function __construct(Project $project, LoggerInterface $logger)
    {
        $this->project = $project;
        $this->logger = $logger;
    }

    /**
     * @param Context $context
     */
    public function send(Context $context)
    {
        try {
            $this->trySend($context);
        } catch (\Exception $exception) {
            $this->logger->error($exception);
        }
    }

    /**
     * @param Context $context
     */
    protected function trySend(Context $context)
    {
        foreach ($this->project->getNotifications() as $notification) {
            $this->getAdapter($notification)->send($context);
        }
    }

    /**
     * @param ProjectNotification $notification
     * @return SenderAdapter
     * @throws \Exception
     */
    protected function getAdapter(ProjectNotification $notification)
    {
        switch ($notification->type) {
            case ProjectNotificationTypes::SLACK:
                return new SenderAdapter($notification);
                break;
            default:
                throw new \Exception('Undefined notification type!');
        }
    }
}
