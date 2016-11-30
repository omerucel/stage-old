<?php

namespace Application\Notification;

use Application\Model\Project;
use Application\Model\ProjectNotificationActions;

class Facade
{
    /**
     * @var Sender;
     */
    protected $sender;

    /**
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->sender = $sender;
    }

    /**
     * @param Project $project
     */
    public function sendProjectSetupStarting(Project $project)
    {
        $context = new Context();
        $context->setAction(ProjectNotificationActions::PROJECT_SETUP_STARTING);
        $context->setMessage($project->name . ' isimli projenin kurulumu başlatılıyor...');
        $context->setStatus(Context::STARTED);
        $this->sender->send($context);
    }

    /**
     * @param Project $project
     */
    public function sendProjectSetupFinished(Project $project)
    {
        $context = new Context();
        $context->setAction(ProjectNotificationActions::PROJECT_SETUP_FINISHED);
        $context->setMessage($project->name . ' isimli projenin kurulumu tamamlandı.');
        $context->setStatus(Context::COMPLETED);
        $this->sender->send($context);
    }

    /**
     * @param Project $project
     * @param $errorMessage
     */
    public function sendProjectSetupFailed(Project $project, $errorMessage)
    {
        $context = new Context();
        $context->setAction(ProjectNotificationActions::PROJECT_SETUP_FAILED);
        $context->setMessage(
            $project->name . ' isimli projenin kurulumu sırasında bir sorun oluştu. Hata:' . $errorMessage
        );
        $context->setStatus(Context::FAILED);
        $this->sender->send($context);
    }

    /**
     * @param Project $project
     */
    public function sendProjectStarting(Project $project)
    {
        $context = new Context();
        $context->setAction(ProjectNotificationActions::PROJECT_STARTING);
        $context->setMessage($project->name . ' isimli proje başlatılıyor...');
        $context->setStatus(Context::STARTED);
        $this->sender->send($context);
    }

    /**
     * @param Project $project
     */
    public function sendProjectStarted(Project $project)
    {
        $context = new Context();
        $context->setAction(ProjectNotificationActions::PROJECT_STARTED);
        $context->setMessage($project->name . ' isimli proje başlatıldı.');
        $context->setStatus(Context::COMPLETED);
        $this->sender->send($context);
    }

    /**
     * @param Project $project
     * @param $errorMessage
     */
    public function sendProjectStartFailed(Project $project, $errorMessage)
    {
        $context = new Context();
        $context->setAction(ProjectNotificationActions::PROJECT_START_FAILED);
        $context->setMessage($project->name . ' isimli proje başlatılırken bir sorun oluştu. Hata:' . $errorMessage);
        $context->setStatus(Context::FAILED);
        $this->sender->send($context);
    }

    /**
     * @param Project $project
     */
    public function sendProjectStopping(Project $project)
    {
        $context = new Context();
        $context->setAction(ProjectNotificationActions::PROJECT_STOPPING);
        $context->setMessage($project->name . ' isimli proje durduruluyor...');
        $context->setStatus(Context::STARTED);
        $this->sender->send($context);
    }

    /**
     * @param Project $project
     */
    public function sendProjectStopped(Project $project)
    {
        $context = new Context();
        $context->setAction(ProjectNotificationActions::PROJECT_STOPPED);
        $context->setMessage($project->name . ' isimli proje durduruldu.');
        $context->setStatus(Context::COMPLETED);
        $this->sender->send($context);
    }

    /**
     * @param Project $project
     * @param $errorMessage
     */
    public function sendProjectStopFailed(Project $project, $errorMessage)
    {
        $context = new Context();
        $context->setAction(ProjectNotificationActions::PROJECT_STOPPED);
        $context->setMessage($project->name . ' isimli proje durdurulurken bir sorun oluştu. Hata:' . $errorMessage);
        $context->setStatus(Context::FAILED);
        $this->sender->send($context);
    }
}
