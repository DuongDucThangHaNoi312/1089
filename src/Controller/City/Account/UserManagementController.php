<?php

namespace App\Controller\City\Account;

use App\Entity\City;
use App\Entity\CityCityUser;
use App\Entity\User\CityUser;
use App\Form\City\Account\Users\CreateUserType;
use App\Security\Voter\CityAdminVoter;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserManagementController extends AbstractController
{
    private $eventDispatcher;
    private $userManager;
    private $translator;

    public function __construct(EventDispatcherInterface $eventDispatcher, UserManagerInterface $userManager, TranslatorInterface $translator)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->userManager = $userManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/city/{city_slug}/account/users", name="city_manage_users")
     * @ParamConverter("city", options={"mapping"={"city_slug"="slug"}})
     * @param Request $request
     * @param City $city
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function users(Request $request, City $city, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted(CityAdminVoter::MANAGEUSERS, $city);

        $request->getSession()->remove('fos_user_send_confirmation_email/email');

        // CreateUserForm
        $createUserForm = $this->createForm(CreateUserType::class, null, [
            'action' => $this->generateUrl('city_add_user', ['city_slug' => $city->getSlug()])
        ]);

        // FilterForm
        $filterForm = $this->createFormBuilder()
            ->add('showPerPage', ChoiceType::class, [
                'choices' => [10 => 10, 25 => 25, 50 => 50, 100 => 100],
                'attr' => ['onchange' => 'this.form.submit();']
            ])
            ->setAction($request->getUri())
            ->setMethod('GET')
            ->getForm();

        $filterForm->handleRequest($request);

        $showPerPage = 10;

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();
            $showPerPage = $data['showPerPage'];
        }

        //UserQuery
        $userQuery =  $this->getDoctrine()->getRepository(CityCityUser::class)->getQueryForCityCityUsers($city, $this->getUser());

        $pagination = $paginator->paginate(
            $userQuery,
            $request->query->getInt('page', 1),
            $showPerPage
            );

        return $this->render('city/account/users.html.twig', [
            'city' => $city,
            'pagination' => $pagination,
            'filterForm' => $filterForm->createView(),
            'createUserForm' => $createUserForm->createView(),
        ]);
    }

    /**
     * @Route("/city/{city_slug}/account/users/add", name="city_add_user")
     * @ParamConverter("city", options={"mapping"={"city_slug"="slug"}})
     * @param Request $request
     * @param City $city
     * @param RouterInterface $router
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @return
     */
    public function createUser(Request $request, City $city, RouterInterface $router, SessionInterface $session) {
        $this->denyAccessUnlessGranted(CityAdminVoter::MANAGEUSERS, $city);

        $form = $this->createForm(CreateUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CityUser $data */
            $data = $form->getData();
            $data->setEnabled(true);
            $data->setUsername($data->getEmail());
            $data->setPlainPassword($data->getEmail());
            $data->setCity($city);

            $cityCityUser = new CityCityUser();
            $cityCityUser->setCity($city);
            $cityCityUser->setCityUser($data);

            $city->addCityCityUser($cityCityUser);
            $data->addRole('ROLE_PENDING_CITYUSER');

            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            $em->persist($cityCityUser);
            $em->persist($city);
            $em->flush();

            $this->addFlash('success', 'Invite has been sent to ' . $data->getEmail());
        } else {
            $this->addFlash('error', 'Error sending invitation to user. Please try again.');
        }

        return $this->redirectToRoute('city_manage_users', ['city_slug' => $city->getSlug()]);
    }

    /**
     * @Route("/city/user/{id}/delete", name="delete_city_user")
     * @param Request $request
     * @param CityUser $cityUser
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteUser(Request $request, CityUser $cityUser) {
        $this->denyAccessUnlessGranted(CityAdminVoter::MANAGEUSERS, $cityUser);

        $username = $cityUser->getUsername();
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($cityUser);
            $em->flush();
            $this->addFlash('success', $username. ' has been deleted successfully.');
        } catch(\Exception $exception) {
            $this->addFlash('error', 'Error deleting '. $username . ' please try again.');
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/city/user/{id}/enable", name="enable_city_user")
     * @param Request $request
     * @param CityUser $cityUser
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function enableUser(Request $request, CityUser $cityUser) {
        // Todo: Make sure you have the ability to delete a User
        $username = $cityUser->getUsername();
        try {
            $em = $this->getDoctrine()->getManager();
            $cityUser->setEnabled(true);
            $em->persist($cityUser);
            $em->flush();
            $this->addFlash('success', $username. ' has been enabled.');
        } catch(\Exception $exception) {
            $this->addFlash('error', 'Error enabling '. $username . ' please try again.');
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/city/user/{id}/disable", name="disable_city_user")
     * @param Request $request
     * @param CityUser $cityUser
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function disableUser(Request $request, CityUser $cityUser) {
        // Todo: Make sure you have the ability to disable a User
        $username = $cityUser->getUsername();
        try {
            $em = $this->getDoctrine()->getManager();
            $cityUser->setEnabled(false);
            $em->persist($cityUser);
            $em->flush();
            $this->addFlash('success', $username. ' has been disabling.');
        } catch(\Exception $exception) {
            $this->addFlash('error', 'Error disabling '. $username . ' please try again.');
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/city/{city_slug}/user/{id}/send/invite", name="send_city_user_invite")
     * @ParamConverter("city", options={"mapping"={"city_slug"="slug"}})
     * @ParamConverter("cityUser", options={"mapping"={"id"="id"}})
     * @param Request $request
     * @param CityUser $cityUser
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sendInvitation(Request $request, CityUser $cityUser, City $city) {
        $username = $cityUser->getUsername();
        // Only allow to invite Users up to the CountofAllowedUsers limit or if its null unlimited.
        if ($city->getSubscription()->getSubscriptionPlan()->getCountOfAllowedUsers() == null || count($city->getCityCityUsers()) < $city->getSubscription()->getSubscriptionPlan()->getCountOfAllowedUsers()) {
            try {
                $form = $this->createForm(CreateUserType::class);
                $form->setData($cityUser);
                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
                $this->addFlash('success', 'Invitation to ' . $username. ' has been sent.');
            } catch(\Exception $exception) {
                $this->addFlash('error', 'Error inviting '. $username . ' please try again.');
            }
        } else {
            $format = 'Your subscription does not allow you to invite more users. <a href="%s">You can change your subscription plan here.</a>';
            $url = $this->generateUrl('city_subscription', ['slug' =>  $city->getSlug(), 'update' => 'subscription']);
            $this->addFlash('error', sprintf($format, $url));
        }


        return $this->redirect($request->headers->get('referer'));
    }

}