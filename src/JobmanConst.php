<?php
/**
 * Author: LI Mengxiang
 * Email: limengxian876g@gmail.com
 * Date: 2018/3/17
 */

namespace Limen\Jobs;

/**
 * Class JobsConst
 * @package Limen\Jobs
 */
class JobsConst
{
    /**
     * job statuses
     */
    const JOB_STATUS_DEFAULT = 0;
    const JOB_STATUS_FINISHED = 1;
    const JOB_STATUS_FAILED = 2;
    const JOB_STATUS_WAITING_RETRY = 3;
    // Waiting feedback means your job is a distributed transaction
    // such as calling an external API and waiting callback.
    const JOB_STATUS_WAITING_FEEDBACK = 4;
    const JOB_STATUS_CANCELED = 5;

    /**
     * jobset statuses
     */
    const JOB_SET_STATUS_DEFAULT = 0;
    const JOB_SET_STATUS_FINISHED = 1;
    const JOB_SET_STATUS_FAILED = 2;
    // ongoing is the middle status
    // between default status and finished status
    const JOB_SET_STATUS_ONGOING = 3;
    const JOB_SET_STATUS_CANCELED = 4;
    // Have been dispatched to the commander
    // after finished
    const JOB_SET_STATUS_FINISHED_AND_DISPATCHED = 5;
    // Have been dispatched to the commander
    // after failed
    const JOB_SET_STATUS_FAILED_AND_DISPATCHED = 6;
}