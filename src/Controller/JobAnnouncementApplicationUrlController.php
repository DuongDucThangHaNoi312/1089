<?php

namespace App\Controller;

use App\Entity\JobAnnouncement;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class JobAnnouncementApplicationUrlController extends CRUDController
{
    public function applicationUrlAction(JobAnnouncement $ja)
    {
        $em = $this->getDoctrine()->getManager();

        $ja->setLastTestedDate(new \DateTime());

        $em->persist($ja);
        $em->flush();

        return new RedirectResponse($ja->getApplicationUrl());
    }
}