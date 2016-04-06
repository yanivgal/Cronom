<?php

/*
 *   Copy this cronom.php file into your project root folder and
 *   add the following line to your crontab file:
 *   * * * * * cd /path/to/project && php cronom.php 1>> /dev/null 2>&1
 */

use yanivgal\Cronom;
use yanivgal\CronomJob;

// Check if the location of autoload.php need to be changed
require __DIR__ . '/../vendor/autoload.php';

// Add needed timezone so the CronomJob won't scream at you
date_default_timezone_set('UTC');

$cronom = new Cronom();

// You can create a new CronomJob
$job1 = new CronomJob([
    CronomJob::KEY_EXPRESSION => '* * * * *',
    CronomJob::KEY_JOB => function() {
        print (new DateTime())->format('Y-m-d H:i:s') . PHP_EOL;
    },
    CronomJob::KEY_OUTPUT => __DIR__ . '/job1.log'
]);

// And add it to Cronom
$cronom->add($job1);

// Or you can add the CronomJob configs directly
$cronom->add([
    CronomJob::KEY_EXPRESSION => '*/5 * * * *',
    CronomJob::KEY_JOB => function() {
        print (new DateTime())->format('Y-m-d H:i:s') . PHP_EOL;
    },
    CronomJob::KEY_OUTPUT => __DIR__ . '/job2.log'
]);

// Finally, run the added CronomJobs per each schedule expression
$cronom->run();
