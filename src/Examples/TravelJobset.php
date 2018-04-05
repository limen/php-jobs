<?php
namespace Limen\Jobs\Examples;

use Limen\Jobs\Contracts\BaseJobset;
use Limen\Jobs\Examples\Models\JobsetModel;
use Limen\Jobs\JobsConst;

class TravelJobset extends BaseJobset
{
    protected $name = 'travel';

    protected function getOrderedJobNames()
    {
        return [
            'visit_Beijing',
            'visit_Shanghai',
        ];
    }

    protected function getUnorderedJobNames()
    {
        return [
            'visit_Nanjing',
            'visit_Huangshan',
        ];
    }

    protected function findModel($id)
    {
        return JobsetModel::findById($id);
    }

    protected function initJobs()
    {
        $job = VisitBeijingJob::get($this->getId());
        $this->updateLocalJob($job);

        $job = VisitShanghaiJob::get($this->getId());
        $this->updateLocalJob($job);

        $job = VisitNanjingJob::get($this->getId());
        $this->updateLocalJob($job);

        $job = VisitHuangshanJob::get($this->getId());
        $this->updateLocalJob($job);
    }

    protected function makeJobs()
    {
        $job = VisitBeijingJob::make($this->getId());
        $this->updateLocalJob($job);

        $job = VisitShanghaiJob::make($this->getId());
        $this->updateLocalJob($job);

        $job = VisitNanjingJob::make($this->getId());
        $this->updateLocalJob($job);

        $job = VisitHuangshanJob::make($this->getId());
        $this->updateLocalJob($job);
    }
}