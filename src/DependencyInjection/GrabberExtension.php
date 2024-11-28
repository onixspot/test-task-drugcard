<?php

namespace App\DependencyInjection;

use App\Metadata\Resource;
use App\Resource\ResourceInterface;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class GrabberExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerAttributeForAutoconfiguration(
            Resource::class,
            function (ChildDefinition $childDefinition, Resource $resource, ReflectionClass $reflector) use ($container) {
//                $name = strtolower(
//                    preg_replace(['/.+\\\(\w+)$/', '/([a-z])([A-Z])/'], ['\1', '\1_\2'], $reflector->name)
//                );
                $container->setDefinition(
                    $reflector->name,
                    $childDefinition
                        ->setClass($reflector->name)
                        ->setParent(ResourceInterface::class)
                        ->setArguments([
                            '$uri' =>  $resource->uri,
                            '$selectors' => $resource->xpathChain,
                            '$paginationXPath' => $resource->paginationXPath,
                        ])
                        ->addMethodCall('setExtractor', [new Reference('grabber.listing.extractor')])
                );
            }
        );
    }
}