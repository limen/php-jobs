<?php
namespace Limen\Jobs\Examples;

use Limen\Jobs\Examples\Models\JobModel;
use Limen\Jobs\JobsConst;

class NoticeOneJob extends ExampleJob
{
    protected $name = 'notice_one';

    protected function doStuff()
    {
        $statuses = [
            JobsConst::JOB_STATUS_WAITING_FEEDBACK,
            JobsConst::JOB_STATUS_FINISHED,
            JobsConst::JOB_STATUS_WAITING_RETRY,
            JobsConst::JOB_STATUS_FAILED,
        ];
        $card = time() % 4;

        return $statuses[$card];
    }

    protected function findModel($jobsetId)
    {
        $model = new JobModel();
        $model->setId(5);
        $model->setName('notice_one');
        $model->setStatus(JobsConst::JOB_SET_STATUS_DEFAULT);
        return $model;
    }

    protected function getNextTryTime()
    {
        return date('Y-m-d H:i:s', time() + 20);
    }
}