<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Repository\User\SavedSearchRepository;
use App\Repository\User\SavedCityRepository;
use App\Repository\User\JobSeekerUser\SavedJobAnnouncementRepository;

class UserController extends AbstractController
{
    private $em;
    private $security;
    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    /**
     * @Route("/user", name="user")
     */
    public function index()
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/job-seeker/account/information/remove", name="remove_user")
     */
    public function remove( 
        SavedSearchRepository $saveSearchRepo,
        SavedCityRepository $saveCityRepo,
        SavedJobAnnouncementRepository $saveJobAnnounRepo
    ) 
    {
        $em = $this->em;
        $user = $this->security->getUser(); 

        // set null 
        $user->setStripeCustomer(null);
        $user_id = $user->getId();

        // get
        $saveSearchs = $saveSearchRepo->findByUserId($user_id);
        $saveCities = $saveCityRepo->findByUserId($user_id); 
        $saveJobAnnouns = $saveJobAnnounRepo->findByUserId($user_id);

        $arrayRm = [$saveSearchs, $saveCities, $saveJobAnnouns];

        // remove 
        for($i = 0; $i < count($arrayRm); $i++) {
            if($arrayRm[$i]) {
                foreach ($arrayRm[$i] as $item) {
                    $em->remove($item);
                }
            }
        }
        // enable user
        $user->setEnabled(false);
        $em->persist($user);
        $em->flush();
  
        return $this->redirectToRoute('fos_user_security_login');

        // remove savedSearch
        // $saveSearchs = $saveSearchRepo->findByUserId($user_id);
        // if( $saveSearchs) {
        //     foreach ($saveSearchs as $saveSearch) {
        //         $user->removeSavedSearch($saveSearch);
        //     }
        // }

        //remove savedCity
        // $saveCities = $saveCityRepo->findByUserId($user_id); 
        // if( $saveCities) {
        //     foreach ($saveCities as $saveCity) {
        //         // $em->remove($saveCity);
        //         $user->removeSavedCity($saveCity);
        //     }
        // }
        // remove saveJobAnnouns
        // $saveJobAnnouns = $saveJobAnnounRepo->findByUserId($user_id);

        // if( $saveJobAnnouns) {
        //     foreach ($saveJobAnnouns as $saveJobAnnoun) {
        //         $em->remove($saveJobAnnoun);
        //     }
        // }
        // enable user   
        // $user->setEnabled(false);
        
    }
}
