<?php

namespace App\Controller\City;

use App\Entity\City;
use App\Entity\User\CityUser;
use App\Form\City\Account\AccountInformationType;
use App\Form\City\Account\ChangeEmailType;
use App\Form\City\Account\ChangePasswordType;
use App\Form\City\Account\ChangeUsernameType;
use FOS\UserBundle\Form\Type\UsernameFormType;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @Route("/city/account", name="city_account")
     * @return Response
     */
    public function account(Request $request)
    {
        // If User is logged in and is type CityUser
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('error', 'You cannot access your account because you are not logged in. Please log in and try again.');
            $url = $this->generateUrl('fos_user_security_login');
            return $this->redirect($url);
        }

        $this->denyAccessUnlessGranted('ROLE_CITYUSER');
        return $this->render('city/account/index.html.twig');
    }

    /**
     * @Route("/city/account/information", name="city_account_information")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function accountInformation(Request $request) {

        // If User is logged in and is type CityUser
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('error', 'You cannot access your account because you are not logged in. Please log in and try again.');
            $url = $this->generateUrl('fos_user_security_login');
            return $this->redirect($url);
        }

        $this->denyAccessUnlessGranted('ROLE_CITYUSER');
        $user = $this->getUser();

        $accountInformationForm = $this->createForm(AccountInformationType::class, null, [
            'city' => $this->getUser()->getCity()
        ]);
        $accountInformationForm->setData($user);
        $accountInformationForm->handleRequest($request);
        $this->processAccountInformationForm($accountInformationForm, $user);

        $changeUsernameForm = $this->createForm(ChangeUsernameType::class);
        $changeUsernameForm->setData($user);
        $changeUsernameForm->handleRequest($request);
        $this->processUserForm($changeUsernameForm, $user, 'Username');


        $changeEmailForm = $this->createForm(ChangeEmailType::class);
        $changeEmailForm->setData($user);
        $changeEmailForm->handleRequest($request);
        $this->processUserForm($changeEmailForm, $user, 'Email');


        $changePasswordForm = $this->createForm(ChangePasswordType::class);
        $changePasswordForm->setData($this->getUser());
        $changePasswordForm->handleRequest($request);
        $this->processUserForm($changePasswordForm, $user, 'Password');


        return $this->render('city/account/information.html.twig', [
            'accountInformationForm' => $accountInformationForm->createView(),
            'changeEmailForm' => $changeEmailForm->createView(),
            'changeUsernameForm' => $changeUsernameForm->createView(),
            'changePasswordForm' => $changePasswordForm->createView(),
        ]);
    }

    public function processAccountInformationForm($form, CityUser $user) {
        if($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $em = $this->getDoctrine()->getManager();
                    /** @var CityUser $data */
                    $data = $form->getData();
                    $user->setDepartment($data->getDepartment());
                    $user->setJobTitle($data->getJobTitle());
                    $user->setFirstname($data->getFirstname());
                    $user->setLastname($data->getLastname());
                    $user->setPhone($data->getPhone());
                    $em->persist($user);
                    $em->flush();
                    $this->addFlash('success', 'Success: Registration Information has been updated.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', 'Error: Registration Information could not be updated. Please correct the errors and try again.');
                }
            } else {
                $this->addFlash('error', 'Error: Registration Information could not be updated. Please correct the errors and try again.');
            }
        }
    }

    public function processUserForm($form, CityUser $user, $type) {
        if($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $this->userManager->updateUser($user);
                    $this->addFlash('success', 'Success: '. $type .' has been updated.');
                } catch(\Exception $exception) {
                    $this->addFlash('error', 'Error: '. $type .' could not be updated. Please correct the errors and try again.');
                }

            } else {
                $this->addFlash('error', 'Error: '. $type .' could not be updated. Please correct the errors and try again.');
            }
        }
    }

    /**
     * @Route("/city/account/payment_history", name="city_account_payment_history")
     */
    public function paymentHistory(Request $request) {
        // If User is logged in and is type CityUser
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $url = $this->generateUrl('fos_user_security_login');
            return $this->redirect($url);
        }

        $this->denyAccessUnlessGranted('ROLE_CITYUSER');
        $user = $this->getUser();

        return $this->render('city/account/payment_history.html.twig');
    }

    /**
     * @Route("/city/account/agreements", name="city_account_agreements")
     */
    public function agreements(Request $request) {
        // If User is logged in and is type CityUser
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('error', 'You cannot access your account because you are not logged in. Please log in and try again.');
            $url = $this->generateUrl('fos_user_security_login');
            return $this->redirect($url);
        }

        $this->denyAccessUnlessGranted('ROLE_CITYUSER');
        $user = $this->getUser();

        return $this->render('city/account/agreements.html.twig');
    }
}
