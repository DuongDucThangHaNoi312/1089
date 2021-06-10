<?php

namespace App\Controller\JobSeeker;

use App\Entity\City;
use App\Entity\City\County;
use App\Entity\City\State;
use App\Entity\JobTitle\Lookup\JobCategory;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\SavedCity;
use App\Form\JobSeeker\Registration\JobSeekerProfileType;
use App\Service\LocationGetter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountController extends AbstractController
{
    /**
     * @Route("/job/seeker/account", name="job_seeker_account")
     */
    public function index()
    {
        return $this->render('job_seeker/account/index.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }

    /**
     * @Route("/job-seeker/account/information", name="job_seeker_profile_edit")
     *
     * @param Request $request
     * @param LocationGetter $locationGetter
     *
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function jobSeekerProfileEdit(Request $request, LocationGetter $locationGetter, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // CIT-807: Redirecting to registration flow. If they have not completed it.
        if ($this->isGranted('ROLE_PENDING_JOBSEEKER')) {
            $this->addFlash('warning', 'Please complete your Registration before accessing your account');
            /** @var JobSeekerUser $user */
            $user = $this->getUser();
            $url = $this->generateUrl('job_seeker_registration_step_two');
            if ($user->getConfirmationToken()) {
                $url = $this->generateUrl('job_seeker_registration_step_one_verify');
            } elseif ($user->getCity() && $user->getState()) {
                $url = $this->generateUrl('job_seeker_registration_step_three');
            }
            return $this->redirect($url);
        }

        $em = $this->getDoctrine()->getManager();
        /** @var JobSeekerUser $jobSeekerUser */
        $jobSeekerUser = $this->getUser();

        $form = $this->createForm(JobSeekerProfileType::class, $jobSeekerUser, [
            'profile' => true,
            'step3' => false,
            'validation_groups' => 'Default'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $jobSeekerUser = $form->getData();
            $jobSeekerUser->setUsername($jobSeekerUser->getEmail());

            // save user location
            $residentLocation = $form->get('residentLocation')->getData();
            if ($residentLocation) {
                $locationGetter->setJobSeekerLocation($jobSeekerUser, $residentLocation);
            }

            $em->flush();

            $this->addFlash('success', $translator->trans('job_seeker.profile_edit.saved_succeed'));

            $url      = $this->generateUrl('job_seeker_profile_edit');
            $response = new RedirectResponse($url);
            return $response;
        }

        return $this->render('job_seeker/account/profile-edit.html.twig', [
            'formProfileEdit' => $form->createView()
        ]);
    }
}
