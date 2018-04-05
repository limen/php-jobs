<?php
/**
 * Author: LI Mengxiang
 * Email: limengxiang876@gmail.com
 * Date: 2018/4/5
 */

namespace Limen\Jobs\Examples;

use Limen\Jobs\Contracts\BaseJob;
use Limen\Jobs\Examples\Models\JobModel;

abstract class ExampleJob extends BaseJob
{
    protected function makeModel($jobsetId)
    {
        $model = new JobModel();
        $model->setName($this->name);
        $model->setJobsetId($jobsetId);
        $model->setTryAt($this->getFirstTryAt());
        $model->persist();

        return $model;
    }
}