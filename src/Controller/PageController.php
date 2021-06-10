<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\City;
use App\Entity\City\County;
use App\Entity\CMSJobCategory;
use App\Entity\ContactForm;
use App\Entity\JobAnnouncement;
use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan;
use App\Entity\Url;
use App\Entity\User;
use App\Entity\User\JobSeekerUser;
use App\Form\ContactFormType;
use App\Service\DowngradeSubscriptions;
use App\Service\EmailHelper;
use Doctrine\ORM\EntityManager;
use App\Form\Homepage\HomepageJobSearchType;
use App\Form\Homepage\HomepageLinkSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


class PageController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {

        $formFactory = $this->get('form.factory');
        $jobSearchForm = $formFactory->createNamed('homepage_job_search', HomepageJobSearchType::class, null, [
            'action' => $this->generateUrl('job_seeker_jobtitle_search')
        ]);

        $secondJobSearchForm = $formFactory->createNamed('second_homepage_job_search', HomepageJobSearchType::class, null, [
                'action' => $this->generateUrl('job_seeker_jobtitle_search')
        ]);

        $linkSearchForm = $this->createForm(HomepageLinkSearchType::class, null, [
            'action' => $this->generateUrl('city_search')
        ]);

        $countyRepo = $this->getDoctrine()->getRepository(County::class);


        $counties = [
            [
                [
                    'county'      => $countyRepo->findOneBySlug('alameda-county'),
                    'short-name'  => 'Alameda',
                    'city-count'  => 14,
                    'job-count'   => '3,000',
                    'img'         => 'counties-served-01-alameda.jpg'
                ],
                [
                    'county'      => $countyRepo->findOneBySlug('contra-costa-county'),
                    'short-name'  => 'Contra Costa',
                    'city-count'  => 19,
                    'job-count'   => '1,550',
                    'img'         => 'counties-served-02-contra-costa.jpg'
                ],
            ],

            [
                [
                    'county'      => $countyRepo->findOneBySlug('marin-county'),
                    'short-name'  => 'Marin',
                    'city-count'  => 11,
                    'job-count'   => '600',
                    'img'         => 'counties-served-03-marin.jpg'
                ],
                [
                    'county'      => $countyRepo->findOneBySlug('monterey-county'),
                    'short-name'  => 'Monterey',
                    'city-count'  => 12,
                    'job-count'   => '850',
                    'img'         => 'counties-served-12-monterey.jpg'
                ],
            ],

            [
                [
                    'county'     => $countyRepo->findOneBySlug('napa-county'),
                    'short-name' => 'Napa',
                    'city-count' => 5,
                    'job-count'  => '400',
                    'img'        => 'counties-served-10-napa.jpg'
                ],
                [
                    'county'     => $countyRepo->findOneBySlug('placer-county'),
                    'short-name' => 'Placer',
                    'city-count' => 6,
                    'job-count'  => '750',
                    'img'        => 'counties-served-15-placer.jpg'
                ],
            ],
            [
                [
                    'county'     => $countyRepo->findOneBySlug('san-francisco-county'),
                    'short-name' => 'San Francisco',
                    'city-count' => 1,
                    'job-count'  => '2,200',
                    'img'        => 'counties-served-16-san-francisco.jpg'
                ],
                [
                    'county'     => $countyRepo->findOneBySlug('san-joaquin-county'),
                    'short-name' => 'San Joaquin',
                    'city-count' => 7,
                    'job-count'  => '1,000',
                    'img'        => 'counties-served-14-lodi.jpg'
                ],
            ],

            [
                [
                    'county'      => $countyRepo->findOneBySlug('sacramento-county'),
                    'short-name'  => 'Sacramento',
                    'city-count'  => 7,
                    'job-count'   => '1,200',
                    'img'         => 'counties-served-04-sacramento.jpg'
                ],
                [
                    'county'      => $countyRepo->findOneBySlug('san-mateo-county'),
                    'short-name'  => 'San Mateo',
                    'city-count'  => 20,
                    'job-count'   => '1,800',
                    'img'         => 'counties-served-05-san-mateo.jpg'
                ],
            ],

            [
                [
                    'county'      => $countyRepo->findOneBySlug('santa-clara-county'),
                    'short-name'  => 'Santa Clara',
                    'city-count'  => 15,
                    'job-count'   => '3,300',
                    'img'         => 'counties-served-06-santa-clara.jpg'
                ],
                [
                    'county'      => $countyRepo->findOneBySlug('santa-cruz-county-1'),
                    'short-name'  => 'Santa Cruz',
                    'city-count'  => 4,
                    'job-count'   => '700',
                    'img'         => 'counties-served-07-santa-cruz.jpg'
                ],
            ],

            [
                [
                    'county'      => $countyRepo->findOneBySlug('solano-county'),
                    'short-name'  => 'Solano',
                    'city-count'  => 7,
                    'job-count'   => '850',
                    'img'         => 'counties-served-09-solano.jpg'
                ],
                [
                    'county'      => $countyRepo->findOneBySlug('sonoma-county'),
                    'short-name'  => 'Sonoma',
                    'city-count'  => 8,
                    'job-count'   => '850',
                    'img'         => 'counties-served-11-sonoma.jpg'
                ],
            ]
        ];

        /** @var County $yoloCounty */
        $yoloCounty = $countyRepo->findOneBySlug('yolo-county');
        $california = $yoloCounty->getState();

        return $this->render('page/home.html.twig', [
            'jobSearchForm'       => $jobSearchForm->createView(),
            'secondJobSearchForm' => $secondJobSearchForm->createView(),
            'linkSearchForm'      => $linkSearchForm->createView(),
            'counties'            => $counties,
            'yoloCounty'          => $yoloCounty,
            'california'          => $california
        ]);
    }

    /**
     * @Route("/ck-editor-reference", name="ck-editor-reference")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ckEditorReference()
    {
        return $this->render('page/ck_editor_reference.html.twig');
    }

    /**
     * @Route("/sitemap.xml", name="sitemap", defaults={"_format"="xml"})
     */
    public function sitemap()
    {
        $em                  = $this->getDoctrine();
        $counties            = $em->getRepository(County::class)->getCountiesForSitemap();

        $jobAnnouncementRepo = $em->getRepository(JobAnnouncement::class);
        $jobAnnouncements    = $jobAnnouncementRepo->getJobAnnouncementsForSitemap();

        $cityRepo            = $em->getRepository(City::class);
        $cities              = $cityRepo->getCitiesForSitemap();

        $response = new Response($this->renderView('sitemap.html.twig', [
            'jobAnnouncements' => $jobAnnouncements,
            'counties'         => $counties,
            'cities'           => $cities
        ]), 200);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Route("/error", name="error")
     */
    public function error()
    {
        return $this->render('page/error.html.twig');
    }

    /**
     * @Route("/access-denied", name="access-denied")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function accessDenied()
    {
        return $this->render('page/access_denied.html.twig', [], new Response('', 403));
    }

    /**
     * @Route("/faq", name="faq")
     */
    public function FAQ()
    {
        return $this->render('page/general_faq.html.twig');
    }

    /**
     * @Route("/city/faq", name="city_faq")
     */
    public function cityFAQ()
    {
        return $this->render('page/city_faq.html.twig');
    }

    /**
     * @Route("/job-seeker/faq", name="job_seeker_faq")
     */
    public function jobSeekerFAQ()
    {
        return $this->render('page/jobseeker/job_seeker_faq.html.twig');
    }

    /**
     * @Route("/job-seeker/subscription/options", name="job_seeker_subcription_options")
     */
    public function jobSeekerSubscriptionOptions()
    {
        $jsspRepo                     = $this->getDoctrine()->getRepository(JobSeekerSubscriptionPlan::class);
        $jobSeekerSubscriptionOptions = $jsspRepo->getAllSubscriptionsForJobSeeker();

        return $this->render('page/jobseeker/subscription_options.html.twig', [
            'jobSeekerSubscriptionOptions' => $jobSeekerSubscriptionOptions
        ]);
    }

    /**
     * @Route("/privacy", name="privacy_policy")
     */
    public function privacyPolicy()
    {
        return $this->render('page/privacy.html.twig');
    }

    /**
     * @Route("/terms", name="terms_of_use")
     */
    public function termsOfService()
    {
        return $this->render('page/terms.html.twig');
    }

    /**
     * @Route("/city/subscription/options", name="city_subcription_options")
     */
    public function citySubscriptionOptions()
    {
        return $this->render('page/city/subcription_options.html.twig');
    }


    /**
     * @Route("/about/cities", name="about_cities")
     */
    public function aboutCities(EntityManagerInterface $em)
    {
        $jobCategories = $em->getRepository(CMSJobCategory::class)->findAllWithNameOrdered();
        return $this->render('city/about-tabs/city_about.html.twig', [
            'about_city' => 'active',
            'title'      => 'About Cities',
            'categories' => $jobCategories
        ]);
    }

    /**
     * @Route("/about/cities/job-categories", name="default_job_categories")
     * @Route("/about/cities/job-categories/{id}/{name}", name="job_categories")
     */
    public function jobCategories(EntityManagerInterface $em, Request $request)
    {
        $jobCategories = $em->getRepository(CMSJobCategory::class)->findAllWithNameOrdered();

        $activeCategory = null;
        if ($jobCategories && count($jobCategories)) {
            $activeCategory = $jobCategories[0];
        }

        foreach ($jobCategories as $category) {
            if ($category->getId() == $request->get('id')) {
                $activeCategory = $category;
                break;
            }
        }

        return $this->render('city/about-tabs/city_job_categories.html.twig', [
            'job_categories' => 'active',
            'title'          => 'Job Categories',
            'categories'     => $jobCategories,
            'activeCategory' => $activeCategory
        ]);
    }

    /**
     * @Route("/about/cities/just-starting-out", name="just_starting_out")
     */
    public function justStartingOut()
    {
        return $this->render('city/about-tabs/city_just_starting_out.html.twig', [
            'just_starting_out' => 'active',
            'title'             => 'Just Starting Out'
        ]);
    }

    /**
     * @Route("/about/cities/active-states", name="active_states")
     */
    public function activeStates()
    {
        return $this->render('city/about-tabs/city_active_states.html.twig', [
            'active_states' => 'active',
            'title'         => 'Active States'
        ]);
    }

    /**
     * @Route("/contact", name="contact")
     *
     * @param Request $request
     * @param EmailHelper $mailer
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse|Response
     */
    public function contactForm(Request $request, EmailHelper $mailer, TranslatorInterface $translator)
    {
        $contactForm = new ContactForm();
        $form        = $this->createForm(ContactFormType::class, $contactForm, [
            'action' => $request->getUri()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    /** @var EntityManager $em */
                    $em = $this->getDoctrine()->getEntityManager();
                    $em->persist($contactForm);
                    $em->flush();

                    $mailer->sendContactFormEmail($contactForm);

                    $successMsg = $translator->trans('success_send_email_contact');
                    $this->addFlash('success', $successMsg);
                    $url      = $this->generateUrl('contact_form_thank_you');
                    $response = new RedirectResponse($url);

                    return $response;
                } catch (\Exception $e) {
                    $errMsg = $translator->trans('error_email_unexpected', ['message' => $e->getMessage()]);
                    $this->addFlash('error', $errMsg);
                }
            } else {
                $errMsg = $translator->trans('form.invalid');
                $this->addFlash('error', $errMsg);

                foreach ($form->getErrors() as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('page/contact.html.twig', [
            'contactForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/contact/thank-you", name="contact_form_thank_you")
     *
     * @return Response
     */
    public function thankYou(){

        return $this->render('page/contact_thank_you.html.twig', []);
    }

    /**
     * @Route("/cities-served", name="cities_served")
     *
     * @return Response
     */
    public function citiesServed()
    {
        return $this->render('page/cities_served.html.twig');
    }

    /**
     * @Route("/about-us/the-team", name="the_team")
     */
    public function theTeam()
    {
        return $this->render('page/the_team.html.twig');
    }

    /**
     * @Route("/about-us/the-founder-story", name="the_founder_story")
     */
    public function theFounderStory()
    {
        return $this->render('page/the_founder_story.html.twig');
    }

    /**
     * @Route("/about/job-alerts/city-users", name="about_job_alerts_city_users")
     */
    public function aboutJobAlertsCityUsers()
    {
        return $this->render('page/about_job_alerts_city_users.html.twig');
    }

    /**
     * @Route("/about/job-alerts/job-seekers", name="about_job_alerts_job_seekers")
     */
    public function aboutJobAlertsJobSeekers()
    {
        return $this->render('page/about_job_alerts_job_seekers.html.twig');
    }

    /**
     * @Route("/news", name="news")
     * @param Request $request
     * @param PaginatorInterface $paginator
     */
    public function news(Request $request, PaginatorInterface $paginator) {
        $showPerPage = 10;
        $articleRepository = $this->getDoctrine()->getRepository(Article::class);
        $articleQuery = $articleRepository->findAllQuery();
        $paginator->setDefaultPaginatorOptions([PaginatorInterface::PAGE_PARAMETER_NAME => 'articlePage']);
        $pagination = $paginator->paginate(
            $articleQuery,
            $request->query->getInt('articlePage', 1),
            $showPerPage
        );

        return $this->render('page/news.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/news/{slug}", name="article")
     * @ParamConverter("article", options={"mapping"={"slug"="slug"}})
     * @param Request $request
     */
    public function article(Request $request, Article $article) {

        return $this->render('page/news/article.html.twig', [
            'article' => $article,
        ]);

    }

    //Testing purposes
//
//    /**
//     * @Route("/subscription/downgrade", name="downgrade")
//     * @param Request $request
//     * @param DowngradeSubscriptions $downgradeSubscriptions
//     */
//    public function downgrade(Request $request, DowngradeSubscriptions $downgradeSubscriptions) {
//        $downgradeSubscriptions->downgradeSubscriptions();
//        return $this->render('page/the_founder_story.html.twig');
//    }

    /**
     * @Route("check-email-exist", name="check_email_exist")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function resettingPasswordEmail(Request $request)
    {
        $email = $request->query->get('email');

        /** @var User $user */
        $user  = $this->getDoctrine()->getRepository(User::class)->findOneByEmail($email);

        if ($user) {
            return $this->json(['success' => true]);
        } else {
            return $this->json(['success' => false]);
        }
    }

    /**
     * @Route("/count/{id}/city-link-type", name="count_city_link_type")
     * @param Request $request
     * @param Url $url
     * @ParamConverter("url", options={"mapping"={"id"="id"}})
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function countCityLinkType(Request $request, Url $url)
    {
        $em = $this->getDoctrine()->getManager();
        $url->setClickCount($url->getClickCount() ? $url->getClickCount() + 1 : 1);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
