<?php
/**
 * Author: LI Mengxiang
 * Email: limengxiang876@gmail.com
 * Date: 2018/3/17
 */

namespace Limen\Jobs\Contracts;

/**
 * Interface JobsetModelInterface
 * @package Limen\Jobs
 */
interface JobsetModelInterface
{
    /**
     * @param $id
     * @return JobsetModelInterface
     */
    public static function findById($id);

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getName();

    public function getStatus();

    public function updateStatus($status);

    public function getCreatedAt();

    public function getTryAt();

    public function getJobIds();

    public function setId($id);

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