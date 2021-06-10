<?php

namespace App\EventListener;

use App\Entity\CardInterface;
use App\Entity\ChargeInterface;
use App\Entity\InvoiceInterface;
use App\Entity\Stripe\StripeCard;
use App\Entity\Stripe\StripeCharge;
use App\Entity\User;
use App\Mailer\TwigSwiftMailer;
use App\Repository\Stripe\StripeCardRepository;
use App\Repository\UserRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use FOS\UserBundle\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SubscriptionPaymentFailedListener implements EventSubscriber {
    /** @var TwigSwiftMailer $mailer */
    private $mailer;
    private $router;
    /** @var EntityManager $em */
    private $em;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $router, EntityManager $em)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->em = $em;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args) {
        $object = $args->getObject();

        if ($object instanceof ChargeInterface) {
            /** @var ChargeInterface $object */
            if ($object->hasFailed()) {
                /** @var UserRepository $userRepository */
                $userRepository = $this->em->getRepository(User::class);
                /** @var StripeCardRepository $cardRepository */
                $cardRepository = $this->em->getRepository(StripeCard::class);
                /** @var User $user */
                $users = $userRepository->findBy(['rawStripeCustomer' => $object->getCustomer()]);
                /** @var CardInterface $card */
                $cards = $cardRepository->findBy(['stripeId' => $object->getSource()]);
                if (count($users) > 0 && count($cards) > 0) {
                    $this->sendEmail($cards[0], $object, $users[0]);
                }

            }
        }
    }

    public function sendEmail(CardInterface $card, ChargeInterface $charge,  User $user) {
        $this->mailer->sendPaymentFailureMessage($user->getEmail(), $charge->getAmount()/100, $card, $charge->getCreated(), $user instanceof User\CityUser ? $user->getCity() : null);
    }
}