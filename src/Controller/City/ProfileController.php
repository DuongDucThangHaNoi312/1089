<?php

namespace App\Controller\City;

use App\Entity\City;
use App\Entity\CMSBlock;
use App\Entity\JobAnnouncement;
use App\Entity\User;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser;
use App\Form\City\Profile\AboutType;
use App\Form\City\Profile\AgencyInfoType;
use App\Form\City\Profile\BannerImageType;
use App\Form\City\Profile\CityLinksType;
use App\Form\City\Profile\ContactInfoType;
use App\Form\City\Profile\NameType;
use App\Form\City\Profile\SealImageType;
use App\Security\CityVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @param Request $request
     * @param City $city
     * @return RedirectResponse|Response
     * @Route("/city/{slug}/profile/view", name="view_city_profile")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function cityProfile(Request $request, City $city)
    {
        $this->denyAccessUnlessGranted('view', $city);

        return $this->render('city/city_profile.html.twig', [
            'city' => $city,
            'isEditable' => false,
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     * @Route("/city/{slug}/profile/edit", name="edit_city_profile")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function editCityProfile(Request $request, City $city)
    {
        $this->denyAccessUnlessGranted('edit', $city);

        return $this->render('city/city_profile.html.twig', [
            'city' => $city,
            'isEditable' => true,
        ]);
    }


    /**
     * @Route("/city/{slug}/profile/about", name="city_profile_about")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @param City $city
     * @return Response
     */
    public function about(City $city, Bool $isEditable) {
        $templateArgs = [
            'city' => $city
        ];
        // only create the form if the user is authorized to edit the city
        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createCityProfileAjaxForm($city, AboutType::class, 'create_city_profile_about');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
            ]);
        }

        $defaultAboutTemplate = null;
        if (false == $city->getProfileAbout()) {
            $defaultAboutCMSBlock = $this->getDoctrine()->getRepository(CMSBlock::class)->findOneBy(['slug' => 'default-city-about']);
            if (!$defaultAboutCMSBlock) {
                $defaultAboutCMSBlock = new CMSBlock();
                $defaultAboutCMSBlock->setName('Default City About');
                $defaultAboutCMSBlock->setContent('Admin will define the template soon.');
                $this->getDoctrine()->getManager()->persist($defaultAboutCMSBlock);
                $this->getDoctrine()->getManager()->flush();
            }
            $defaultAboutTemplate = $defaultAboutCMSBlock->getContent();
        }
        $templateArgs['default_about_template'] = $defaultAboutTemplate;

        return $this->render('city/profile/_about.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/profile/about/create", name="create_city_profile_about")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @param Request $request
     * @param City $city
     * @return JsonResponse|RedirectResponse
     */
    public function createAbout(Request $request, City $city) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createCityProfileAjaxForm($city, AboutType::class, 'create_city_profile_about');
        return $this->processCityProfileAjaxForm($request, $city, $form, 'city/profile/_about_form.html.twig', 'city/profile/_about.html.twig');
    }

    /**
     * @Route("/city/{slug}/profile/name", name="city_profile_name")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function name(Request $request, City $city, Bool $isEditable) {
        $templateArgs = [
            'city' => $city
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createCityProfileAjaxForm($city, NameType::class, 'create_city_profile_name');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
            ]);
        }

        return $this->render('city/profile/_name.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/profile/name/create", name="create_city_profile_name")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function createName(Request $request, City $city) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createCityProfileAjaxForm($city, NameType::class, 'create_city_profile_name');
        return $this->processCityProfileAjaxForm($request, $city, $form, 'city/profile/_name_form.html.twig', 'city/profile/_name.html.twig');
    }

    /**
     * @Route("/city/{slug}/profile/contact_info", name="city_profile_contact_info")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function contactInfo(Request $request, City $city, Bool $isEditable) {
        $templateArgs = [
            'city' => $city
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createCityProfileAjaxForm($city, ContactInfoType::class, 'create_city_profile_contact_info');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
            ]);
        }

        return $this->render('city/profile/_contact_info.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/profile/contact_info/create", name="create_city_profile_contact_info")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function createContactInfo(Request $request, City $city) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createCityProfileAjaxForm($city, ContactInfoType::class, 'create_city_profile_contact_info');
        return $this->processCityProfileAjaxForm($request, $city, $form, 'city/profile/_contact_info_form.html.twig', 'city/profile/_contact_info.html.twig');
    }

    /**
     * @Route("/city/{slug}/profile/agency_info", name="city_profile_agency_info")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function agencyInfo(Request $request, City $city, Bool $isEditable) {
        $templateArgs = [
            'city' => $city
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createCityProfileAjaxForm($city, AgencyInfoType::class, 'create_city_profile_agency_info');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
            ]);
        }

        return $this->render('city/profile/_agency_info.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/profile/agency_info/create", name="create_city_profile_agency_info")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function createAgencyInfo(Request $request, City $city) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createCityProfileAjaxForm($city, AgencyInfoType::class, 'create_city_profile_agency_info');
        return $this->processCityProfileAjaxForm($request, $city, $form, 'city/profile/_agency_info_form.html.twig', 'city/profile/_agency_info.html.twig');
    }

    /**
     * @Route("/city/{slug}/profile/opportunities", name="city_profile_opportunities")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function opportunities(Request $request, City $city) {
        $acceptingInterest = $this->getDoctrine()->getRepository(City\JobTitle::class)->getTotalJobTitlesAcceptingInterest($city);
        $job_announcement_count = $this->getDoctrine()->getRepository(JobAnnouncement::class)->getTotalActiveJobAnnouncements($city);

        $jobsOfInterestLink = null;
        $jobAnnouncementsLink = null;
        if ($this->getUser() instanceof CityUser) {
            if ($this->getUser()->getCity() == $city) {
                $jobsOfInterestLink = $this->generateUrl('city_job_titles', [
                    'slug' => $city->getSlug(),
                ]);
                $jobAnnouncementsLink = $this->generateUrl('city_job_announcements', [
                    'slug'=> $city->getSlug(),
                    'status' => 'active',
                ]);
            }
        }

        if (!$jobsOfInterestLink) {
            $jobsOfInterestLink = $this->generateUrl('job_seeker_jobtitle_search', ['city' => $city->getId(), 'type' => 'jobTitle', '_fragment' =>  'job-title']);
        }

        if (!$jobAnnouncementsLink) {
            $jobAnnouncementsLink = $this->generateUrl('job_seeker_jobtitle_search', ['city' => $city->getId(), 'type' => 'announcement', '_fragment' =>  'job-announcement']);

        }

        return $this->render('city/profile/_opportunities.html.twig', [
            'city' => $city,
            'acceptingInterest' => $acceptingInterest,
            'job_announcements' => $job_announcement_count,
            'status' => JobAnnouncement::STATUS_ACTIVE,
            'jobs_interest_link' => $jobsOfInterestLink,
            'job_announcements_link' => $jobAnnouncementsLink,
        ]);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/profile/urls/create", name="create_city_profile_urls")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function createUrls(Request $request, City $city) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createCityProfileAjaxForm($city, CityLinksType::class, 'create_city_profile_urls');
        return $this->processCityProfileAjaxForm($request, $city, $form, 'city/profile/_city_links_form.html.twig', 'city/profile/_city_links.html.twig');
    }

    /**
     * @Route("/city/{slug}/profile/urls", name="city_profile_urls")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function urls(Request $request, City $city, Bool $isEditable) {
        $templateArgs = [
            'city' => $city
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createCityProfileAjaxForm($city, CityLinksType::class, 'create_city_profile_urls');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
            ]);
        }

        return $this->render('city/profile/_city_links.html.twig', $templateArgs);
    }

    /**
     * @Route("/city/{slug}/profile/departments", name="city_profile_departments")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @param Request $request
     * @param City $city
     *
     * @return Response
     */
    public function departments(Request $request, City $city) {
        $departmentRepo = $this->getDoctrine()->getRepository(City\Department::class);
        $departments    = $departmentRepo->findBy([
            'city'              => $city,
            'hideOnProfilePage' => false
        ], [
            'orderByNumber' => 'ASC',
            'name'          => 'ASC'
        ]);

        return $this->render('city/profile/_departments.html.twig', [
            'city'        => $city,
            'departments' => $departments
        ]);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/profile/seal_image/create", name="create_city_profile_seal_image")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function createSealImage(Request $request, City $city) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createCityProfileAjaxForm($city, SealImageType::class, 'create_city_profile_seal_image');
        return $this->processCityProfileAjaxForm($request, $city, $form, 'city/profile/_seal_image_form.html.twig', 'city/profile/_seal_image.html.twig');
    }

    /**
     * @Route("/city/{slug}/profile/seal_image", name="city_profile_seal_image")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function sealImage(Request $request, City $city, Bool $isEditable) {
        $templateArgs = [
            'city' => $city
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createCityProfileAjaxForm($city, SealImageType::class, 'create_city_profile_seal_image');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
            ]);
        }

        return $this->render('city/profile/_seal_image.html.twig', $templateArgs);
    }

    /**
     * @Route("/city/{slug}/profile/banner_image", name="city_profile_banner_image")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function bannerImage(Request $request, City $city, Bool $isEditable) {
        $templateArgs = [
            'city' => $city
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createCityProfileAjaxForm($city, BannerImageType::class, 'create_city_profile_banner_image');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
            ]);
        }

        return $this->render('city/profile/_banner_image.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/profile/banner_image/create", name="create_city_profile_banner_image")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     */
    public function createBannerImage(Request $request, City $city) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createCityProfileAjaxForm($city, BannerImageType::class, 'create_city_profile_banner_image');
        return $this->processCityProfileAjaxForm($request, $city, $form, 'city/profile/_banner_image_form.html.twig', 'city/profile/_banner_image.html.twig');
    }

    /* Generic Methods to process and create Ajax Forms */
    public function processCityProfileAjaxForm(Request $request, City $city, $form, string $view, string $successView) {
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $city->setInverseSide();
            $em->persist($city);
            $em->flush();

            if (!$request->isXmlHttpRequest()) {
                if ($view == "city/profile/_seal_image_form.html.twig" || $view == "city/profile/_banner_image_form.html.twig") {
                    $this->addFlash('success', 'Success! '. ucwords(str_replace("_", " ", $form->getName()))   . " has been updated.");

                }
                return $this->redirectToRoute('edit_city_profile', ['slug' => $city->getSlug()]);
            }

            return new JsonResponse(
                array(
                    'message' => 'Success! '. ucwords(str_replace("_", " ", $form->getName()))   . " has been updated.",
                    'display' => $this->renderView($successView, [
                        'form_name' => $form->getName(),
                        'city' => $city,
                        'form' => $form->createView(),
                    ])), 200);
        } else {
            /** @var Form $form */
            if ($view == "city/profile/_seal_image_form.html.twig" || $view == "city/profile/_banner_image_form.html.twig") {
                $errors = $form->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }

            }
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('edit_city_profile', ['slug' => $city->getSlug()]);
        }

        $response = new JsonResponse(
            array(
                'message' =>"<b>Error!</b> Updating " . ucwords(str_replace("_", " ", $form->getName())). ".Please correct the errors and try again.",
                'form' => $this->renderView($view,
                    array(
                        'city' => $city,
                        'form' => $form->createView(),
                    ))), 400);

        return $response;
    }

    public function createCityProfileAjaxForm(City $city, string $type, string $route) {
        $form = $this->createForm($type, $city, [
            'action'  => $this->generateUrl($route, ['slug' => $city->getSlug()]),
            'method' => 'POST',
        ]);
        return $form;
    }
}
