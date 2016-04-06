<?php

namespace yanivgal;

use yanivgal\Exceptions\CronomException;

class Cronom
{
    const KEY_DEBUG = 'debug';
    const KEY_DEBUG_OUTPUT = 'debugOutput';

    /**
     * @var string
     */
    private $debugOutput;

    /**
     * @var array
     */
    private $cronomJobs = [];

    /**
     * Cronom constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->init($config);
    }

    /**
     * @param array $config
     */
    private function init($config)
    {
        $debug = false;
        if (
            isset($config[self::KEY_DEBUG]) &&
            is_bool($config[self::KEY_DEBUG])
        ) {
            $debug = $config[self::KEY_DEBUG];
        }

        $this->debugOutput = getcwd() . '/cronom.log';
        if (
            isset($config[self::KEY_DEBUG_OUTPUT]) &&
            is_string($config[self::KEY_DEBUG_OUTPUT])
        ) {
            $this->debugOutput = $config[self::KEY_DEBUG_OUTPUT];
        }

        if ($debug) {
            fclose(STDIN);
            fclose(STDOUT);
            fclose(STDERR);

            global $STDIN;
            global $STDOUT;
            global $STDERR;

            $STDIN = fopen('/dev/null', 'r');
            $STDOUT = fopen($this->debugOutput, 'a');
            $STDERR = fopen($this->debugOutput, 'a');
        }
    }

    /**
     * @return string
     */
    public function getDebugOutput()
    {
        return $this->debugOutput;
    }

    /**
     * @param array|CronomJob $job
     */
    public function add($job)
    {
        if (is_array($job)) {
            $this->addJobArray($job);
        } elseif (is_a($job, CronomJob::class)) {
            $this->addCronomJob($job);
        }
    }

    /**
     * Runs the jobs
     */
    public function run()
    {
        /* @var CronomJob $cronomJob */
        foreach ($this->cronomJobs as $cronomJob) {
            $cronomJob->run();
        }
    }

    /**
     * @param array $jobConfig
     */
    private function addJobArray($jobConfig)
    {
        $this->addCronomJob(new CronomJob($jobConfig));
    }

    /**
     * @param CronomJob $job
     */
    private function addCronomJob(CronomJob $job)
    {
        $this->cronomJobs[] = $job;
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