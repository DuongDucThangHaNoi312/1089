<?php

namespace App\EventListener;

use App\Entity\User\JobSeekerUser;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use FOS\UserBundle\Mailer\MailerInterface;

class JobSeekerWelcomeEmailListener implements EventSubscriber {
    private $emails = [];
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function getSubscribedEvents() {
        return [
          Events::preUpdate,
          Events::postFlush
        ];
    }

    public function preUpdate(PreUpdateEventArgs $args) {
        $object = $args->getObject();
        if ($object instanceof JobSeekerUser) {
            $hasRoleChanged = $args->hasChangedField('roles');
            if($hasRoleChanged && $object->hasRole('ROLE_JOBSEEKER')) {
                $this->emails[$object->getEmail()] = $object;
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args) {
        if (count($this->emails)) {
            $emails = $this->emails;
            $this->emails = [];

            foreach ($emails as $email => $jobSeekerUser) {
                $this->mailer->sendJobSeekerWelcomeEmailMessage($jobSeekerUser);
            }
        }
    }
}