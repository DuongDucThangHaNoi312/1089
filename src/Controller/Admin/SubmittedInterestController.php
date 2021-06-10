<?php


namespace App\Controller\Admin;


use App\Entity\City\JobTitle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SubmittedInterestController extends AbstractController
{
    /**
     * @Route("/submitted-interest/{id}")
     */
    public function getJobTitleSubmittedInterests(Request $request, $id)
    {
        $em       = $this->getDoctrine()->getManager();
        $jobTitle = $em->getRepository(JobTitle::class)->find($id);
        $total    = 0;
        if ($jobTitle) {
            $total = $jobTitle->getSubmittedJobTitleInterestCount();
        }
        if ( ! $request->isXmlHttpRequest()) {
            return $this->redirectToRoute('/');
        }

        return $this->json(['submittedInterestTotal' => $total]);
    }
}
