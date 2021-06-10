<?php

namespace App\DependencyInjection\Compiler;

use App\Manager\Doctrine\DoctrineORMModelManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class StripeBundleModelManagerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('miracode_stripe.model_manager')) {
            return;
        }

        $definition = $container->findDefinition('miracode_stripe.model_manager');
        $definition->setClass(DoctrineORMModelManager::class);
    }
}
