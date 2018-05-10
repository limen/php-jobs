<?php
/**
 * Author: LI Mengxiang
 * Email: limengxiang876@gmail.com
 * Date: 2018/3/17
 */

namespace Limen\Jobs\Contracts;

interface JobModelInterface
{
    /**
     * @param $jobsetId
     * @param $jobName
     * @return JobModelInterface
     */
    public static function findByJobsetIdAndJobName($jobsetId, $jobName);

    /**
     * @param $jobId
     * @return JobModelInterface
     */
    public static function findById($jobId);

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getName();

    public function getStatus();

    public function getTriedCount();

    public function updateStatus($status);

    public function getCreatedAt();

    public function getTryAt();

    public function setId($id);

    public function getJobsetId();

    public function setJobsetId($jobSetId);

    public function setTryAt($tryAt);

    public function setStatus($status);

    public function setTriedCount($count);

    public function setName($name);

    public function setAttribute($attr, $value);

    public function getAttribute($attr);

    /**
     * Make model durable
     *
     * @return mixed
     */
    public function persist();
}