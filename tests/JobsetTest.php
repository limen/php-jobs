<?php
/**
 * Created by PhpStorm.
 * User: lim
 * Date: 2018/3/17
 * Time: 12:05
 */

use Limen\Jobs\Helper;
use PHPUnit\Framework\TestCase;
use Limen\Jobs\Examples\TravelJobset;
use Limen\Jobs\Examples\NoticeJobset;
use Limen\Jobs\JobsConst;

/**
 * Class JobsetTest
 */
class JobsetTest extends TestCase
{
    public function testExecute()
    {
        $jobset = TravelJobset::make();

        $this->assertFalse($jobset->isFailed());
        $this->assertFalse($jobset->isFinished());
        $this->assertFalse($jobset->isDispatched());
        $this->assertFalse($jobset->isOngoing());

        $jobNames = $jobset->getJobNames();
        $this->assertEquals($jobNames, [
            'visit_Beijing',
            'visit_Shanghai',
            'visit_Nanjing',
            'visit_Huangshan',
        ]);

        for ($i = 0; $i < 10; $i++) {
            $jobset->execute();
            $executionStatus = $jobset->getJobsetExecutionStatus();

            // execution status must equals jobset status
            $this->assertEquals($jobset->getStatus(), $executionStatus);

            $default = $executionStatus === JobsConst::JOB_SET_STATUS_DEFAULT;
            $ongoing = $executionStatus === JobsConst::JOB_SET_STATUS_ONGOING;
            $finished = $executionStatus === JobsConst::JOB_SET_STATUS_FINISHED;
            $failed = $executionStatus === JobsConst::JOB_SET_STATUS_FAILED;
            $this->assertTrue($default || $ongoing || $finished || $failed);
            $sum = (int)$default + (int)$ongoing + (int)$finished + (int)$failed;
            $this->assertEquals($sum, 1);

            $bjJob = $jobset->getJob('visit_Beijing');
            $shJob = $jobset->getJob('visit_Shanghai');
            $njJob = $jobset->getJob('visit_Nanjing');
            $hsJob = $jobset->getJob('visit_Huangshan');

            var_dump("loop $i...", $bjJob->getStatus(), $shJob->getStatus(), $njJob->getStatus(), $hsJob->getStatus());

            if ($jobset->isFinished()) {
                foreach ($jobNames as $jobName) {
                    $job = $jobset->getJob($jobName);
                    $this->assertTrue($job->isFinished());
                }
            } elseif ($jobset->isFailed()) {
                if ($bjJob->isFailed()) {
                    $this->assertEquals($shJob->getStatus(), JobsConst::JOB_SET_STATUS_DEFAULT);
                } else {
                    if ($bjJob->isFinished()) {
                        $this->assertNotEquals($shJob->getStatus(), JobsConst::JOB_SET_STATUS_DEFAULT);
                    } else {
                        $this->assertEquals($shJob->getStatus(), JobsConst::JOB_SET_STATUS_DEFAULT);
                    }
                    $this->assertTrue($shJob->isFailed() || $njJob->isFailed() || $hsJob->isFailed());
                }
            } else {
                $this->assertFalse($bjJob->isFailed());
                $this->assertFalse($shJob->isFailed());
                $this->assertFalse($njJob->isFailed());
                $this->assertFalse($hsJob->isFailed());
                $this->assertTrue(
                    (
                        $bjJob->isWaitingFeedback()
                        || $shJob->isWaitingFeedback()
                        || $njJob->isWaitingFeedback()
                        || $hsJob->isWaitingFeedback()
                    ) || (
                        $bjJob->isWaitingRetry()
                        || $shJob->isWaitingRetry()
                        || $njJob->isWaitingRetry()
                        || $hsJob->isWaitingRetry()
                    ));
                if ($bjJob->isWaitingFeedback() || $bjJob->isWaitingRetry()) {
                    $this->assertEquals($shJob->getStatus(), JobsConst::JOB_STATUS_DEFAULT);
                }
            }

            sleep(rand(1,5));
        }

        $this->assertFalse($jobset->isDispatched());

        $jobsetStatus = $jobset->getStatus();

        $this->assertTrue($jobset->execute());
        $jobset->dispatched();
        if ($jobset->isDispatched()) {
            $this->assertFalse($jobset->execute());
        }

        if ($jobsetStatus === JobsConst::JOB_SET_STATUS_FINISHED) {
            $this->assertEquals($jobset->getStatus(), JobsConst::JOB_SET_STATUS_FINISHED_AND_DISPATCHED);
        } elseif ($jobsetStatus === JobsConst::JOB_SET_STATUS_FAILED) {
            $this->assertEquals($jobset->getStatus(), JobsConst::JOB_SET_STATUS_FAILED_AND_DISPATCHED);
        }
    }

    public function testExecuteTime()
    {
        $jobset = NoticeJobset::get(1);

        for ($i = 0; $i < 10; $i ++) {
            $jobset->execute();
            if ($jobset->isOngoing()) {
                $jobOne = $jobset->getJob('notice_one');
                $jobTwo = $jobset->getJob('notice_two');
                $tryAt = date('Y-m-d H:i:s');
                if (!$jobOne->isFinished() && Helper::datetimeLE($jobOne->getTryAt(), $tryAt)) {
                    $tryAt = $jobOne->getTryAt();
                }
                if (!$jobTwo->isFinished() && Helper::datetimeLE($jobTwo->getTryAt(), $tryAt)) {
                    $tryAt = $jobTwo->getTryAt();
                }
                $this->assertEquals($jobset->getTryAt(), $tryAt);
                if (Helper::datetimeLT(date('Y-m-d H:i:s'), $jobset->getTryAt())) {
                    $this->assertFalse($jobset->execute());
                }

                sleep(rand(1,5));
            }
        }
    }

    public function testMake()
    {
        $jobset = NoticeJobset::make();
        $this->assertEquals(get_class($jobset), NoticeJobset::class);

        $jobset = TravelJobset::make();
        $this->assertEquals(get_class($jobset), TravelJobset::class);

        $job = \Limen\Jobs\Examples\VisitShanghaiJob::make(1);
        $this->assertEquals(get_class($job), \Limen\Jobs\Examples\VisitShanghaiJob::class);
    }
}