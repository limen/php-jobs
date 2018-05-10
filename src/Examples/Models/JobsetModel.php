<?php
namespace Limen\Jobs\Examples\Models;

use Limen\Jobs\Contracts\JobsetModelInterface;

class JobsetModel implements JobsetModelInterface
{
    private $memory = [];

    public function getId()
    {
        return $this->getField('id', 0);
    }

    public function getName()
    {
        return $this->getField('name', '');
    }

    public function getStatus()
    {
        return $this->getField('status', 0);
    }

    public function updateStatus($status)
    {
        $this->setField('status', $status);
    }

    public function getCreatedAt()
    {
        return $this->getField('created_at', '2018-01-01 00:00:00');
    }

    public function getTryAt()
    {
        return $this->getField('try_at', '2018-01-01 00:00:00');
    }

    public function getJobIds()
    {
        return [];
    }

    public function setId($id)
    {
        $this->setField('id', $id);
    }

    public function setTryAt($tryAt)
    {
        $this->setField('try_at', $tryAt);
    }

    public function setStatus($status)
    {
        $this->setField('status', $status);
    }

    public function setTriedCount($count)
    {
        $this->setField('tried_count', $count);
    }

    public function setName($name)
    {
        $this->setField('name', $name);
    }

    public function setAttribute($attr, $value)
    {
        $this->setField($attr, $value);
    }

    public function getAttribute($attr)
    {
        return $this->getField($attr);
    }

    public function persist()
    {
        return true;
    }

    private function getField($field, $default = null)
    {
        return isset($this->memory[$field]) ? $this->memory[$field] : $default;
    }

    private function setField($field, $value)
    {
        $this->memory[$field] = $value;
    }

    public static function findById($id)
    {
        $model = new static();
        $model->setId($id);

        return $model;
    }
}