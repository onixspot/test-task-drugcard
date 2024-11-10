<?php

namespace App\Grabber;

use App\Message\ProductSynchronizationMessage;
use Doctrine\Common\Collections\ArrayCollection;
use League\Uri\Uri;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class Grabber
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly SerializerInterface $serializer,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(SourceInterface $source, ?int $offset = null, ?int $limit = null): ArrayCollection
    {
        try {
            $uri = $source->getUri();
            $crawler = $this->request($uri, [
                'vars' => $source->getUriVariables(),
            ]);

            if ($offset !== null || $limit !== null) {
                $references = $source->resolvePagination($crawler);
                $references = array_slice($references, $offset ?? 0, $limit);
                $key = array_search($uri, $references, true);
                if ($key !== false) {
                    unset($references[$key]);
                }

                foreach ($references as $reference) {
                    $this->messageBus->dispatch(new ProductSynchronizationMessage(
                        grabber: $source::class,
                        uri: $reference
                    ));
                }
            }

            $resolvedItems = array_merge(...$source
                ->defineScope($crawler)
                ->each(function (Crawler $crawler) use ($source) {
                    return $source
                        ->resolveItems($crawler)
                        ->each(function ($crawler) use ($source) {
                            try {
                                return $source->parseItem($crawler);
                            } catch (Throwable $e) {
                                throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
                            }
                        });
                }));

            return new ArrayCollection($this->serializer->deserialize(json_encode($resolvedItems, JSON_THROW_ON_ERROR), sprintf('%s[]', $source->targetEntity()), 'json'));
        } catch (Throwable $exception) {
            return new ArrayCollection();
        }
    }

    private function request(Uri|string $uri, array $options = []): Crawler
    {
        try {
            $response = $this->getHttpClient()->request(Request::METHOD_GET, $uri, $options);
            $content = $response->getContent();

            return new Crawler($content, $uri);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }
}