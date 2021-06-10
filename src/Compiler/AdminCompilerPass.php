<?php
namespace App\Compiler;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
/**
 * @see https://github.com/sonata-project/SonataAdminBundle/issues/4710#issuecomment-340791743
 */
final class AdminCompilerPass implements CompilerPassInterface
{
    private const CODE_MATCHES = [
    ];
    private const LABEL_MATCHES = [
    ];
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('sonata.admin.audit.orm.reader')) {
            $container->getDefinition('sonata.admin.audit.orm.reader')->setPublic(true);
        }
        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
            if (!$container->hasDefinition($id)) {
                continue;
            }
            if ('sonata.user.admin.user' === $id || 'sonata.user.admin.group' === $id) {
                $container->removeDefinition($id);
                continue;
            }
            $definition = $container->getDefinition($id);
            $adminClass = $definition->getClass();
            if (false !== strpos($adminClass, '%')) {
                $adminClass = $container->getParameter(str_replace('%', '', $adminClass));
            }
            $adminClassTab = \explode('\\', $adminClass);
            $entityName = \preg_replace('/Admin$/', '', \end($adminClassTab));
            $label = Inflector::ucwords(\str_replace('_', ' ', Inflector::tableize($entityName)));
            $code = 'app.admin.'.Inflector::tableize($entityName);
            if (\array_key_exists($code, static::CODE_MATCHES)) {
                $code = static::CODE_MATCHES[$code];
            }
            if (\array_key_exists($label, static::LABEL_MATCHES)) {
                $label = static::LABEL_MATCHES[$label];
            }
            //glr 20190331: If arguments are explicitly passed in, it should be used.
            $args = $definition->getArguments();
            if (isset($args[0])) {
                $code = $args[0];
            }
            if (isset($args[1])) {
                $entityFQCN = $args[1];
            } else {
                $entityFQCN = \preg_replace('/Admin$/', '', $adminClass);
                $entityFQCN = \str_replace('Admin', 'Entity', $entityFQCN);
            }
            if (isset($args[2])) {
                $adminController = $args[2];
            } else {
                $adminController = \str_replace('Admin', 'Controller', $adminClass);
            }
            if(!class_exists($adminController)){
                $adminController = null;
            }
            if ($definition->getTag('sonata.admin')) {
                $tag = $definition->getTag('sonata.admin');
                if (isset($tag[0]['label'])) {
                    $label = $tag[0]['label'];
                }
            }

            $definition
                ->clearTag('sonata.admin')
                ->addTag('sonata.admin', [
                    'manager_type' => 'orm',
                    'label' => $this->getAdminLabel($code, $label),
                    'group' => $this->getAdminGroup($code),
                    'on_top' => $this->getAdminOnTop($code),
                    'show_in_dashboard' => $this->getShowInDashboard($code),
                ])
                ->setArguments([
                    $code,
                    $entityFQCN,
                    $adminController,
                ])
                ->setPublic(true)
            ;
            if (false !== strpos($adminClass, 'UserAdmin')) {
                $definition->addMethodCall('setUserManager', [new Reference('fos_user.user_manager')]);
                $container->setAlias('sonata.user.admin.user', $adminClass)->setPublic(true);
            }
            if (false !== strpos($adminClass, 'GroupAdmin')) {
                $container->setAlias('sonata.user.admin.group', $adminClass)->setPublic(true);
            }
//            if (false !== strpos($adminClass, 'RetailerAdmin')) {
//                $definition->addMethodCall('addChild', [new Reference('app.admin.retailer_contact')]);
//            }
//            if (false !== strpos($adminClass, 'ManufacturerAdmin')) {
//                $definition->addMethodCall('addChild', [new Reference('app.admin.manufacturer_contact')]);
//            }
        }
    }
    private function getAdminLabel($code, $defaultLabel)
    {
//        $defaultLabel = str_replace('M A P', 'MAP', $defaultLabel);
//        $defaultLabel = str_replace('Manufacturer', 'Client', $defaultLabel);
//        $defaultLabel = str_replace('Retailer', 'Customer', $defaultLabel);
        $defaultLabel = str_replace('Url', 'City Links', $defaultLabel);
        return $defaultLabel;
    }
    private function getAdminGroup($code)
    {

        if ('app.admin.city_registration' == $code) {
            return 'Registration';
        }

        if ('app.admin.city' == $code
        || 'app.admin.state' == $code
        || 'app.admin.county' == $code) {
            return 'Location';
        }

        if ('app.admin.job_title' == $code
            || 'app.admin.job_title_name' == $code
            || 'app.admin.department' == $code
            || 'app.admin.division' == $code) {
            return 'Job Titles';
        }

        if ('app.admin.job_announcement' == $code
            || 'app.admin.job_announcement_view' == $code
            || 'app.admin.job_announcement_alerts' == $code
            || 'app.admin.job_announcement_impression' == $code) {
            return 'Job Announcements';
        }

        if ('app.admin.url' == $code) {
            return 'City Links';
        }

        if ('app.admin.city_upload' == $code
        || 'app.admin.city_profile_upload' == $code
        || 'app.admin.job_title_upload' == $code) {
            return 'Importers';
        }

        if ('app.admin.user' == $code
            || 'app.admin.group' == $code
            || 'app.admin.city_user' == $code
            || 'app.admin.job_seeker_user' == $code
        ) {
            return 'Users';
        }

        if ('app.admin.job_seeker_subscription_plan' == $code
            || 'app.admin.city_subscription_plan' == $code
            || 'app.admin.jobseeker.subscription' == $code
            || 'app.admin.city.subscription' == $code
        ) {
            return 'Subscriptions';
        }

        if ('app.admin.article' == $code) {
            return 'News';
        }

        return 'Lookups';
    }
    private function getAdminOnTop($code)
    {
        return null;
    }

    private function getShowInDashboard($code){
        if (
            'app.admin.department' == $code
            ||
            'app.admin.census_population' == $code
            ||
            'app.admin.submitted_job_title_interest' == $code
            ||
            'app.admin.division' == $code
            ||
            'app.admin.price_schedule' == $code
        ){
            return false;
        }
        return null;
    }
}
