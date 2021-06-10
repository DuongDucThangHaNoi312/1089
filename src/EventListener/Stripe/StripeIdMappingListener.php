<?php

namespace App\EventListener\Stripe;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

class StripeIdMappingListener implements EventSubscriber {

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetaData(LoadClassMetadataEventArgs $loadClassMetadataEventArgs) {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        $entityName = $classMetadata->getName();

        if (strpos($entityName, 'Stripe') !== false) {
            if (isset($classMetadata->fieldMappings['stripeId'])) {
                $classMetadata->fieldMappings['stripeId']['length'] = 190;
            }
        }
    }
}