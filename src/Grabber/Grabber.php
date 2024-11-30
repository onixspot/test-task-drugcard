<?php

namespace App\Grabber;

use App\Event\GrabberEvent;
use App\Extractor\PaginationExtractor;
use App\Filter\FilterInterface;
use App\Filter\PagesFilter;
use App\Message\GrabResourceMessage;
use App\Resource\ResourceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\UriResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler(handles: GrabResourceMessage::class, method: 'handleGrab')]
class Grabber
{
    public const string PAGINATION_FETCHED_EVENT = 'grabber.pagination-fetched';
    public const string CONTENT_CAPTURED_EVENT = 'grabber.content-captured';

    /**
     * @param ServiceLocator<ResourceInterface> $locator
     * @param MessageBusInterface $messageBus
     * @param HttpClientInterface $client
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        #[AutowireLocator(ResourceInterface::class, indexAttribute: 'key')]
        private readonly ServiceLocator $locator,
        private readonly MessageBusInterface $messageBus,
        private readonly HttpClientInterface $client,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke($resourceClass): GrabberInterface
    {
        $resource = $this->locator->get($resourceClass);

        return new class ($resource, grabber: $this) implements GrabberInterface {

            /** @var Collection<FilterInterface> */
            private Collection $filters;

            public function __construct(
                private readonly ResourceInterface $resource,
                private readonly Grabber $grabber,
            ) {
                $this->filters = new ArrayCollection();
            }

            public function addFilter(FilterInterface $filter): GrabberInterface
            {
                $this->filters->add($filter);

                return $this;
            }

            public function grab(): void
            {
                $this
                    ->getMessageBus()
                    ->dispatch(
                        new GrabResourceMessage(
                            resourceClass: ($this->getResource())::class,
                            filters: $this->getFilters()->toArray(),
                        )
                    );
            }

            public function getMessageBus(): MessageBusInterface
            {
                return $this->getGrabber()->getMessageBus();
            }

            public function getResource(): ResourceInterface
            {
                return $this->resource;
            }

            public function getGrabber(): Grabber
            {
                return $this->grabber;
            }

            public function getFilters(): Collection
            {
                return $this->filters;
            }
        };
    }

    /**
     * @return MessageBusInterface
     */
    public function getMessageBus(): MessageBusInterface
    {
        return $this->messageBus;
    }

    public function handleGrab(GrabResourceMessage $message): void
    {
        $resource = $this->locator->get($message->resourceClass);
        $filters = $message->getFilters();

        call_user_func(
            match (true) {
                true !== $filters->isEmpty() => function () use ($resource, $filters) {
                    $filter = $filters->findFirst(function ($index, $filter) {
                        return $filter instanceof PagesFilter;
                    });
                    $filter ??= new PagesFilter();
                    $hrefs = $resource
                        ->getExtractor()
                        ->extract(
                            $this->loadCrawler($resource),
                            PaginationExtractor::PAGINATION_ONLY
                        );
                    $this->eventDispatcher->dispatch(
                        new GrabberEvent(
                            new ArrayCollection(
                                array_slice(
                                    array: $hrefs,
                                    offset: $filter->getOffset(),
                                    length: $filter->getLimit()
                                )
                            ),
                            resourceClass: $resource::class
                        ),
                        Grabber::PAGINATION_FETCHED_EVENT
                    );
                },
                $message->uri !== null => function () use ($message, $resource) {
                    $this->eventDispatcher->dispatch(
                        new GrabberEvent(
                            $resource->getExtractor()->extract($this->loadCrawler($resource, $message->uri))
                        ),
                        self::CONTENT_CAPTURED_EVENT
                    );
                }
            }
        );
    }

    private function loadCrawler(ResourceInterface $resource, ?string $relativeUri = null): Crawler
    {
        $uri = $relativeUri !== null ? UriResolver::resolve($relativeUri, $resource->getUri()) : $resource->getUri();

        return new Crawler(
            $this->getClient()
                ->request(Request::METHOD_GET, $uri)
                ->getContent(),
            $uri
        );
    }

    public function getClient(): HttpClientInterface
    {
        return $this->client;
    }
}