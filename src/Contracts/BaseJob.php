<?php
/**
 * Author: LI Mengxiang
 * Email: limengxiang876@gmail.com
 * Date: 2018/3/17
 */

namespace Limen\Jobs\Contracts;

use Limen\Jobs\Helper;
use Limen\Jobs\JobsConst;

/**
 *
 * Class BaseJob
 * @package Limen\Jobs\Contracts
 */
abstract class BaseJob
{
    /** @var JobModelInterface */
    protected $model;

    /**
     *  The jobs should be classified by their names
     *
     * @var string job name
     */
    protected $name;

    /**
     * @param $jobsetId
     * @param array $attributes
     * @return static
     */
    public static function make($jobsetId, $attributes = [])
    {
        $job = new static();
        $model = $job->makeModel($jobsetId, $attributes);
        $job->setModel($model);

        return $job;
    }

    public static function get($jobsetId)
    {
        $job = new static();
        $model = $job->findModel($jobsetId);
        $job->setModel($model);

        return $job;
    }

    /**
     * @param $jobsetId
     * @param array $attributes
     * @return JobModelInterface
     */
    abstract protected function makeModel($jobsetId, $attributes = []);

    /**
     * @param JobModelInterface $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return JobModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function getJobsetId()
    {
        return $this->model->getJobsetId();
    }

    /**
     * Do your business here.
     * Return the status @see JobsConst
     * Or throw exception
     *
     * @see JobConst
     * @return int
     * @throws \Exception
     */
    abstract protected function doStuff();

    /**
     * @param $jobsetId
     * @return JobModelInterface
     */
    abstract protected function findModel($jobsetId);

    /**
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->isFinished() || $this->isFailed()) {
            return;
        }
        elseif ($this->isCanceled() || !$this->isInTime()) {
            return;
        }

        try {
            // do your real business
            $doStatus = $this->doStuff();
            if ($doStatus === JobsConst::JOB_STATUS_FAILED) {
                // need to increase retry count and set next try time
                $this->failed();
            } else {
                // just update status
                $this->model->updateStatus($doStatus);
            }
        } catch (\Exception $e) {
            // exception means failed
            $this->failed();
            throw $e;
        }
    }

    /**
     * 取消任务
     *
     * @return mixed
     */
    public function cancel()
    {
        return $this->model->updateStatus(JobsConst::JOB_STATUS_CANCELED);
    }

    public function setId($id)
    {
        $this->model->setId($id);

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->model->getId();
    }

    /**
     * job名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->model->getName();
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->model->getStatus();
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->getStatus() == JobsConst::JOB_STATUS_FINISHED;
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return $this->getStatus() == JobsConst::JOB_STATUS_FAILED;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->getStatus() == JobsConst::JOB_STATUS_CANCELED;
    }

    /**
     * @return bool
     */
    public function isWaitingRetry()
    {
        return $this->getStatus() == JobsConst::JOB_STATUS_WAITING_RETRY;
    }

    /**
     * @return bool
     */
    public function isWaitingFeedback()
    {
        return $this->getStatus() == JobsConst::JOB_STATUS_WAITING_FEEDBACK;
    }

    /**
     * check the try at is before now
     */
    public function isInTime()
    {
        return Helper::datetimeLE($this->getTryAt(), date('Y-m-d H:i:s'));
    }

    /**
     * update to finished
     */
    protected function finished()
    {
        return $this->model->updateStatus(JobsConst::JOB_STATUS_FINISHED);
    }

    /**
     * After failed we need to update tried count
     * and check whether the tried count have reached to max retry count
     */
    protected function failed()
    {
        $retryCount = $this->model->getTriedCount() + 1;

        if ($retryCount >= $this->getMaxRetryCount()) {
            $this->model->setStatus(JobsConst::JOB_STATUS_FAILED);
        } else {
            $this->model->setStatus(JobsConst::JOB_STATUS_WAITING_RETRY);
            $this->model->setTryAt($this->getNextTryTime());
        }
        $this->model->setTriedCount($retryCount);

        return $this->model->persist();
    }

    /**
     * @return mixed
     */
    public function getTryAt()
    {
        return $this->model->getTryAt();
    }

    /**
     * @return false|string
     */
    protected function getFirstTryAt()
    {
        return Helper::nowDatetime();
    }

    /**
     * @return int
     */
    protected function getRetriedCount()
    {
        return $this->model->getTriedCount();
    }

    /**
     * The job's max retry count before failed
     *
     * @return int
     */
    protected function getMaxRetryCount()
    {
        return 3;
    }

    /**
     * Next retry time, default is now
     */
    protected function getNextTryTime()
    {
        return date('Y-m-d H:i:s', time());
    }
}