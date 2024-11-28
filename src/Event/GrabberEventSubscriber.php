<?php

namespace App\Event;

use App\Grabber\Grabber;
use App\Synchronizer\SynchronizerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class GrabberEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
       private readonly SynchronizerInterface $synchronizer
    )
    {
    }


    public static function getSubscribedEvents(): array
    {
        return [
            Grabber::GRABBED_ITEM_EVENT => 'handleGrabbedItemEvent',
        ];
    }

    public function handleGrabbedItemEvent(GrabberEvent $event): void
    {
        $data = $event->getData();

        $this->synchronizer->synchronize($data);
    }
}