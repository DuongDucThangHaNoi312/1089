<?php

namespace App\EventListener;
use App\Entity\JobAnnouncement;
use App\Entity\JobAnnouncement\Lookup\JobAnnouncementStatus;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class JobAnnouncementStatusListener implements EventSubscriber {

    public function getSubscribedEvents() {
        return [
            Events::preUpdate,
        ];
    }

    public function preUpdate(LifecycleEventArgs $args) {
        $object = $args->getObject();
        if ($object instanceof JobAnnouncement) {
            $status = $object->getStatus();
            if ($status == null || $status->getId() == JobAnnouncement::STATUS_TODO) {
                $em = $args->getObjectManager();
                $object->setStatus($em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_DRAFT));
            }
        }
    }
}