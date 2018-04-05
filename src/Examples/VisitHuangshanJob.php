<?php
namespace Limen\Jobs\Examples;

use Limen\Jobs\Contracts\BaseJob;
use Limen\Jobs\Examples\Models\JobModel;
use Limen\Jobs\Helper;
use Limen\Jobs\JobsConst;

class VisitHuangshanJob extends BaseJob
{
    protected $name = 'visit_Huangshan';

    protected function doStuff()
    {
        $statuses = [
            JobsConst::JOB_STATUS_FINISHED,
            JobsConst::JOB_STATUS_WAITING_FEEDBACK,
            JobsConst::JOB_STATUS_FAILED,
            JobsConst::JOB_STATUS_WAITING_RETRY,
        ];
        $card = time() % 4;

        return $statuses[$card];
    }

    protected function findModel($jobsetId)
    {
        $model = new JobModel();
        $model->setName('visit_Huangshan');
        $model->setId(4);
        $model->setStatus(JobsConst::JOB_SET_STATUS_DEFAULT);

        return $model;
    }
}