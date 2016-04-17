<?php

namespace yanivgal;

use Cron\CronExpression;
use DateTime;
use yanivgal\Exceptions\CronomJobException;

/**
 * Class CronomJob
 * @package yanivgal
 *
 * @property-read string $expression
 * @property-read callable $job
 * @property-read string $output
 * @property-read bool $enabled
 * @property-read int $maxRuntime Max execution time. Default: 60.
 */
class CronomJob
{
    const KEY_EXPRESSION = 'expression';
    const KEY_JOB = 'job';
    const KEY_OUTPUT = 'output';
    const KEY_ENABLED = 'enabled';
    const KEY_MAX_RUNTIME = 'maxRuntime';
    
    /**
     * @var CronExpression
     */
    private $expression;

    /**
     * @var callable
     */
    private $job;

    /**
     * @var string
     */
    private $output;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var int
     */
    private $maxRuntime;

    /**
     * CronomJob constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->validateConfig($config);
        $this->validateExpression($config[self::KEY_EXPRESSION]);
        $this->validateJob($config[self::KEY_JOB]);

        $this->init($config);
    }

    /**
     * @param array $config
     */
    private function init($config)
    {
        $this->expression = CronExpression::factory($config[self::KEY_EXPRESSION]);

        $this->job = $config[self::KEY_JOB];

        $this->output = '/dev/null';
        if (
            isset($config[self::KEY_OUTPUT]) &&
            $config[self::KEY_OUTPUT] != ''
        ) {
            $this->output = $config[self::KEY_OUTPUT];
        }

        $this->enabled = true;
        if (
            isset($config[self::KEY_ENABLED]) &&
            is_bool($config[self::KEY_ENABLED])
        ) {
            $this->enabled = $config[self::KEY_ENABLED];
        }
        
        $this->maxRuntime = 60;
        if (
            isset($config[self::KEY_MAX_RUNTIME]) &&
            is_int($config[self::KEY_MAX_RUNTIME]) &&
            $config[self::KEY_MAX_RUNTIME] >= 0
        ) {
            $this->maxRuntime = $config[self::KEY_MAX_RUNTIME];
        }
    }

    /**
     * Runs the assigned job
     */
    public function run()
    {
        if (!$this->enabled) {
            return;
        }
        
        $this->validateJob($this->job);
        
        if ($this->getExpression()->isDue()) {
            $this->runJob();
        }
    }

    /**
     * Force run the assigned job without checking the scheduled expression
     * or enabled flag
     */
    public function forceRun()
    {
        $this->validateJob($this->job);

        $this->runJob();
    }
    
    private function runJob()
    {
        $dir = __DIR__;
        $serializedJob = CronomSerializer::serialize($this->job);
        exec("php $dir/CronomRunner.php $serializedJob $this->maxRuntime 1 >> $this->output 2>&1");
    }

    /**
     * @return bool
     */
    public function isDue()
    {
        return $this->getExpression()->isDue();
    }

    /**
     * @return DateTime
     */
    public function getNextRunDate()
    {
        return $this->getExpression()->getNextRunDate();
    }

    /**
     * @return DateTime
     */
    public function getPreviousRunDate()
    {
        return $this->getExpression()->getPreviousRunDate();
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            $this->throwPropertyNotExistException($name);
        }
        
        return $this->getValue($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function getValue($name)
    {
        switch ($name) {
            case self::KEY_EXPRESSION:
                return $this->getExpressionString();
                break;
            case self::KEY_JOB:
                return $this->getJob();
                break;
            case self::KEY_OUTPUT:
                return $this->getOutput();
                break;
            case self::KEY_ENABLED:
                return $this->getEnabled();
                break;
            case self::KEY_MAX_RUNTIME:
                return $this->getMaxRuntime();
                break;
            default:
                $this->throwPropertyNotExistException($name);
        }
        
        return null;
    }

    /**
     * @return string
     */
    private function getExpressionString()
    {
        return $this->getExpression()->getExpression();
    }

    /**
     * @return callable
     */
    private function getJob()
    {
        return $this->job;
    }

    /**
     * @return string
     */
    private function getOutput()
    {
        return $this->output;
    }

    /**
     * @return bool
     */
    private function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return int
     */
    private function getMaxRuntime()
    {
        return $this->maxRuntime;
    }

    /**
     * @param array $config
     * @throws CronomJobException
     */
    private function validateConfig($config)
    {
        if (!is_array($config)) {
            $this->throwException('Config must be an array');
        }

        $keyExpression = self::KEY_EXPRESSION;
        if (!isset($config[$keyExpression])) {
            $this->throwException("Config must contain $keyExpression key");
        }

        $keyJob = self::KEY_JOB;
        if (!isset($config[self::KEY_JOB])) {
            $this->throwException("Config must contain $keyJob key");
        }
    }

    /**
     * @param string $expression
     * @throws CronomJobException
     */
    private function validateExpression($expression)
    {
        if (!CronExpression::isValidExpression($expression)) {
            $this->throwException('Invalid expression');
        }
    }

    /**
     * @param callable $job
     * @throws CronomJobException
     */
    private function validateJob($job)
    {
        if (!is_callable($job)) {
            $this->throwException('Job must be a legit callable');
        }
    }

    /**
     * @param string $propertyName
     * @throws CronomJobException
     */
    private function throwPropertyNotExistException($propertyName)
    {
        $this->throwException("Property $propertyName does not exist in CronomJob");
    }

    /**
     * @param string $message
     * @throws CronomJobException
     */
    private function throwException($message)
    {
        throw new CronomJobException($message);
    }

    /**
     * @return CronExpression
     */
    private function getExpression()
    {
        return $this->expression;
    }
}