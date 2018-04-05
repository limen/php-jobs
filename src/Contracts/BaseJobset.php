<?php
/**
 * Author: LI Mengxiang
 * Email: limengxiang876@gmail.com
 * Date: 2018/3/17
 */

namespace Limen\Jobs\Contracts;

use Limen\Jobs\Examples\Models\JobsetModel;
use Limen\Jobs\Helper;
use Limen\Jobs\JobsConst;

/**
 * There can be one or more jobs inside a jobset.
 * The jobset's try at time is decided by the earliest one of its jobs.
 *
 * Class BaseJobset
 * @package Limen\Jobs
 */
abstract class BaseJobset
{
    /** @var string jobset name */
    protected $name;

    /** @var JobsetModelInterface */
    protected $model;

    /** @var BaseJob[] */
    protected $jobs;

    /**
     * Make new jobset
     *
     * @return BaseJobset
     */
    public static function make()
    {
        $jobset = new static();

        // make jobset model
        $model = $jobset->makeModel();
        // update local model
        $jobset->setModel($model);
        // make job models and init job instances
        $jobset->makeJobs();
        // set jobset try at
        $jobsetTryAt = $jobset->getJobsEarliestTryAt();
        $jobset->model->setTryAt($jobsetTryAt);
        $jobset->model->persist();

        return $jobset;
    }

    /**
     * Get an existed jobset
     *
     * @param $id
     * @return static
     */
    public static function get($id)
    {
        $jobset = new static();

        // find jobset model
        $model = $jobset->findModel($id);
        // update local model
        $jobset->setModel($model);
        // Init job instances
        $jobset->initJobs();

        return $jobset;
    }

    /**
     * @return JobsetModelInterface
     */
    protected function makeModel()
    {
        $model = new JobsetModel();
        $model->setName($this->name);
        $model->setStatus(JobsConst::JOB_SET_STATUS_DEFAULT);
        $model->persist();

        return $model;
    }

    /**
     * @param $id
     * @return JobsetModelInterface
     */
    abstract protected function findModel($id);

    /**
     * @param JobsetModelInterface $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return JobsetModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return array
     */
    public function getJobNames()
    {
        return array_values(
            array_merge(
                $this->getOrderedJobNames(),
                $this->getUnorderedJobNames()
            )
        );
    }

    /**
     * Return the jobs need to execute in order.
     * The order is according the jobs positions.
     * If the previous job failed, the following job(s) would be blocked.
     *
     * @return array [
     *      job_name1,
     *      job_name2,
     *      job_nameN,
     *      ...
     * ]
     */
    abstract protected function getOrderedJobNames();

    /**
     * return the jobs can execute without order.
     *
     * @return array [
     *      job_name1,
     *      job_name2, ...
     *      job_nameN,
     * ]
     */
    abstract protected function getUnorderedJobNames();

    /**
     * Initialize the jobs instances
     * The instances is stored in $tis->jobs
     * Key is the job name and value is the job instance
     */
    abstract protected function initJobs();

    /**
     * Make durable job records and update local job instances
     *
     * @return mixed
     */
    abstract protected function makeJobs();

    /**
     * Get the jobset id which should be unique.
     *
     * @return int
     */
    public function getId()
    {
        return $this->model->getId();
    }

    /**
     * The jobset name
     *
     * @return string
     */
    public function getName()
    {
        return $this->model->getName();
    }

    /**
     * Get jobset try at
     *
     * @return string
     */
    public function getTryAt()
    {
        return $this->model->getTryAt();
    }

    /**
     * get the job instance after initialization.
     *
     * @param string $jobName
     * @return BaseJob|null
     */
    public function getJob($jobName)
    {
        return $this->getLocalJob($jobName);
    }

    /**
     * If return true, the jobset should be dispatched with considering its status
     * Or the jobset process should be terminated
     *
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        // If the jobset is finished or failed
        // the jobset may need to be dispatched
        // so we return true
        if ($this->isFinished() || $this->isFailed()) {
            return true;
        }
        // Just return false to tell to terminate the process
        if (!$this->isInTime()) {
            return false;
        }
        // return false means the jobset is done
        // and the process should be terminated
        if ($this->isDispatched() || $this->isCanceled()) {
            return false;
        }

        try {
            if (!$this->jobs) {
                $this->initJobs();
            }

            $this->doUnorderedJobs();
            $this->doOrderedJobs();

            $status = $this->getJobsetExecutionStatus();
            if ($status === JobsConst::JOB_SET_STATUS_FAILED) {
                $this->failed();
            } else {
                $this->updateStatus($status);
            }
        } catch (\Exception $e) {
            // just throw it
            throw $e;
        }

        return true;
    }

    /**
     * Finished means all the jobs are finished
     *
     * @return bool
     */
    public function isFinished()
    {
        return $this->getStatus() == JobsConst::JOB_SET_STATUS_FINISHED;
    }

    /**
     * Failed means at least one job is failed
     *
     * @return bool
     */
    public function isFailed()
    {
        return $this->getStatus() == JobsConst::JOB_SET_STATUS_FAILED;
    }

    /**
     * The middle status between default and finished
     *
     * @return bool
     */
    public function isOngoing()
    {
        return $this->getStatus() == JobsConst::JOB_SET_STATUS_ONGOING;
    }

    /**
     * The jobset may have an commander
     * After the jobset is finished, we should notice the commander
     * and mark the jobset as dispatched
     *
     * @return bool
     */
    public function isDispatched()
    {
        return $this->getStatus() === JobsConst::JOB_SET_STATUS_FINISHED_AND_DISPATCHED
            || $this->getStatus() === JobsConst::JOB_SET_STATUS_FAILED_AND_DISPATCHED;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->getStatus() === JobsConst::JOB_SET_STATUS_CANCELED;
    }

    /**
     * Check try at is before now
     * @return bool
     */
    public function isInTime()
    {
        return Helper::datetimeLE($this->getTryAt(), date('Y-m-d H:i:s'));
    }

    /**
     * After successfully notice to commander, we mark the jobset as dispatched here.
     */
    public function dispatched()
    {
        // Only finished or failed jobset should be dispatched
        if ($this->isFinished() || $this->isFailed()) {
            $status = $this->isFinished() ?
                JobsConst::JOB_SET_STATUS_FINISHED_AND_DISPATCHED : JobsConst::JOB_SET_STATUS_FAILED_AND_DISPATCHED;

            return $this->model->updateStatus($status);
        }

        return false;
    }

    /**
     * Get jobset current status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->model->getStatus();
    }

    /**
     * Get jobset execution status from sub jobs statuses.
     * At least one job failed, the jobset is failed.
     * All jobs finished, the jobset is finished.
     * At least one job has been executed, the jobset is ongoing.
     * All jobs have not been executed, the jobset is in default status.
     *
     * @return int
     */
    public function getJobsetExecutionStatus()
    {
        $finished = count($this->jobs) > 0 ? true : false;
        $failed = false;
        $middle = false;

        foreach ($this->jobs as $job) {
            if ($job->isFailed()) {
                $finished = false;
                $middle = false;
                $failed = true;
                break;
            }
            $finished = $finished && $job->isFinished();
            if (!$job->isFinished() && !$job->isFailed()) {
                $middle = true;
            }
        }

        if ($failed) {
            return JobsConst::JOB_SET_STATUS_FAILED;
        } elseif ($finished) {
            return JobsConst::JOB_SET_STATUS_FINISHED;
        } elseif ($middle) {
            return JobsConst::JOB_SET_STATUS_ONGOING;
        } else {
            return JobsConst::JOB_SET_STATUS_DEFAULT;
        }
    }

    /**
     * Do ordered jobs
     */
    protected function doOrderedJobs()
    {
        $jobNames = $this->getOrderedJobNames();
        foreach ($jobNames as $jobName) {
            $job = $this->getLocalJob($jobName);
            if (!$job) {
                throw new \Exception("job [$jobName] not exists");
            }
            $job->execute();
            $this->updateLocalJob($job);
            if (!$job->isFinished()) {
                break;
            }
        }
    }

    /**
     * Do unordered jobs
     */
    protected function doUnorderedJobs()
    {
        $jobNames = $this->getUnorderedJobNames();

        foreach ($jobNames as $jobName) {
            $job = $this->getLocalJob($jobName);
            if (!$job) {
                throw new \Exception("job [$jobName] not exists");
            }
            $job->execute();
            $this->updateLocalJob($job);
        }
    }

    /**
     * get the earliest job try at time, except the finished jobs
     *
     * @return false|mixed|string
     */
    protected function getJobsEarliestTryAt()
    {
        $tryAt = date('Y-m-d H:i:s');

        foreach ($this->jobs as $job) {
            if (!$job->isFinished() && Helper::datetimeLT($job->getTryAt(), $tryAt)) {
                $tryAt = $job->getTryAt();
            }
        }

        return $tryAt;
    }

    /**
     * update status after finish
     */
    protected function finished()
    {
        return $this->updateStatus(JobsConst::JOB_SET_STATUS_FINISHED);
    }

    /**
     * update status after fail
     */
    protected function failed()
    {
        return $this->updateStatus(JobsConst::JOB_SET_STATUS_FAILED);
    }

    /**
     * @param $status
     * @return bool
     */
    protected function updateStatus($status)
    {
        if ($status === JobsConst::JOB_SET_STATUS_ONGOING) {
            $tryAt = $this->getJobsEarliestTryAt();
            $this->model->setTryAt($tryAt);
        }
        $this->model->setStatus($status);

        return $this->model->persist();
    }

    /**
     * @param string $jobName
     * @return BaseJob|null
     */
    protected function getLocalJob($jobName)
    {
        return isset($this->jobs[$jobName]) ? $this->jobs[$jobName] : null;
    }

    /**
     * @param BaseJob $job
     */
    protected function updateLocalJob($job)
    {
        $this->jobs[$job->getName()] = $job;
    }
}