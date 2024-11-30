<?php

namespace App\Event;

use App\Grabber\Grabber;
use App\Message\GrabResourceMessage;
use App\Synchronizer\SynchronizerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;


readonly class GrabberEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SynchronizerInterface $synchronizer,
        private MessageBusInterface $messageBus,
    ) {
    }


    public static function getSubscribedEvents(): array
    {
        return [
            Grabber::CONTENT_CAPTURED_EVENT => 'handleCapturedContentEvent',
            Grabber::PAGINATION_FETCHED_EVENT => 'handlePaginationFetchedEvent',
        ];
    }

    public function handleCapturedContentEvent(GrabberEvent $event): void
    {
        $this->synchronizer->synchronize(new ArrayCollection($event->getData()));
    }

    public function handlePaginationFetchedEvent(GrabberEvent $event): void
    {
        $event
            ->getData()
            ->forAll(fn($i, $uri) => $this->messageBus->dispatch(
                new GrabResourceMessage(
                    resourceClass: $event->getResourceClass(),
                    uri: $uri,
                )
            ));
    }
}