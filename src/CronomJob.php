<?php

namespace yanivgal;

use Cron\CronExpression;
use DateTime;
use yanivgal\Exceptions\CronomJobException;

/**
 * Class CronomJob
 * @package yanivgal
 *
 * @property
 * @property
 * @property callable $job
 * @property-read string $expression
 */
class CronomJob
{
    const KEY_EXPRESSION = 'expression';
    const KEY_JOB = 'job';
    
    /**
     * @var CronExpression
     */
    private $expression;

    /**
     * @var callable
     */
    private $job;
    
    /**
     * CronomJob constructor.
     * @param string $expression
     * @param callable $job
     * @throws CronomJobException
     */
    public function __construct($expression, $job = null)
    {
        $this->validateExpression($expression);
        $this->expression = CronExpression::factory($expression);
        if (isset($job)) {
            $this->setJob($job);
        }
    }

    /**
     * Runs the assigned job
     */
    public function run()
    {
        $this->validateJob($this->job);
        
        if ($this->getExpression()->isDue()) {
            call_user_func($this->job);
        }
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
            $this->throwPropertyNotExistsException($name);
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
            default:
                $this->throwPropertyNotExistsException($name);
        }
    }

    /**
     * @return string
     */
    private function getExpressionString()
    {
        return $this->getExpression();
    }

    /**
     * @return callable
     */
    private function getJob()
    {
        return $this->job;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws CronomJobException
     */
    public function __set($name, $value)
    {
        if (!property_exists($this, $name)) {
            $this->throwPropertyNotExistsException($name);
        }

        $this->setValue($name, $value);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws CronomJobException
     */
    private function setValue($name, $value)
    {
        switch ($name) {
            case self::KEY_JOB:
                $this->setJob($value);
                break;
            default:
                $this->throwPropertyNotExistsException($name);
        }
    }

    private function setJob($job)
    {
        $this->validateJob($job);
        $this->job = $job;
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
    private function throwPropertyNotExistsException($propertyName)
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