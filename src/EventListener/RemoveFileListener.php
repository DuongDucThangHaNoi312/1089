<?php

namespace App\EventListener;

use App\Entity\City;
use Vich\UploaderBundle\Event\Event;
use Doctrine\ORM\EntityManager;

class RemoveFileListener
{
    /**
     * @var EntityManager $em
     */
    private $em;
    /**
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    // make sure a file entity object is removed after the file is deleted
    public function onPostRemove(Event $event)
    {
        $propertyName = $event->getMapping()->getFileNamePropertyName();
        $object = $event->getObject();
        if ($object instanceof City) {
            switch($propertyName) {
                case 'bannerImage':
                    $object->setBannerImage(null);
                    break;
                case 'sealImage':
                    $object->setSealImage(null);
                    break;
                default:
                    break;
            }
        }
        // remove the file object from the database
        try {
            $this->em->persist($object);
            $this->em->flush();
        } catch (\Exception $exception) {
            echo 'Exception';
        }
    }
}