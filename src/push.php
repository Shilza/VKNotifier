<?php

use InstaWorker\InstaWorker;

require_once '../vendor/autoload.php';

$instaWorker = new InstaWorker();

if ($instaWorker->login('', ''))
    $instaWorker->startMsgListener();
else
    echo 'Something went wrong' . PHP_EOL;