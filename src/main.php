<?php

require_once '../vendor/autoload.php';

$instagram = new \InstagramAPI\Instagram();
$instagram->login('', '');

var_dump($url);


var_dump(getNewMessages());

//Direct::approvePendingThreads()

/**
 * @return array
 */
function getNewMessages()
{
    global $instagram;
    $cursor = null;
    $messages = [];
    $unreadThreadsCount = $instagram->direct->getInbox($cursor)->getInbox()->getUnseenCount();
    $countOfUsers = 0;

    do {
        $inbox = $instagram->direct->getInbox($cursor)->getInbox();
        $threads = $inbox->getThreads();

        foreach ($threads as $thread) {
            if ($countOfUsers++ >= $unreadThreadsCount)
                break;

            //var_dump(count($instagram->direct->getThread($thread->getThreadId())->getThread()->getItems()[0]));
            array_push($messages, [$thread->getUsers()[0]->getUsername(), $thread->getItems()[0]->getText()]);
        }

        $cursor = $inbox->getOldestCursor();
    } while (isset($cursor) && ($countOfUsers++ < $unreadThreadsCount));

    return $messages;
}

function getPendingMessages()
{
    global $instagram;
    $threads = $instagram->direct->getPendingInbox()->getInbox()->getThreads();
    $messages = [];

    foreach ($threads as $thread)
        array_push($messages, [$thread->getUsers()[0]->getUsername(), $thread->getItems()[0]->getText()]);

    $instagram->direct->approvePendingThreads($threads);

    return $messages;
}