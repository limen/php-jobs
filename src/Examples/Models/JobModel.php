<?php
namespace Limen\Jobs\Examples\Models;

use Limen\Jobs\Contracts\JobModelInterface;

class JobModel implements JobModelInterface
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

    public function getTriedCount()
    {
        return $this->getField('tried_count', 0);
    }

    public function getJobsetId()
    {
        // TODO: Implement getJobSetId() method.
    }

    public function setId($id)
    {
        $this->setField('id', $id);
    }

    public function setJobsetId($jobSetId)
    {
        $this->setField('job_set_id', $jobSetId);
    }

    public static function findByJobsetIdAndJobName($jobsetId, $jobName)
    {
        $model = new static();
        $model->setJobsetId($jobsetId);
        $model->setName($jobName);

        return $model;
    }

    public static function findById($jobId)
    {
        $model = new static();
        $model->setId($jobId);

        return $model;
    }
}