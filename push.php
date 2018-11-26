<?php

require_once 'vendor/autoload.php';

$instagram = new \InstagramAPI\Instagram();
$instagram->login('', '');

$loop = \React\EventLoop\Factory::create();
$push = new \InstagramAPI\Push($loop, $instagram);

$push->on('direct_v2_message', function (\InstagramAPI\Push\Notification $push) {
    global $instagram;

    $message = $instagram->direct->getThread($push->getActionParam('id'))->getThread()->getItems()[0]->getText();

    echo $message . PHP_EOL;

    exec("python notifier.py $message");

    // thread id
    // $push->getActionParam('id');

    // message id
    // $push->getActionParam('x');

});
$push->start();

$loop->run();