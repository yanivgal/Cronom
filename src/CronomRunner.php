<?php

use yanivgal\CronomSerializer;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    require __DIR__ . '/../../../autoload.php';
}

$timeLimit = $argv[2];
set_time_limit($timeLimit);

date_default_timezone_set('UTC');

$job = CronomSerializer::deserialize($argv[1]);

call_user_func($job);