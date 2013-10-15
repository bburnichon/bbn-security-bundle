<?php

namespace BBn\SecurityBundle\DependencyInjection;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

class ApiKeyFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.api_key.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('security.authentication.provider.api_key'))
            ->replaceArgument(0, new Reference($userProvider))
            ->replaceArgument(2, $providerId)
        ;

        $listenerId = 'security.authentication.listener.api_key.'.$id;
        $container->setDefinition($listenerId, new DefinitionDecorator('security.authentication.listener.api_key'))
            ->replaceArgument(1, $config['parameter'])
            ->replaceArgument(2, $providerId)
        ;

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'api_key';
    }

    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
            ->scalarNode('provider')->end()
            ->scalarNode('parameter')->defaultValue('apikey')->end()
            ->end();
    }
}
