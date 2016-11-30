<?php

namespace Application\Model;

class ProjectNotificationSettings
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param $jsonEncodedData
     */
    final public function __construct($jsonEncodedData)
    {
        $this->data = json_decode($jsonEncodedData, true);
        if (is_array($this->data) == false) {
            $this->data = [];
        }
        if (array_key_exists('accepted_actions', $this->data) == false) {
            $this->data['accepted_actions'] = [];
        }
    }

    /**
     * @param $action
     * @return bool
     */
    public function isAcceptedAction($action)
    {
        return in_array($action, $this->data['accepted_actions']);
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function getSettingValue($key, $default = null)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }
}
