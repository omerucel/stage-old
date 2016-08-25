<?php

namespace Application\Controller;

use Application\Docker;
use Application\Model\Permission;
use Application\Model\Project;
use Application\Nginx;
use Application\Pdo\Exception\RecordNotFoundException;

class ProjectsController extends BaseController
{
    public function indexAction()
    {
        if ($this->getUser() == null) {
            return $this->response->redirect('/login/');
        }
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_LIST) == false) {
            return $this->response->redirect('/');
        }
        $currentPage = intval($this->request->get('page'));
        $pager = $this->getMapperContainer()->getProjectMapper()
            ->paginate(array('name' => 'asc'), $currentPage);
        if ($currentPage > $pager->getLastPage()) {
            return $this->response->redirect('/projects?page=' . $pager->getLastPage());
        }
        $templateParams = array(
            'page' => 'projects',
            'pager' => $pager
        );
        return $this->render('/projects/list.twig', $templateParams);
    }

    public function saveAction()
    {
        /**
         * @var \PDO $pdo
         */
        $pdo = $this->getDI()->get('pdo');
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
        if ($this->getUser() == null) {
            return $this->response->redirect('/login/');
        }
        $id = $this->request->get('id');
        $copyId = $this->request->get('copy_id');
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_SAVE) == false) {
            return $this->response->redirect('/');
        }
        if ($id > 0) {
            try {
                $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($id);
            } catch (RecordNotFoundException $exception) {
                return $this->response->redirect('/projects');
            }
        } else if ($copyId > 0) {
            try {
                $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($copyId);
            } catch (RecordNotFoundException $exception) {
                $project = new Project($this->di);
            }
        } else {
            $project = new Project($this->di);
        }
        $files = array();
        foreach ($project->getFiles() as $fileName => $fileContent) {
            $files[] = [
                'name' => $fileName,
                'content' => $fileContent
            ];
        }
        if ($copyId > 0) {
            $project->id = 0;
            $project->name = $project->name . ' Copy';
        }
        $templateParams = array(
            'form_messages' => array(),
            'id' => $id,
            'page' => 'projects',
            'form_data' => array(
                'name' => $project->name,
                'files' => json_encode($files)
            )
        );
        if ($this->request->isPost()) {
            $name = trim($this->request->get('name'));
            $fileNames = $this->request->get('file_name');
            $fileContents = $this->request->get('file_content');
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
            $templateParams['form_data'] = array(
                'name' => $name,
                'files' => json_encode($files),
            );

            if ($this->getMapperContainer()->getProjectMapper()->isProjectNameUsing($name, $id) != null) {
                $templateParams['form_messages'][] = array(
                    'message' => 'Girilen proje adı kullanılmakta. Lütfen başka bir proje adı giriniz.',
                    'type' => 'danger'
                );
                $templateParams['has_name_error'] = true;
            }

            if ($name == '') {
                $templateParams['form_messages'][] = array(
                    'message' => 'Lütfen proje adı alanını boş bırakmayınız.',
                    'type' => 'danger'
                );
                $templateParams['has_name_error'] = true;
            }

            if (empty($templateParams['form_messages'])) {
                $project->name = $name;
                $this->getMapperContainer()->getProjectMapper()->save($project);
                $this->getMapperContainer()->getProjectMapper()->updateProjectFiles($project->id, $files);
                $this->getMapperContainer()->getUserActivityMapper()->newActivity(
                    $this->getUser()->id,
                    Permission::PERM_PROJECT_SAVE,
                    array('affected_project_id' => $project->id)
                );

                $message = 'Proje kaydedildi.';
                if ($id == 0) {
                    $templateParams['form_data'] = array(
                        'name' => '',
                        'folder' => '',
                        'files' => json_encode([])
                    );
                }
                $templateParams['form_messages'][] = array(
                    'message' => $message,
                    'type' => 'success'
                );
            }
        }
        return $this->render('projects/save.twig', $templateParams);
    }

    public function setup_ServerAction()
    {
        if ($this->getUser() == null) {
            return $this->response->redirect('/login/');
        }
        $id = $this->request->get('id');
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_SERVER_SETUP) == false) {
            return $this->response->redirect('/');
        }
        try {
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($id);
        } catch (RecordNotFoundException $exception) {
            return $this->response->redirect('/projects');
        }
        $projectDir = $this->getConfig()->base_path . '/websites/' . $project->name;
        if (is_dir($projectDir) == false) {
            mkdir($projectDir, 0777);
        }
        foreach ($project->getFiles() as $fileName => $fileContent) {
            file_put_contents($projectDir . '/' . $fileName, $fileContent);
        }
        $docker = new Docker($this->di);
        $docker->start($projectDir);
        return $this->response->redirect('/projects/server?id=' . $project->id);
    }

    public function serverAction()
    {
        if ($this->getUser() == null) {
            return $this->response->redirect('/login/');
        }
        $id = $this->request->get('id');
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_SERVER) == false) {
            return $this->response->redirect('/');
        }
        try {
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($id);
        } catch (RecordNotFoundException $exception) {
            return $this->response->redirect('/projects');
        }
        $projectDir = $this->getConfig()->base_path . '/websites/' . $project->name;
        $docker = new Docker($this->di);
        $containers = $docker->getContainersInfo($projectDir);
        $templateParams = [
            'project' => $project,
            'containers' => $containers
        ];
        return $this->render('projects/server.twig', $templateParams);
    }

    public function start_ServerAction()
    {
        $this->response->setContentType('application/json', 'utf-8');
        if ($this->getUser() == null) {
            $this->response->setStatusCode(401);
            return $this->response;
        }
        $id = $this->request->get('id');
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_SERVER_START) == false) {
            $this->response->setStatusCode(401);
            return $this->response;
        }
        try {
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($id);
        } catch (RecordNotFoundException $exception) {
            $this->response->setStatusCode(401);
            return $this->response;
        }
        $projectDir = $this->getConfig()->base_path . '/websites/' . $project->name;
        $docker = new Docker($this->di);
        $response = $docker->start($projectDir);
        if ($response['exitCode'] == 0) {
            $this->response->setStatusCode(200);
        } else {
            $this->response->setStatusCode(500);
        }
        return $this->response;
    }

    public function stop_ServerAction()
    {
        $this->response->setContentType('application/json', 'utf-8');
        if ($this->getUser() == null) {
            $this->response->setStatusCode(401);
            return $this->response;
        }
        $id = $this->request->get('id');
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_SERVER_STOP) == false) {
            $this->response->setStatusCode(401);
            return $this->response;
        }
        try {
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($id);
        } catch (RecordNotFoundException $exception) {
            $this->response->setStatusCode(401);
            return $this->response;
        }
        $projectDir = $this->getConfig()->base_path . '/websites/' . $project->name;
        $docker = new Docker($this->di);
        $response = $docker->stop($projectDir);
        if ($response['exitCode'] == 0) {
            $this->response->setStatusCode(200);
        } else {
            $this->response->setStatusCode(500);
        }
        return $this->response;
    }

    public function inspect_ServerAction()
    {
        $this->response->setContentType('application/json', 'utf-8');
        if ($this->getUser() == null) {
            $this->response->setStatusCode(401);
            $this->response->setContent('User not found');
            return $this->response;
        }
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_SERVER_INSPECT) == false) {
            $this->response->setStatusCode(401);
            $this->response->setContent('Permission denied');
            return $this->response;
        }
        $containerId = $this->request->get('container_id');
        $docker = new Docker($this->di);
        $response = $docker->inspect($containerId);
        if ($response['exitCode'] == 0) {
            $this->response->setStatusCode(200);
            $this->response->setContent($response['output'][0]);
        } else {
            $this->response->setStatusCode(500);
        }
        return $this->response;
    }

    public function get_VhostAction()
    {
        $this->response->setContentType('application/json', 'utf-8');
        if ($this->getUser() == null) {
            $this->response->setStatusCode(401);
            $this->response->setContent('User not found');
            return $this->response;
        }
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_SERVER_VHOST_GET) == false) {
            $this->response->setStatusCode(401);
            $this->response->setContent('Permission denied');
            return $this->response;
        }
        $id = $this->request->get('id');
        try {
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($id);
        } catch (RecordNotFoundException $exception) {
            $this->response->setStatusCode(401);
            return $this->response;
        }
        $vhostFile = $this->getConfig()->base_path . '/nginx.conf.d/project_' . $project->id . '.conf';
        if (is_file($vhostFile)) {
            $this->response->setStatusCode(200);
            $this->response->setContent(file_get_contents($vhostFile));
        } else {
            $this->response->setStatusCode(404);
        }
        return $this->response;
    }

    public function save_VhostAction()
    {
        $this->response->setContentType('application/json', 'utf-8');
        if ($this->getUser() == null) {
            $this->response->setStatusCode(401);
            $this->response->setContent('User not found');
            return $this->response;
        }
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_SERVER_VHOST_GET) == false) {
            $this->response->setStatusCode(401);
            $this->response->setContent('Permission denied');
            return $this->response;
        }
        $id = $this->request->get('id');
        $content = $this->request->get('content');
        try {
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($id);
        } catch (RecordNotFoundException $exception) {
            $this->response->setStatusCode(401);
            return $this->response;
        }
        $vhostFile = $this->getConfig()->base_path . '/nginx.conf.d/project_' . $project->id . '.conf';
        file_put_contents($vhostFile, $content);
        $nginx = new Nginx($this->di);
        $response = $nginx->restart();
        if ($response['exitCode'] == 0) {
            $this->response->setStatusCode(200);
        } else {
            $this->response->setStatusCode(500);
        }
        return $this->response;
    }
}
