<?php

namespace InstaWorker;

use Exception;
use InstagramAPI\Exception\ChallengeRequiredException;

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

            $username = $receivedMessage->getUsers()[0]->getUsername();
            $attachment = $this->getAttachment($receivedMessage);

            exec("python notifier.py $username $attachment");
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
            $attachment = $text;
        else if($receivedMessage->getItems()[0]->isLike())
            $attachment = "Action:Like";
        else if($url = $this->toJson($receivedMessage->getItems()[0])->visual_media->media->image_versions2->candidates[0]->url)
            $attachment = $url;
        else if($videoShare = $this->toJson($receivedMessage->getItems()[0])->media_share->video_versions[0]->url)
            $attachment = $videoShare;
        else if($imageShare = $this->toJson($receivedMessage->getItems()[0]->getMediaShare())->image_versions2->candidates[0]->url)
            $attachment = $imageShare;
        else if($animatedMedia = $this->toJson($receivedMessage->getItems()[0])->animated_media->images->fixed_height->url)
            $attachment = $animatedMedia;
        else if($broadcast = $this->toJson($receivedMessage->getItems()[0]->getLiveViewerInvite())->broadcast->cover_frame_url)
            $attachment = $broadcast;
        else if($videoStories = $this->toJson($receivedMessage->getItems()[0]->getStoryShare()->getMedia())->video_versions[0]->url)
            $attachment = $videoStories;
        else if($imageStories = $this->toJson($receivedMessage->getItems()[0]->getStoryShare()->getMedia())->image_versions2->candidates[0]->url)
            $attachment = $imageStories;

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