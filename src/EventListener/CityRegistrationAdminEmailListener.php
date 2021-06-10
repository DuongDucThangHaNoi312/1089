<?php

namespace App\EventListener;
use App\Entity\CityRegistration;
use App\Entity\User;
use App\Mailer\TwigSwiftMailer;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use FOS\UserBundle\Mailer\MailerInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class CityRegistrationAdminEmailListener implements EventSubscriber {

    private $emails = [];

    /** @var TwigSwiftMailer $mailer */
    private $mailer;

    /**
     * CityRegistrationAdminEmailListener constructor.
     *
     * @param MailerInterface         $mailer
     */
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
        $em = $args->getObjectManager();
        if ($object instanceof CityRegistration) {
            if ($object->getStatus()->getId() == CityRegistration::STATUS_PENDING || $object->getStatus()->getId() == CityRegistration::STATUS_APPROVED) {
                $admins = $this->getAdminUsers($em);
                foreach ($admins as $admin) {
                    $this->emails[$admin->getEmail()] = $object;
                }
            }
        }

    }

    private function getAdminUsers(ObjectManager $em) {
        return $em->getRepository(User::class)->findByAdminRole();
    }

    public function postFlush(PostFlushEventArgs $args) {
        if(count($this->emails)) {
            $emails = $this->emails;
            $this->emails = [];


            foreach ($emails as $email => $cityRegistration) {
                $this->mailer->sendCityRegistrationAdminEmailMessage($email, $cityRegistration);
            }
        }
    }
}