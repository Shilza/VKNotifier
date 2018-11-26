<?php

namespace InstaWorker;

use Exception;
use InstagramAPI\Exception\ChallengeRequiredException;

/**
 * Created by PhpStorm.
 * User: Raw
 * Date: 26.11.2018
 * Time: 11:52
 */
class InstaWorker
{
    private $instagram;

    public function __construct()
    {
        $this->instagram = new \InstagramAPI\Instagram();
    }

    /**
     * @param $login
     * @param $password
     * @return bool
     */
    function login($login, $password)
    {
        try {
            $this->instagram->login($login, $password);
            echo 'Login' . PHP_EOL;
        } catch (ChallengeRequiredException $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    function startMsgListener()
    {
        $loop = \React\EventLoop\Factory::create();
        $push = new \InstagramAPI\Push($loop, $this->instagram);

        echo 'Start' . PHP_EOL;

        $push->on('direct_v2_message', function (\InstagramAPI\Push\Notification $push) {

            $message = $this->instagram->direct->getThread(
                $push->getActionParam('id')
            )->getThread()->getItems()[0]->getText();

            echo $message . PHP_EOL;

            exec("python notifier.py $message");

            // thread id
            // $push->getActionParam('id');

            // message id
            // $push->getActionParam('x');
        });
        $push->start();

        $loop->run();
    }
}