<?php
namespace Limen\Jobs\Examples;


use Limen\Jobs\Examples\Models\JobModel;
use Limen\Jobs\JobsConst;

class NoticeTwoJob extends ExampleJob
{
    protected $name = 'notice_two';

    protected function doStuff()
    {
        $statuses = [
            JobsConst::JOB_STATUS_WAITING_RETRY,
            JobsConst::JOB_STATUS_FAILED,
            JobsConst::JOB_STATUS_WAITING_FEEDBACK,
            JobsConst::JOB_STATUS_FINISHED,
        ];
        $card = time() % 4;

        return $statuses[$card];
    }

    protected function findModel($jobsetId)
    {
        $model = new JobModel();
        $model->setId(6);
        $model->setName('notice_two');
        $model->setStatus(JobsConst::JOB_SET_STATUS_DEFAULT);

        return $model;
    }

    protected function getNextTryTime()
    {
        return date('Y-m-d H:i:s', time() + 40);
    }
}