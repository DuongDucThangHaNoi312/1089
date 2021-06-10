<?php

namespace App\Controller\City;

use App\Annotation\IgnoreSoftDelete;
use App\Entity\City;
use App\Entity\JobAnnouncement;
use App\Entity\JobAnnouncement\Lookup\JobAnnouncementStatus;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\User\JobSeekerUser\SubmittedJobTitleInterest;
use App\Form\City\Job\DepartmentType;
use App\Form\City\Job\DivisionType;
use App\Form\City\JobTitleType;
use App\Repository\City\DepartmentRepository;
use App\Repository\City\JobTitleRepository;
use App\Repository\JobTitle\Lookup\JobTitleNameRepository;
use App\Service\JobTitleML;
use Doctrine\Common\Collections\Criteria;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class JobController extends AbstractController
{

    /**
     * This method is responsible for list and filter of Submitted Interest for a given City.
     *
     * @Route("/city/{slug}/job/interest", name="city_job_interest")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @param City $city
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function interest(City $city, Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('edit', $city);

        // define and handle whichever list filters you want
        $filterForm = $this->createFormBuilder()
            ->add('jobTitle', EntityType::class, [
                'class' => City\JobTitle::class,
                'query_builder' => function (JobTitleRepository $jtr) use (&$city) {
                    return $jtr->createQueryBuilder('jt')
                        ->where('jt.city = :city')
                        ->join('jt.jobTitleName', 'jtn')
                        ->setParameter('city', $city)
                        ->groupBy('jtn.id')
                        ->orderBy('jtn.name');
                },                'required' => false,
                'label' => 'Filter by Job Title',
                'attr' => ['onchange' => 'this.form.submit();']
            ])
            ->add('department', EntityType::class, [
                'class' => City\Department::class,
                'query_builder' => function (DepartmentRepository $dr) use (&$city) {
                    return $dr->createQueryBuilder('d')
                        ->where('d.city = :city')
                        ->setParameter('city', $city)
                        ->orderBy('d.name');
                },
                'required' => false,
                'label' => 'Filter by Department',
                'attr' => ['onchange' => 'this.form.submit();']
            ])
            ->add('display', ChoiceType::class, [
                'choices' => ['Active Only' => 'active', 'Hidden Only' => 'hidden', 'Both' => 'both'],
                'required' => true,
                'label' => 'Show',
                'attr' => ['onchange' => 'this.form.submit();']
            ])
            ->add('showPerPage', ChoiceType::class, [
                'choices' => [10 => 10, 25 => 25, 50 => 50, 100 => 100],
                'attr' => ['onchange' => 'this.form.submit();']
            ])
            ->add('Go', SubmitType::class)
            ->setAction($request->getUri())
            ->setMethod('GET')
            ->getForm();

        $filterForm->handleRequest($request);

        // if linked in directly from external source
        if ($request->get('jobTitleId')) {
            $jobTitle = $this->getDoctrine()->getRepository(City\JobTitle::class)->find($request->get('jobTitleId'));
            $filterForm->get('jobTitle')->setData($jobTitle);
        }

        // set defaults and process the filter form
        $department = null;
        $show = 'active';
        $showPerPage = 50;
        $jobTitleText = '';

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();
            $jobTitle = $data['jobTitle'];
            $department = $data['department'];
            $show = $data['display'];
            $showPerPage = $data['showPerPage'];
            $jobTitleText = $jobTitle ? $jobTitle->getJobTitleName()->getName() : '';
        }

        // build query based on filter form values, if any
        $jobTitleQuery = $this->getDoctrine()->getRepository(City\JobTitle::class)->getQueryJobTitlesForCity($city, $show, $department, $jobTitleText, true);

        $pagination = $paginator->paginate(
            $jobTitleQuery,
            $request->query->getInt('page', 1),
            $showPerPage
        );

        return $this->render('city/job/interest.html.twig', [
            'city' => $city,
            'pagination' => $pagination,
            'filterForm' => $filterForm->createView(),
            'queryString' => $request->getQueryString()
        ]);

    }

    /**
     * @Route("/city/{slug}/job-title/{jobTitleId}", name="city_job_titles_submitted_interest")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobTitle", options={"mapping"={"jobTitleId"="id"}})
     * @param City $city
     * @param City\JobTitle $jobTitle
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getJobTitleSubmittedInterests(City $city, City\JobTitle $jobTitle) {

        $this->denyAccessUnlessGranted('edit', $city);
        if ($jobTitle && ($jobTitle->getCity() !== $city)) {
            throw new AccessDeniedException('Job Title does not belong to City!');
        }

        $submittedInterests = $jobTitle->getSubmittedJobTitleInterests();

        $counties = [];
        $levels = [];
        $jobTitleHelds = [];
        $yearsOfExp = [
            'Yrs with Cities' => 0,
            'Yrs in Profession' => 0
        ];
        $education = [];

        foreach ($submittedInterests as $interest) {
            $jobSeeker = $interest->getJobSeekerUser();

            // county
            $county = $jobSeeker->getCounty() . ', ' . $jobSeeker->getState();
            if ( ! key_exists($county, $counties)) {
                $counties[$county] = 0;
            }
            $counties[$county] = $counties[$county] + 1;

            // level
            /** @var JobLevel $jobLevel */
            foreach($jobSeeker->getInterestedJobLevels() as $jobLevel) {
                $level = $jobLevel->getName();
                if ( ! key_exists($level, $levels)) {
                    $levels[$level] = 0;
                }
                $levels[$level] = $levels[$level] + 1;
            }

            // job title held
            $jt = $jobSeeker->getCurrentJobTitle();
            if ($jt) {
                if ( ! key_exists($jt, $jobTitleHelds)) {
                    $jobTitleHelds[$jt] = 0;
                }
                $jobTitleHelds[$jt] = $jobTitleHelds[$jt] + 1;
            }

            $resume = $jobSeeker->getResume();
            if ($resume) {

                // years of exp
                if ($resume->getYearsWorkedInCityGovernment()) {
                    $yearsOfExp['Yrs with Cities']  = $yearsOfExp['Yrs with Cities']  + $resume->getYearsWorkedInCityGovernment();
                }

                if ($resume->getYearsWorkedInProfession()) {
                    $yearsOfExp['Yrs in Profession']  = $yearsOfExp['Yrs in Profession']  + $resume->getYearsWorkedInProfession();
                }

                // education
                if ($resume->getEducation()) {
                    foreach ($resume->getEducation() as $edu) {
                        $degree = $edu->getDegreeType()->getName();
                        if ( ! key_exists($degree, $education)) {
                            $education[$degree] = 0;
                        }
                        $education[$degree] = $education[$degree] + 1;

                    }
                }

            }
        }

        arsort($counties);
        arsort($levels);
        arsort($jobTitleHelds);
        arsort($education);
        $yearsOfExp['Yrs with Cities'] = round($yearsOfExp['Yrs with Cities']/count($submittedInterests), 0);
        $yearsOfExp['Yrs in Profession'] = round($yearsOfExp['Yrs in Profession']/count($submittedInterests), 0);

        return $this->render('city/job/interest_summary.html.twig', [
            'jobTitleName' => $jobTitle->getName(),
            'jobLevel' => $jobTitle->getLevel()->getSlug(),
            'submittedInterests' => $submittedInterests,
            'counties' => $counties,
            'levels' => $levels,
            'jobTitleHelds' => $jobTitleHelds,
            'yearsOfExp' => $yearsOfExp,
            'education' => $education
        ]);
    }

    /**
     *
     * This method manages list of Job Titles and create / edit.
     *
     * @Route("/city/{slug}/job/titles/{jobTitleId}", name="city_job_titles", defaults={"jobTitleId" = null})
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobTitle", options={"mapping"={"jobTitleId"="id"}})
     * @param City $city
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param SessionInterface $session
     * @param JobTitleML $ml
     * @param City\JobTitle|null $jobTitle
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Phpml\Exception\FileException
     * @throws \Phpml\Exception\SerializeException
     */
    public function titles(City $city, PaginatorInterface $paginator, Request $request, SessionInterface $session, JobTitleML $ml, City\JobTitle $jobTitle = null)
    {
        $this->denyAccessUnlessGranted('edit', $city);

        // jobTitle must be from City
        if ($jobTitle && ($jobTitle->getCity() !== $city)) {
            throw new AccessDeniedException('Job Title does not belong to City!');
        }

        // expand the create form on the page by default only if we are actively creating or editing a job title
        $expandJobTitleForm = false;
        $editMode = false;

        // we have a seeded list of names, but let city users create their own, so we have code to workaround
        // jobTitleName as actual entity, but represented by string of name.
        $jobTitleNameString = null;

        // if $jobTitle is defined at this point, then we are in edit mode
        if ($jobTitle) {
            $expandJobTitleForm = true;
            $editMode = true;
            $jobTitleNameString = $jobTitle->getName();
        } else {
            $jobTitle = new City\JobTitle();
            // this switch explicitly forces create form to expand, else only invalid form submission will
            if ($request->get('expandJobTitleForm')) {
                $expandJobTitleForm = true;
            }
        }

        // handle job title form
        $jobTitleForm = $this->createForm(JobTitleType::class, $jobTitle, [
            'city' => $city,
            'jobTitleNameString' => $jobTitleNameString
        ]);
        $jobTitleForm->handleRequest($request);

        if ($jobTitleForm->isSubmitted()) {
            $expandJobTitleForm = true;
            if ($jobTitleForm->isValid()) {

                /** @var City\JobTitle $jobTitle */
                $jobTitle = $jobTitleForm->getData();
                $jobTitle->setCity($city);
                $submittedJobTitleName = $jobTitleForm->get('name')->getData();

                $em           = $this->getDoctrine()->getManager();
                $jobTitleName = $em->getRepository(JobTitleName::class)->findOneBy(['name' => $submittedJobTitleName]);

                if (false == $jobTitleName) {
                    $jobTitleName = new JobTitleName();
                    $jobTitleName->setName($submittedJobTitleName);
                    $jobTitleName->setCreatedByCity($city);
                    $em->persist($jobTitleName);
                }

                /* CIT-752: Needed to set JobTitleName before ml call, needed to make level and category recommendation. */
                $jobTitle->setJobTitleName($jobTitleName);
                /* CIT-517: In City User Job Title Create, set the Category and Level fields based on the Machine Learning */
                if ( ! $jobTitle->getId()) {
                    $ml->initializeJobTitle($jobTitle);
                }

                $em->persist($jobTitle);
                $em->flush();

                $this->addFlash('success', $jobTitle->getName() . ' has been saved!');
                $session->set('closeJobTitleForm', false);

                return $this->redirectToRoute('city_job_titles', [
                    'slug' => $city->getSlug(),
                    'jobTitleId' => $jobTitle->getId()
                ]);
            } else {
                $this->addFlash('error', 'Job Title form did not validate!');
            }
        }

        // override job title form expansion based on session var... coming from valid form submit
        if ($session->get('closeJobTitleForm')) {
            $expandJobTitleForm = false;
            $session->remove('closeJobTitleForm');
        }

        // handle the list filter form
        $filterForm = $this->createFormBuilder()
                           ->add('jobTitleText', TextType::class, [
                               'required' => false,
                               'label' => 'Filter by Job Title',
                           ])
                           ->add('department', EntityType::class, [
                               'class' => City\Department::class,
                               'choices' => $city->getDepartments(),
                               'required' => false,
                               'label' => 'Filter by Department',
                               'attr' => ['onchange' => 'this.form.submit();']
                           ])
                           ->add('display', ChoiceType::class, [
                               'choices' => ['Active Only' => 'active', 'Hidden Only' => 'hidden', 'Deleted Only' => 'deleted'],
                               'required' => true,
                               'label' => 'Show',
                               'attr' => ['onchange' => 'this.form.submit();']
                           ])
                           ->add('showPerPage', ChoiceType::class, [
                               'choices' => [10 => 10, 25 => 25, 50 => 50, 100 => 100],
                               'attr' => ['onchange' => 'this.form.submit();']
                           ])
                           ->add('Go', SubmitType::class)
                           ->setAction($request->getUri())
                           ->setMethod('GET')
                           ->getForm();
        $filterForm->handleRequest($request);

        // set defaults and process the filter form
        $department = null;
        $jobTitleText = null;
        $show = 'active';
        $showPerPage = 10;

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();
            $jobTitleText = $data['jobTitleText'];
            $department = $data['department'];
            $show = $data['display'];
            $showPerPage = $data['showPerPage'];
        }

        if ('deleted' == $show) {
            $this->getDoctrine()->getManager()->getFilters()->disable('softdeleteable');
        }
        // build query based on filter form values, if any
        $jobTitleQuery = $this->getDoctrine()->getRepository(City\JobTitle::class)->getQueryJobTitlesForCity($city, $show, $department, $jobTitleText);

        $pagination = $paginator->paginate(
            $jobTitleQuery,
            $request->query->getInt('page', 1),
            $showPerPage
        );
        if ('deleted' == $show) {
            $this->getDoctrine()->getManager()->getFilters()->enable('softdeleteable');
        }

        return $this->render('city/job/titles.html.twig', [
            'city' => $city,
            'jobTitle' => $jobTitle,
            'pagination' => $pagination,
            'filterForm' => $filterForm->createView(),
            'jobTitleForm' => $jobTitleForm->createView(),
            'expandJobTitleForm' => $expandJobTitleForm,
            'editMode' => $editMode,
            'queryString' => $request->getQueryString()
        ]);
    }


    /**
     * @Route("/city/{slug}/job/vacancies", name="city_job_vacancies")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @param City $city
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function vacancies(City $city, PaginatorInterface $paginator, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $city);

        $filterForm = $this->createFormBuilder()
            ->add('jobTitleText', TextType::class, [
                'required' => false,
                'label' => 'Filter by Job Title',
            ])
            ->add('department', EntityType::class, [
                'class' => City\Department::class,
                'choices' => $city->getDepartments(),
                'required' => false,
                'label' => 'Filter by Department',
                'attr' => ['onchange' => 'this.form.submit();']
            ])
            ->add('display', ChoiceType::class, [
                'choices' => ['Active Only' => 'active', 'Hidden Only' => 'hidden', 'Both' => 'both'],
                'required' => true,
                'label' => 'Show',
                'attr' => ['onchange' => 'this.form.submit();']
            ])
            ->add('showPerPage', ChoiceType::class, [
                'choices' => [10 => 10, 25 => 25, 50 => 50, 100 => 100],
                'attr' => ['onchange' => 'this.form.submit();']
            ])
            ->add('Go', SubmitType::class)
            ->setAction($request->getUri())
            ->setMethod('GET')
            ->getForm();

        $filterForm->handleRequest($request);

        $department = null;
        $jobTitleText = null;
        $show = 'active';
        $showPerPage = 10;

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();
            $jobTitleText = $data['jobTitleText'];
            $department = $data['department'];
            $show = $data['display'];
            $showPerPage = $data['showPerPage'];
        }

        $jobTitleQuery = $this->getDoctrine()->getRepository(City\JobTitle::class)->getQueryJobTitlesForCity($city, $show, $department, $jobTitleText);

        $pagination = $paginator->paginate(
            $jobTitleQuery,
            $request->query->getInt('page', 1),
            $showPerPage
        );

        return $this->render('city/job/vacancies.html.twig', [
            'city' => $city,
            'pagination' => $pagination,
            'filterForm' => $filterForm->createView()
        ]);
    }

    /**
     * @Route("/city/{slug}/job/title/{jobTitleId}/toggle-hidden", name="toggle-job-title-hidden")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobTitle", options={"mapping"={"jobTitleId"="id"}})
     * @param City $city
     * @param City\JobTitle $jobTitle
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function toggleJobTitleHidden(City $city, City\JobTitle $jobTitle, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $city);

        // jobTitle must be from City
        if ($jobTitle->getCity() !== $city) {
            throw new AccessDeniedException('Job Title does not belong to City!');
        }

        $em = $this->getDoctrine()->getManager();

        $requiredVisiblePercent = $city->getSubscription()->getSubscriptionPlan()->getJobTitleMaintenancePercentage();

        if ($jobTitle->getIsHidden()) {
            $jobTitle->setIsHidden(false);
            $jobTitle->setHiddenOn(null);
            $this->addFlash('success', $jobTitle->getName().' was unhidden.');
        } else {

            if (false == $this->checkCityCompliance($city, 1)) {
                $wouldBeVisiblePercent = $city->getWouldBePercentageJobTitlesVisible(1);
                $this->addFlash('error', 'You cannot hide any more job titles. Your subscription requires that '
                    .number_format($requiredVisiblePercent, 0).'% job titles be visible and hiding this job title would leave you at '
                    .number_format($wouldBeVisiblePercent*100,2).'%.');
                return $this->redirect($request->headers->get('referer'));
            }

            // CIT-800: When a City User hides a Job Title, provide them a notice flash message (warning, yellow).
            $wouldBeVisiblePercent = $city->getWouldBePercentageJobTitlesVisible(1);
            $wouldBeStarCount = $this->getCityStarCount($wouldBeVisiblePercent);
            if ($wouldBeStarCount != $city->getCurrentStars()) {
                $this->addFlash('warning', 'Your city star count has been reduced because your percentage of visible jobs has dropped.');
            }

            $jobTitle->setIsHidden(true);
            $jobTitle->setHiddenOn(new \DateTime());
            $this->addFlash('success', $jobTitle->getName().' was hidden.');

        }
        $em->persist($jobTitle);

        /*** CIT-797: When a City User hides or unhides a Job Title, update their currentStars ***/
        $visiblePercent = $city->getPercentageJobTitlesVisible();
        $city->setCurrentStars($this->getCityStarCount($visiblePercent));
        $em->persist($city);
        $em->flush();

        $this->addFlash('info', number_format($visiblePercent*100, 2).
            '% of your city\'s job titles are visible. Your subscription requires that '
            .number_format($requiredVisiblePercent, 0).'% remain visible.');

        return $this->redirect($request->headers->get('referer'));
    }

    private function getCityStarCount($jobTitleVisiblePercent) {
        $retVal                 = 1;
        $jobTitleVisiblePercent = number_format($jobTitleVisiblePercent * 100);

        if ($jobTitleVisiblePercent >= 80) {
            $retVal = 5;
        } elseif ($jobTitleVisiblePercent >= 60) {
            $retVal = 4;
        } elseif ($jobTitleVisiblePercent >= 40) {
            $retVal = 3;
        } elseif ($jobTitleVisiblePercent >= 20) {
            $retVal = 2;
        }

        return $retVal;
    }

    /**
     * @Route("/city/{slug}/job/title/{jobTitleId}/toggle-vacancy", name="toggle-job-title-vacancy")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobTitle", options={"mapping"={"jobTitleId"="id"}})
     * @param City $city
     * @param City\JobTitle $jobTitle
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleJobTitleVacancy(City $city, City\JobTitle $jobTitle, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $city);

        // jobTitle must be from City
        if ($jobTitle->getCity() !== $city) {
            throw new AccessDeniedException('Job Title does not belong to City!');
        }

        if ($jobTitle->getIsHidden()) {
            $this->addFlash('error', 'Job Title must be unhidden before it can be marked vacant.');
            return $this->redirect($request->headers->get('referer'));
        }

        $em = $this->getDoctrine()->getManager();
        $jaRepo = $em->getRepository(JobAnnouncement::class);

        if (true == $jobTitle->getIsVacant()) {
            // when a job title is marked filled, we delete any linked Job Announcement that is "to do", and and move others to "archive"
            // CIT-158
            $jobTitle->setIsVacant(false);
            $jobTitle->setMarkedVacantBy(null);
            $jobAnnouncements = $jaRepo->findBy(['jobTitle' => $jobTitle], ['status' => 'ASC']);
            foreach ($jobAnnouncements as $jobAnnouncement) {
                switch ($jobAnnouncement->getStatus()->getId()) {
                    case JobAnnouncement::STATUS_TODO:
                        $em->remove($jobAnnouncement);
                        $this->addFlash('warning', 'Related "To Post" Job Announcement deleted.');
                        break;
                    case JobAnnouncement::STATUS_ARCHIVED:
                        break;
                    default:
                        $jobAnnouncement->setStatus($em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_ARCHIVED));
                        $jobAnnouncement->setAssignedTo(null);
                        $this->addFlash('warning', 'Related Job Announcement moved to archive.');
                }
            }
            $this->addFlash('success', $jobTitle->getName().' has been marked Filled.');

            $em->persist($jobTitle);
            $em->flush();

//            return $this->redirectToRoute('city_job_vacancies', ['slug' => $city->getSlug()]);
//            return $this->redirectToRoute('city_job_titles', ['slug' => $city->getSlug()]);
            $redirectURL = $request->headers->get('referer');
            return $this->redirect($redirectURL);

        } else {
            // when a job title is marked vacant, we automatically generate a Job Announcement in "To Do" status
            // unless there is a previous Job Announcement for the same title in "Archive", then we pull copy that one into
            // a new announcement.
            // CIT-158, CIT-365

            if (false == $this->checkCityCompliance($city)) {
                $this->addFlash('error', 'Your city currently has more job titles hidden than are allowed for your subscription type. You will not be able to create new postings until your account comes into compliance.');
                return $this->redirect($request->headers->get('referer'));
            } elseif (
                $city->getSubscription()->getSubscriptionPlan()->getAllowedActiveJobPostings()
                &&
                $jaRepo->getCountActiveJobAnnouncementsForCity($city) >= $city->getSubscription()->getSubscriptionPlan()->getAllowedActiveJobPostings()
            ) {
                $this->addFlash('error', 'Your city\'s subscription plan does not allow any more job postings. Please upgrade your subscription to create more.');
                return $this->redirect($request->headers->get('referer'));
            } else {
                $jobTitle->setIsVacant(true);
                $jobTitle->setMarkedVacantBy($this->getUser());
                $this->addFlash('success', $jobTitle->getName().' has been marked Vacant.');
                $oldJobAnnouncement = $em->getRepository(JobAnnouncement::class)->findOneBy(['jobTitle' => $jobTitle, 'status' => JobAnnouncement::STATUS_ARCHIVED]);
                if ($oldJobAnnouncement) {
                    $jobAnnouncement = clone $oldJobAnnouncement;
                    $jobAnnouncement->setStatus($em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_DRAFT));
                    $this->addFlash('warning', $jobTitle->getName().' has an archived Job Announcement which has been cloned into Draft status.');
                } else {
                    $jobAnnouncement = new JobAnnouncement();
                    $jobAnnouncement->setJobTitle($jobTitle)->setStatus($em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_TODO));
                    $this->addFlash('warning', $jobTitle->getName().' has a new Job Announcement in To Do status.');
                }
                $jobAnnouncement->setAssignedTo($this->getUser());
                $em->persist($jobAnnouncement);

                /* CIT-1010: default city address */
                if ( ! $jobAnnouncement->getStreet() && ! $jobAnnouncement->getCity() && ! $jobAnnouncement->getZipcode()) {
                    $jobAnnouncement->setCity($city);
                    $jobAnnouncement->setStreet($city->getAddress());
                    $jobAnnouncement->setZipcode($city->getZipCode());
                }

                $em->persist($jobTitle);
                $em->flush();

//            return $this->redirectToRoute('city_job_vacancies', ['slug' => $city->getSlug()]);
//                return $this->redirectToRoute('city_job_titles', ['slug' => $city->getSlug()]);
                return $this->redirectToRoute('city_job_announcements', ['slug' => $city->getSlug(), 'status' => 'to-do']);
//                $redirectURL = $request->headers->get('referer');
//                return $this->redirect($redirectURL);
            }
        }
    }

    private function checkCityCompliance(City $city, $wouldBeCount = 0)
    {
        $requiredVisiblePercent = $city->getSubscription()->getSubscriptionPlan()->getJobTitleMaintenancePercentage();
        $wouldBeVisiblePercent = $city->getWouldBePercentageJobTitlesVisible($wouldBeCount);

        if (($wouldBeVisiblePercent*100) < $requiredVisiblePercent) {
            return false;
        }
        return true;
    }

    /**
     * @Route("/city/{slug}/job-title/{jobTitleId}/undelete", name="city_jobtitle_undelete")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobTitle", options={"mapping"={"jobTitleId"="id"}})
     * @IgnoreSoftDelete()
     * @param City $city
     * @param City\JobTitle $jobTitle
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function undeleteJobTitle(City $city, City\JobTitle $jobTitle, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $city);

        if ($jobTitle && ($jobTitle->getCity() != $city)) {
            throw new AccessDeniedException('JobTitle does not belong to City!');
        }

        $jobTitleName = $jobTitle->getName();
        try {
            $jobTitle->setDeletedAt(null);
            $em = $this->getDoctrine()->getManager();
            $em->persist($jobTitle);
            $em->flush();
            $this->addFlash('success', 'Successfully undeleted ' . $jobTitleName . ' job title');
        }catch (\Exception $exception) {
            $message = sprintf("Error: %s job title could not be undeleted", $jobTitleName);
            $this->addFlash('error', $message);
        }

        return $this->redirect($request->headers->get('referer'));    }

    /**
     * @Route("/city/{slug}/job-title/{jobTitleId}/delete", name="city_jobtitle_delete")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobTitle", options={"mapping"={"jobTitleId"="id"}})
     * @param City $city
     * @param City\JobTitle $jobTitle
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteJobTitle(City $city, City\JobTitle $jobTitle, Request $request) {
        $this->denyAccessUnlessGranted('edit', $city);

        if ($jobTitle && ($jobTitle->getCity() != $city)) {
            throw new AccessDeniedException('JobTitle does not belong to City!');
        }

        if (count($jobTitle->getJobAnnouncements()) > 0) {
            $this->addFlash('warning', 'Cannot delete ' . $jobTitle->getName() . ' job title because ' . count($jobTitle->getJobAnnouncements()) . ' job announcements are associated with it');
        } else {
            $jobTitleName = $jobTitle->getName();
            try {
                $jobTitle->removeAllJobTitleInterest();
                $em = $this->getDoctrine()->getManager();
                $em->remove($jobTitle);
                $em->flush();
                $this->addFlash('success', 'Successfully deleted ' . $jobTitleName . ' job title');
            }catch (\Exception $exception) {
                $message = sprintf("Error: %s job title could not be deleted", $jobTitleName);
                $this->addFlash('error', $message);
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     *
     * This method manages list of Job Titles and create / edit.
     *
     * @Route("/city/{slug}/departments/{departmentId}", name="city_departments", defaults={"departmentId" = null})
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("department", options={"mapping"={"departmentId"="id"}})
     * @param City $city
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param SessionInterface $session
     * @param City\Department|null $department
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function departments(City $city, PaginatorInterface $paginator, Request $request, SessionInterface $session, City\Department $department = null)
    {
        $this->denyAccessUnlessGranted('edit', $city);

        // department must be from City
        if ($department && ($department->getCity() !== $city)) {
            throw new AccessDeniedException('Department does not belong to City!');
        }

        // expand the create form on the page by default only if we are actively creating or editing a department
        $expandDepartmentForm = false;
        $editMode = false;

        // If $department is defined at this point, then we are in edit mode
        if ($department) {
            $expandDepartmentForm = true;
            $editMode = true;
        } else {
            $department = new City\Department();
            // this switch implicitly forces form to expand, else only invalid form submission will
            if ($request->get('expandDepartmentForm')) {
                $expandDepartmentForm = true;
            }
        }

        // Handle Department form
        $department->setCity($city);
        $departmentForm = $this->createForm(DepartmentType::class, $department);
        $departmentForm->handleRequest($request);

        if ($departmentForm->isSubmitted()) {
            $expandDepartmentForm = true;
            if ($departmentForm->isValid()) {
                $orderBy = 0;
                foreach ($city->getDepartments() as $dpm) {
                    $orderBy = max($orderBy, $dpm->getOrderByNumber());
                }

                /** @var City\Department $department */
                $department = $departmentForm->getData();
                $department->setOrderByNumber($orderBy + 1);
                $em = $this->getDoctrine()->getManager();
                $em->persist($department);
                $em->flush();
                $this->addFlash('success', $department->getName() . ' Department has been saved!');
                $session->set('closeDepartmentForm', true);
                return $this->redirect($request->headers->get('referer'));
            } else {
                foreach ($departmentForm->getErrors() as $error) {
                    $this->addFlash('error', $error->getMessage());
                }

            }
        }

        // override department form expansion based on session var... coming from valid form submit
        if ($session->get('closeDepartmentForm')) {
            $expandDepartmentForm = false;
            $session->remove('closeDepartmentForm');
        }


        // Handle the list filter form
        $filterForm = $this->createFormBuilder()
            ->add('showPerPage', ChoiceType::class, [
                'choices' => [10 => 10, 25 => 25, 50 => 50, 100 => 100],
                'attr' => ['onchange' => 'this.form.submit();']
            ])
            ->add('Go', SubmitType::class)
            ->setAction($request->getUri())
            ->setMethod('GET')
            ->getForm();

        $filterForm->handleRequest($request);

        // set defaults and process the filter form
        $showPerPage = 10;

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();
            $showPerPage = $data['showPerPage'];
        }

        $departmentQuery = $this->getDoctrine()->getRepository(City\Department::class)->getQueryForDepartmentsForCity($city);

        $pagination = $paginator->paginate(
            $departmentQuery,
            $request->query->getInt('page', 1),
            $showPerPage
        );


        return $this->render('city/job/departments.html.twig', [
            'city' => $city,
            'pagination' => $pagination,
            'expandDepartmentForm' => $expandDepartmentForm,
            'departmentForm' => $departmentForm->createView(),
            'editMode' => $editMode,
            'queryString' => $request->getQueryString(),
            'filterForm' => $filterForm->createView(),
        ]);
    }

    /**
     * @Route("/city/{slug}/departments/{departmentId}/delete", name="city_department_delete")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("department", options={"mapping"={"departmentId"="id"}})
     * @param City $city
     * @param City\Department $department
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteDepartment(City $city, City\Department $department, Request $request) {
        $this->denyAccessUnlessGranted('edit', $city);

        if ($department && ($department->getCity() != $city)) {
            throw new AccessDeniedException('Department does not belong to City!');
        }

        if (count($department->getJobTitles()) > 0) {
            $this->addFlash('warning', 'Cannot delete ' . $department->getName() . ' department because ' . count($department->getJobTitles()) . ' job titles are associated with it');
        } elseif (count($department->getDivisions()) > 0) {
            $this->addFlash('warning', 'Cannot delete ' . $department->getName() . ' department because ' . count($department->getDivisions()) . ' divisions are associated with it');
        } else {
            $departmentName = $department->getName();
            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($department);
                $em->flush();
                $this->addFlash('success', 'Successfully deleted ' . $departmentName . ' department');
            } catch (\Exception $exception) {
                $message = sprintf("Error: %s department could not be deleted", $departmentName);
                $this->addFlash('error', $message);
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     *
     * This method manages list of Job Titles and create / edit.
     *
     * @Route("/city/{slug}/divisions/{divisionId}", name="city_divisions", defaults={"divisionId" = null})
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("division", options={"mapping"={"divisionId"="id"}})
     * @param City $city
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param SessionInterface $session
     * @param City\Division|null $division
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function divisions(City $city, PaginatorInterface $paginator, Request $request, SessionInterface $session, City\Division $division = null)
    {
        $this->denyAccessUnlessGranted('edit', $city);

        // division must be from City
        if ($division && ($division->getCity() !== $city)) {
            throw new AccessDeniedException('Division does not belong to City!');
        }

        // expand the create form on the page by default only if we are actively creating or editing a division
        $expandDivisionForm = false;
        $editMode = false;

        // If $division is defined at this point, then we are in edit mode
        if ($division) {
            $expandDivisionForm = true;
            $editMode = true;
        } else {
            $division = new City\Division();
            // this switch implicitly forces form to expand, else only invalid form submission will
            if ($request->get('expandDivisionForm')) {
                $expandDivisionForm = true;
            }
        }

        // Handle Division form
        $divisionForm = $this->createForm(DivisionType::class, $division);
        $divisionForm->handleRequest($request);

        if ($divisionForm->isSubmitted()) {
            $expandDivisionForm = true;
            if ($divisionForm->isValid()) {
                /** @var City\Division $division */
                $division = $divisionForm->getData();
                $division->setCity($city);
                $em = $this->getDoctrine()->getManager();
                $em->persist($division);
                $em->flush();
                $this->addFlash('success', $division->getName() . ' Division has been saved!');
                $session->set('closeDivisionForm', true);
                return $this->redirect($request->headers->get('referer'));
            } else {
                $this->addFlash('error', 'Division form did not validate!');
            }
        }

        // override Division form expansion based on session var... coming from valid form submit
        if ($session->get('closeDivisionForm')) {
            $expandDivisionForm = false;
            $session->remove('closeDivisionForm');
        }

        // Handle the list filter form
        $filterForm = $this->createFormBuilder()
            ->add('showPerPage', ChoiceType::class, [
                'choices' => [10 => 10, 25 => 25, 50 => 50, 100 => 100],
                'attr' => ['onchange' => 'this.form.submit();']
            ])
            ->add('Go', SubmitType::class)
            ->setAction($request->getUri())
            ->setMethod('GET')
            ->getForm();

        $filterForm->handleRequest($request);

        // set defaults and process the filter form
        $showPerPage = 10;

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();
            $showPerPage = $data['showPerPage'];
        }

        $divisionQuery = $this->getDoctrine()->getRepository(City\Division::class)->getQueryForDivisionsForCity($city);

        $pagination = $paginator->paginate(
            $divisionQuery,
            $request->query->getInt('page', 1),
            $showPerPage
        );


        return $this->render('city/job/divisions.html.twig', [
            'city' => $city,
            'pagination' => $pagination,
            'expandDivisionForm' => $expandDivisionForm,
            'divisionForm' => $divisionForm->createView(),
            'editMode' => $editMode,
            'queryString' => $request->getQueryString(),
            'filterForm' => $filterForm->createView(),
        ]);
    }


    /**
     * @Route("/city/{slug}/divisions/{divisionId}/delete", name="city_division_delete")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("division", options={"mapping"={"divisionId"="id"}})
     * @param City $city
     * @param City\Division $division
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteDivision(City $city, City\Division $division, Request $request) {
        $this->denyAccessUnlessGranted('edit', $city);

        if ($division && ($division->getCity() != $city)) {
            throw new AccessDeniedException('Division does not belong to City!');
        }

        if (count($division->getJobTitles()) > 0) {
            $this->addFlash('warning', 'Cannot delete ' . $division->getName() . ' division because ' . count($division->getJobTitles()) . ' job titles are associated with it');
        } else {
            $divisionName = $division->getName();
            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($division);
                $em->flush();
                $this->addFlash('success', 'Successfully deleted ' . $divisionName . ' division');
            }catch (\Exception $exception) {
                $message = sprintf("Error: %s division could not be deleted", $divisionName);
                $this->addFlash('error', $message);
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     *
     * @Route("/city/{slug}/update/{id}/department", name="city_update_department")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("department", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param Request $request
     * @param City\Department|null $department
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateDepartment(City $city, Request $request, City\Department $department)
    {
        $this->denyAccessUnlessGranted('edit', $city);
        $em     = $this->getDoctrine()->getManager();
        $toggle = $request->get('toggle');

        if ($toggle == "true") {
            $department->setHideOnProfilePage(! $department->getHideOnProfilePage());
            $em->flush();
        }


        return $this->json(['message' => 'Department updated successfully.']);
    }

    /**
     *
     * @Route("/city/update/department/sortable", name="city_update_department_sortable")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateDepartmentSortable(Request $request)
    {
        $data           = [];
        $em             = $this->getDoctrine()->getManager();
        $departmentIds  = $request->get('departmentIds');
        $orderByNumbers = $request->get('orderByNumbers');

        foreach ($departmentIds as $key => $id) {
            $data[$id] = $orderByNumbers[$key];
        }

        $departments = $em->getRepository(City\Department::class)->getDepartmentsByIds($departmentIds);

        /** @var City\Department $department */
        foreach ($departments as $department) {
            if (in_array($department->getId(), array_keys($data))) {
                $department->setOrderByNumber($data[$department->getId()]);
            }
        }

        $em->flush();

        return $this->json(['success' => 'true']);
    }
}
