<?php

namespace InstaWorker;

use Exception;
use InstagramAPI\Exception\ChallengeRequiredException;

class InstaWorker
{
    private $instagram;
    const BASE_URL = "https://instagram.com/p/";

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
        } catch (ChallengeRequiredException $e) {
           throw  $e;
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
            $receivedMessage = $this->instagram->direct->getThread(
                $push->getActionParam('id')
            )->getThread();

            $attachment = $this->getAttachment($receivedMessage);
            $username = $this->instagram->username;
            $who = $receivedMessage->getUsers()[0]->getUsername();

            exec("python notifier.py $username $who $attachment[0] $attachment[1]");
        });
        $push->start();

        $loop->run();
    }

    /**
     * @param $receivedMessage
     * @return string
     */
    private function getAttachment($receivedMessage) {
        $attachment = '';

        if($text = $receivedMessage->getItems()[0]->getText())
            $attachment = ["Message", $text];
        else if($receivedMessage->getItems()[0]->isLike())
            $attachment = ["Message", "❤"];
        else if($url = $this->toJson($receivedMessage->getItems()[0])->visual_media->media->image_versions2->candidates[0]->url)
            $attachment = ["Message", $url];
        // video: $this->toJson($receivedMessage->getItems()[0])->media_share->video_versions[0]->url
        // image: $this->toJson($receivedMessage->getItems()[0]->getMediaShare())->image_versions2->candidates[0]->url
        else if($share = $this->toJson($receivedMessage->getItems()[0])->media_share->code)
            $attachment = ["Share", static::BASE_URL . $share];
        else if($animatedMedia = $this->toJson($receivedMessage->getItems()[0])->animated_media->images->fixed_height->url)
            $attachment = ["Giphy", $animatedMedia];
        else if($broadcast = $this->toJson($receivedMessage->getItems()[0]->getLiveViewerInvite())->broadcast) {
            $broadcastOwner = $broadcast->broadcast_owner->username;
            $image = $broadcast->cover_frame_url;

            $attachment = ["Stream-$broadcastOwner", $image];
        }
        // video: $this->toJson($receivedMessage->getItems()[0]->getStoryShare()->getMedia())->video_versions[0]->url
        // image: $this->toJson($receivedMessage->getItems()[0]->getStoryShare()->getMedia())->image_versions2->candidates[0]->url
        else if($stories = $this->toJson($receivedMessage->getItems()[0]->getStoryShare()->getMedia())->code)
            $attachment = ["Stories", static::BASE_URL . $stories];

        return $attachment;
    }

    /**
     * @param $receivedMessage
     * @return mixed
     */
    private function toJson($receivedMessage) {
        return json_decode(json_encode($receivedMessage));
    }
}

// thread id
// $push->getActionParam('id');

// message id
// $push->getActionParam('x');