<?php

namespace Application\Controller;

use Application\Model\Permission;
use Application\Model\Project;
use Application\Model\ProjectNotification;
use Application\Pdo\Exception\RecordNotFoundException;
use Application\Project\BackgroundTaskExecutor;
use Cocur\Slugify\Slugify;

class ProjectSaveController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->checkPermission(Permission::PERM_PROJECT_SAVE);
        /**
         * @var \PDO $pdo
         */
        $pdo = $this->getDi()->get('pdo');
        try {
            $pdo->beginTransaction();
            $response = $this->trySaveAction();
            $pdo->commit();
        } catch (\Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
        return $response;
    }

    protected function trySaveAction()
    {
        $id = $this->getRequest()->get('id');
        $copyId = $this->getRequest()->get('copy_id');
        $files = array();
        $notifications = array();
        if ($id > 0) {
            try {
                $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($id);
            } catch (RecordNotFoundException $exception) {
                return $this->redirect('/projects');
            }
        } elseif ($copyId > 0) {
            try {
                $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($copyId);
            } catch (RecordNotFoundException $exception) {
                $project = new Project($this->getDi());
            }
        } else {
            $project = new Project($this->getDi());
            $files[] = [
                'name' => 'docker-compose.yml',
                'content' => file_get_contents($this->getConfig()->base_path . '/websites/docker-compose.yml.template')
            ];
            $files[] = [
                'name' => 'Dockerfile',
                'content' => file_get_contents($this->getConfig()->base_path . '/websites/Dockerfile.template')
            ];
        }
        if ($project->id > 0 && $this->getUser()->isAllowedProject($project->id) == false) {
            return $this->redirect('/projects');
        }
        foreach ($project->getFiles() as $fileName => $fileContent) {
            $files[] = [
                'name' => $fileName,
                'content' => $fileContent
            ];
        }
        /**
         * @var ProjectNotification $notification
         */
        foreach ($project->getNotifications() as $notification) {
            $notifications[] = [
                'name' => $notification->name,
                'type' => $notification->type,
                'data' => json_decode($notification->data)
            ];
        }
        if ($copyId > 0) {
            $project->id = 0;
            $project->name = $project->name . ' Copy';
        }
        if ($project->id == 0) {
            $project->vhost = file_get_contents($this->getConfig()->base_path . '/nginx.conf.d/vhost.template');
            $project->port = 80;
        }
        $templateParams = array(
            'form_messages' => array(),
            'id' => $id,
            'page' => 'projects',
            'form_data' => array(
                'name' => $project->name,
                'vhost' => $project->vhost,
                'port' => $project->port,
                'files' => json_encode($files),
                'notifications' => json_encode($notifications),
                'public_key' => $project->public_key
            )
        );
        if ($this->getRequest()->isMethod('POST')) {
            $name = trim($this->getRequest()->get('name'));
            $vhost = trim($this->getRequest()->get('vhost'));
            $port = intval($this->getRequest()->get('port'));
            $fileNames = $this->getRequest()->get('file_name');
            $fileContents = $this->getRequest()->get('file_content');
            $notificationNames = $this->getRequest()->get('notification_name');
            $notificationData = $this->getRequest()->get('notification_data');
            $publicKey = $this->getRequest()->get('public_key');
            if (is_array($fileNames) == false) {
                $fileNames = array();
            }
            if (is_array($fileContents) == false) {
                $fileContents = array();
            }
            if (count($fileNames) != count($fileContents)) {
                $fileNames = array();
                $fileContents = array();
            }
            $files = array();
            foreach ($fileNames as $index => $fileName) {
                $files[] = [
                    'name' => $fileName,
                    'content' => $fileContents[$index]
                ];
            }
            if (is_array($notificationNames) == false) {
                $notificationNames = array();
            }
            if (is_array($notificationData) == false) {
                $notificationData = array();
            }
            if (count($notificationNames) != count($notificationData)) {
                $notificationNames = array();
                $notificationData = array();
            }
            $notifications = array();
            foreach ($notificationNames as $index => $notificationName) {
                $notifications[] = [
                    'name' => $notificationName,
                    'type' => 'slack',
                    'data' => json_decode($notificationData[$index])
                ];
            }
            $templateParams['form_data'] = array(
                'name' => $name,
                'files' => json_encode($files),
                'notifications' => json_encode($notifications),
                'vhost' => $vhost,
                'port' => $port,
                'public_key' => $publicKey
            );

            if ($name == '') {
                $templateParams['form_messages'][] = array(
                    'message' => 'Lütfen proje adı alanını boş bırakmayınız.',
                    'type' => 'danger'
                );
                $templateParams['has_name_error'] = true;
            } elseif ($this->getMapperContainer()->getProjectMapper()->isProjectNameUsing($name, $id)) {
                $templateParams['form_messages'][] = array(
                    'message' => 'Girilen proje adı kullanılmakta. Lütfen başka bir proje adı giriniz.',
                    'type' => 'danger'
                );
                $templateParams['has_name_error'] = true;
            }

            if ($publicKey == '') {
                $templateParams['form_messages'][] = array(
                    'message' => 'Lütfen açık anahtar alanını boş bırakmayınız.',
                    'type' => 'danger'
                );
                $templateParams['has_public_key_error'] = true;
            } elseif ($this->getMapperContainer()->getProjectMapper()->isPublicKeyUsing($publicKey, $id)) {
                $templateParams['form_messages'][] = array(
                    'message' => 'Girilen açık anahtar kullanılmakta. Lütfen başka bir açık anahtar giriniz.',
                    'type' => 'danger'
                );
                $templateParams['has_public_key_error'] = true;
            }

            if (empty($templateParams['form_messages'])) {
                $slugify = new Slugify(['rulesets' => ['default', 'turkish']]);
                $oldProjectDir = $project->getDirectory();
                $project->name = $name;
                $project->folder = $slugify->slugify($name);
                $project->vhost = $vhost;
                $project->port = $port;
                $project->public_key = $publicKey;
                $this->getMapperContainer()->getProjectMapper()->save($project);
                $this->getMapperContainer()->getProjectMapper()->updateProjectFiles($project->id, $files);
                $this->getMapperContainer()->getProjectMapper()
                    ->updateProjectNotifications($project->id, $notifications);
                if ($project->id != $id) {
                    $this->getMapperContainer()->getUserMapper()->setUserProject($this->getUser()->id, $project->id);
                }
                $this->newActivity(Permission::PERM_PROJECT_SAVE, array('affected_project_id' => $project->id));
                $taskExecutor = new BackgroundTaskExecutor($this->getDi());
                $taskExecutor->executeSetupTask($project->id, $oldProjectDir);
                if ($id == 0) {
                    $templateParams['form_data'] = array(
                        'name' => '',
                        'folder' => '',
                        'vhost' => file_get_contents($this->getConfig()->base_path . '/nginx.conf.d/vhost.template'),
                        'port' => 80,
                        'files' => json_encode([]),
                        'notifications' => json_encode([]),
                        'public_key' => ''
                    );
                }
                $templateParams['form_messages'][] = array(
                    'message' => 'Proje kaydedildi.',
                    'type' => 'success'
                );
            }
        }
        return $this->render('projects/save.twig', $templateParams);
    }
}
