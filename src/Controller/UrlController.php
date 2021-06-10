<?php

namespace App\Controller;

use App\Entity\Url;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UrlController extends CRUDController
{
    public function testUrlAction(Request $request, Url $url)
    {
        $em = $this->getDoctrine()->getManager();

        $url->setLastTestedDate(new \DateTime());
        $url->setIsUrlTested(true);

        $em->persist($url);
        $em->flush();

//        $url = $this->get('router')->generate('admin_app_city_edit', ['id' => $url->getCity()->getId()]);
//        $url = $request->headers->get('referer');
        return new RedirectResponse($url->getValue());
    }
}