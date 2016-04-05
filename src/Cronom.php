<?php

namespace yanivgal;

use yanivgal\Exceptions\CronomException;

class Cronom
{
    private $cronomJobs = [];

    public function __construct()
    {
        
    }

    public function add($job)
    {
        if (is_array($job)) {
            $this->addJobArray($job);
        } elseif (is_a($job, CronomJob::class)) {
            $this->addCronomJob($job);
        }
    }

    public function run()
    {
        /* @var CronomJob $cronomJob */
        foreach ($this->cronomJobs as $cronomJob) {
            $cronomJob->run();
        }
    }

    /**
     * @param array $jobArray
     */
    private function addJobArray($jobArray)
    {
        $this->validateJobArray($jobArray);

        $this->cronomJobs[] = new CronomJob(
            $jobArray[CronomJob::KEY_EXPRESSION],
            $jobArray[CronomJob::KEY_JOB]
        );
    }

    /**
     * @param CronomJob $job
     */
    private function addCronomJob(CronomJob $job)
    {
        $this->cronomJobs[] = $job;
    }

    /**
     * @param array $jobArray
     * @throws CronomException
     */
    private function validateJobArray($jobArray)
    {
        $expressionKey = CronomJob::KEY_EXPRESSION;
        if (array_key_exists($expressionKey, $jobArray)) {
            $this->throwException("Job array must contain $expressionKey key");
        }

        $jobKey = CronomJob::KEY_JOB;
        if (array_key_exists(CronomJob::KEY_JOB, $jobArray)) {
            $this->throwException("Job array must contain $jobKey key");
        }
    }

    /**
     * @param string $message
     * @throws CronomException
     */
    private function throwException($message)
    {
        throw new CronomException($message);
    }
}