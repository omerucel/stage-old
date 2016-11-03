<?php

namespace Application\Controller\Webhook;

use Application\Controller\BaseController;
use Application\Exception\BadRequestException;
use Application\Project\BackgroundTaskExecutor;
use Symfony\Component\HttpFoundation\Response;

class SetupController extends BaseController
{
    /**
     * @param array $params
     * @return Response
     */
    public function handle(array $params = [])
    {
        $this->getResponse()->headers->set('Content-Type', 'application/json; charset=utf-8');
        try {
            $publicKey = $this->getRequest()->get('public_key');
            if (trim($publicKey) == '') {
                throw new BadRequestException();
            }
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectByPublicKey($publicKey);
        } catch (\Exception $exception) {
            $this->getResponse()->setStatusCode(401);
            return $this->getResponse();
        }
        $taskExecutor = new BackgroundTaskExecutor($this->getDi());
        $taskExecutor->executeSetupTask($project->id, $project->getDirectory());
        return $this->getResponse();
    }
}
