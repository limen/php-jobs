<?php
namespace Limen\Jobs\Examples;

use Limen\Jobs\Examples\Models\JobsetModel;

class NoticeJobset extends ExampleJobset
{
    protected $name = 'notice';

    public function getOrderedJobNames()
    {
        return [];
    }

    public function getUnorderedJobNames()
    {
        return [
            'notice_one',
            'notice_two',
        ];
    }

    protected function findModel($id)
    {
        return JobsetModel::findById($id);
    }
    protected function initJobs()
    {
        $job = NoticeOneJob::get($this->getId());
        $this->updateLocalJob($job);

        $job = NoticeTwoJob::get($this->getId());
        $this->updateLocalJob($job);
    }

    protected function makeJobs()
    {
        $job = NoticeOneJob::make($this->getId());
        $this->updateLocalJob($job);

        $job = NoticeTwoJob::make($this->getId());
        $this->updateLocalJob($job);
    }
}