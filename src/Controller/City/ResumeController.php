<?php

namespace App\Controller\City;

use App\Entity\City\County;
use App\Entity\User\CityUser\SavedResume;
use App\Entity\User\JobSeekerUser\Resume;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\User\CityUser;
use App\Form\City\Resume\SearchFilterType;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

class ResumeController extends AbstractController
{
    /**
     * @Route("/city/resume/search", name="city_resume_search")
     */
    public function resumeSearch(Request $request, PaginatorInterface $paginator, RouterInterface $router)
    {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');
        /** @var CityUser $user */
        $user = $this->getUser();

        $filterForm = $this->createForm(SearchFilterType::class, null, [
            'action' => $request->getUri(),
            'reset' => $request->get('reset'),
            'method' => 'GET'
        ]);

        $filterForm->handleRequest($request);

        $showPerPage = 20;
        $searchData = [];

        if (($filterForm->isSubmitted() && $filterForm->isValid())) {
            $searchData = $filterForm->getData();
        } elseif (false == $request->get('reset')) {
            // Initial Access should default to state and county
            $searchData['state'] = $user->getCity()->getStateFromCounty();
            $searchData['counties'] = $user->getCity()->getCounties();
            $filterForm->get('counties')->setData($searchData['counties']);
            $filterForm->get('state')->setData($searchData['state']);
        }
        $resumeRepository = $this->getDoctrine()->getRepository(Resume::class);
        $resumeQuery = $resumeRepository->getQueryWithSearchFilterData($user->getCity(), $searchData);
        $paginator->setDefaultPaginatorOptions([PaginatorInterface::PAGE_PARAMETER_NAME => 'resumePage']);
        $pagination = $paginator->paginate(
            $resumeQuery,
            $request->query->getInt('resumePage', 1),
            $showPerPage
        );

        $savedResumeIds = [];
        if (count($user->getSavedResumes()) != 0) {
            $savedResumeIds = array_map(function(SavedResume $savedResume) {
                return $savedResume->getResume()->getId();
            }, $user->getSavedResumes()->getValues());
        }

        return $this->render('city/resume/search.html.twig', [
            'savedResumes' => $savedResumeIds,
            'pagination' => $pagination,
            'filterForm' => $filterForm->createView(),
        ]);

    }

    /**
     * @Route("/city/resume/{id}/save", name="city_resume_save")
     * @param Request $request
     * @param Resume $resume
     * @return Response
     */
    public function saveResume(Request $request, Resume $resume) {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');

        /** @var CityUser $user */
        $user = $this->getUser();

        $savedResume = new CityUser\SavedResume();
        $savedResume->setCityUser($user);
        $savedResume->setResume($resume);

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($savedResume);
            $em->flush();
            $this->addFlash('success', 'Success! Resume has been saved.');
        } catch(\Exception $exception) {
            $this->addFlash('error', 'Error! Unable to save resume at this time, please try again.');
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/city/resume/saved", name="city_resume_saved")
     */
    public function resumeSaved(Request $request, PaginatorInterface $paginator, RouterInterface $router) {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');
        /** @var CityUser $user */
        $user = $this->getUser();

        $filterForm = $this->createForm(SearchFilterType::class, null, [
            'action' => $request->getUri(),
            'method' => 'GET',
            'reset'  => $request->get('reset'),
        ]);

        $filterForm->handleRequest($request);

        $showPerPage = 20;
        $searchData = [];

        if (($filterForm->isSubmitted() && $filterForm->isValid()) && false == $request->get('reset')) {
            $searchData = $filterForm->getData();
        }

        $resumeRepository = $this->getDoctrine()->getRepository(CityUser\SavedResume::class);
        $resumeQuery = $resumeRepository->getQueryWithSearchFilterData($user, $searchData);
        $paginator->setDefaultPaginatorOptions([PaginatorInterface::PAGE_PARAMETER_NAME => 'resumePage']);
        $pagination = $paginator->paginate(
            $resumeQuery,
            $request->query->getInt('resumePage', 1),
            $showPerPage
        );

        return $this->render('city/resume/saved.html.twig', [
            'pagination' => $pagination,
            'filterForm' => $filterForm->createView(),
        ]);
    }


    /**
     * @Route("/city/resume/{resumeId}/saved/{savedResumeId}/remove", name="city_remove_saved_resume")
     * @ParamConverter("savedResume", options={"mapping"={"savedResumeId"="id"}})
     * @ParamConverter("resume", options={"mapping"={"resumeId"="id"}})
     * @param Request $request
     * @param Resume $resume
     * @param CityUser\SavedResume $savedResume
     * @return Response
     */
    public function deleteSavedResume(Request $request, Resume $resume, CityUser\SavedResume $savedResume) {
        $this->denyAccessUnlessGranted('edit', $savedResume);

        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($savedResume);
            $em->flush();
            $this->addFlash('success', 'Success! Saved Resume has been removed successfully.');
        } catch(\Exception $exception) {
            $this->addFlash('error', 'Error! Unable to remove Saved Resume at this time, please try again.');
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/city/resume/{id}/save", name="city_resume_save")
     * @param Resume $resume
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function save(Resume $resume, Request $request, ValidatorInterface $validator)
    {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');

        $savedResume = new SavedResume();
        $savedResume->setResume($resume);
        $savedResume->setCityUser($this->getUser());

        $errors = $validator->validate($savedResume);
        if (count($errors)) {
            foreach ($errors as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->persist($savedResume);
            $em->flush();

            $this->addFlash('success', 'You have saved the resume to your dashboard.');
        }

        $redirectURL = $request->headers->get('referer');

        return $this->redirect($redirectURL);

    }
}
