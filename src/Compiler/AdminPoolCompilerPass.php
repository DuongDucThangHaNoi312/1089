<?php
namespace App\Compiler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
/**
 * @see https://github.com/sonata-project/SonataAdminBundle/issues/4710#issuecomment-340791743
 */
final class AdminPoolCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $adminServicesIds = [];
        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $code = $definition->getArgument(0);
            // Must keep an alias with service code for proper role generation.
            $container->setAlias($code, $id);
            \array_push($adminServicesIds, $id);
            \array_push($adminServicesIds, $code);
            $container->getAlias($code)->setPublic(true);
            if ($container->hasDefinition($id . '.template_registry')) {
                $container->setAlias($code . '.template_registry', $id . '.template_registry');
                $container->getAlias($code . '.template_registry')->setPublic(true);
            }
        }
        $container->getDefinition('sonata.admin.pool')
            ->removeMethodCall('setAdminServiceIds')
            ->addMethodCall('setAdminServiceIds', [$adminServicesIds])
        ;
    }
}