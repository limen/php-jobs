<?php
namespace Limen\Jobs\Examples;

use Limen\Jobs\Contracts\BaseJob;
use Limen\Jobs\Examples\Models\JobModel;
use Limen\Jobs\JobsConst;

class VisitShanghaiJob extends BaseJob
{
    protected $name = 'visit_Shanghai';

    protected function doStuff()
    {
        $statuses = [
            JobsConst::JOB_STATUS_FAILED,
            JobsConst::JOB_STATUS_WAITING_RETRY,
            JobsConst::JOB_STATUS_FINISHED,
            JobsConst::JOB_STATUS_WAITING_FEEDBACK,
        ];
        $card = time() % 4;

        return $statuses[$card];
    }

    protected function findModel($jobsetId)
    {
        $model = new JobModel();
        $model->setName('visit_Shanghai');
        $model->setId(2);
        $model->setStatus(JobsConst::JOB_SET_STATUS_DEFAULT);

        return $model;
    }
}