<?php
/**
 * Author: LI Mengxiang
 * Email: limengxiang876@gmail.com
 * Date: 2018/4/5
 */

namespace Limen\Jobs\Examples;

use Limen\Jobs\Contracts\BaseJobset;
use Limen\Jobs\Examples\Models\JobsetModel;
use Limen\Jobs\JobsConst;

abstract class ExampleJobset extends BaseJobset
{
    protected function makeModel($attributes = [])
    {
        $model = new JobsetModel();
        $model->setName($this->name);
        $model->setStatus(JobsConst::JOB_SET_STATUS_DEFAULT);

        foreach ($attributes as $attr => $value) {
            $model->setAttribute($attr, $value);
        }

        $model->persist();

        return $model;
    }
}