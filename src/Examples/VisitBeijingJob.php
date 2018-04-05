<?php
namespace Limen\Jobs\Examples;

use Limen\Jobs\Examples\Models\JobModel;
use Limen\Jobs\JobsConst;

class VisitBeijingJob extends ExampleJob
{
    protected $name = 'visit_Beijing';

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
        $model->setId(1);
        $model->setName('visit_Beijing');
        $model->setStatus(JobsConst::JOB_SET_STATUS_DEFAULT);

        return $model;
    }
}