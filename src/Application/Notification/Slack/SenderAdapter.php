<?php

namespace Application\Notification\Slack;

use Application\Notification\Context;
use Application\Notification\SenderAdapterAbstract;

class SenderAdapter extends SenderAdapterAbstract
{
    /**
     * @param Context $context
     */
    public function send(Context $context)
    {
        if ($this->getSettings()->isAcceptedAction($context->getAction())) {
            $this->httpPost($context);
        }
    }

    /**
     * @param Context $context
     */
    protected function httpPost(Context $context)
    {
        if ($context->isFailed()) {
            $color = 'danger';
        } elseif ($context->isCompleted()) {
            $color = 'good';
        } else {
            $color = 'warning';
        }
        $postData = json_encode([
            'username' => 'stage',
            'icon_emoji' => ':rocket:',
            'attachments' => [[
                'text' => $context->getMessage(),
                'color' => $color
            ]]
        ]);
        $curl = curl_init($this->getUrl());
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ));
        $result = curl_exec($curl);
        error_log(json_encode($result));
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getUrl()
    {
        $url = trim($this->getSettings()->getSettingValue('url'));
        if ($url == '') {
            throw new \Exception('Invalid url!');
        }
        return $url;
    }
}
