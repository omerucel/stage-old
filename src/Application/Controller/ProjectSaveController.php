<?php

namespace Application\Controller;

use Application\Docker;
use Application\Model\Permission;
use Application\Model\Project;
use Application\Pdo\Exception\RecordNotFoundException;
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
        }
        if ($project->id > 0 && $this->getUser()->isAllowedProject($project->id) == false) {
            return $this->redirect('/projects');
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
        if ($this->getRequest()->isMethod('POST')) {
            $name = trim($this->getRequest()->get('name'));
            $fileNames = $this->getRequest()->get('file_name');
            $fileContents = $this->getRequest()->get('file_content');
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
                $isNewProject = $project->id == 0;
                $oldProjectDir = $project->getDirectory();
                $docker = new Docker($this->getDi());
                $slugify = new Slugify(['rulesets' => ['default', 'turkish']]);
                if ($isNewProject == false) {
                    $response = $docker->stop($oldProjectDir);
                    if ($response['exitCode'] !== 0) {
                        $templateParams['form_messages'][] = [
                            'message' => 'Sunucular durdurulamadı. ' . implode(' ', $response['output']),
                            'type' => 'warning'
                        ];
                    } else {
                        $templateParams['form_messages'][] = [
                            'message' => 'Sunucular başarıyla durduruldu.',
                            'type' => 'success'
                        ];
                    }
                }
                $project->name = $name;
                $project->folder = $slugify->slugify($name);
                $this->getMapperContainer()->getProjectMapper()->save($project);
                $this->getMapperContainer()->getProjectMapper()->updateProjectFiles($project->id, $files);
                $this->getMapperContainer()->getUserActivityMapper()->newActivity(
                    $this->getUser()->id,
                    Permission::PERM_PROJECT_SAVE,
                    array('affected_project_id' => $project->id)
                );
                $newProjectDir = $project->getDirectory();
                if ($isNewProject == false && is_dir($oldProjectDir) && $oldProjectDir != $newProjectDir) {
                    exec('mv ' . $oldProjectDir . ' ' . $newProjectDir);
                }
                if (is_dir($newProjectDir) == false) {
                    mkdir($newProjectDir, 0777);
                }
                foreach ($files as $file) {
                    file_put_contents($newProjectDir . '/' . $file['name'], $file['content']);
                }
                $docker->build($project->getDirectory());
                $response = $docker->start($newProjectDir);
                if ($response['exitCode'] !== 0) {
                    $templateParams['form_messages'][] = [
                        'message' => 'Sunucular başlatılamadı. ' . implode(' ', $response['output']),
                        'type' => 'warning'
                    ];
                } else {
                    $templateParams['form_messages'][] = [
                        'message' => 'Sunucular başarıyla başlatıldı',
                        'type' => 'success'
                    ];
                }

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
}
