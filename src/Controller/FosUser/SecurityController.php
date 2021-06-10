<?php
/**
 * Created by PhpStorm.
 * User: tuong
 * Date: 5/8/20
 * Time: 17:03
 */

namespace App\Controller\FosUser;

use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class   SecurityController extends BaseController
{

    private $request;
    private $router;

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $router, CsrfTokenManagerInterface $tokenManager = null)
    {
        parent::__construct($tokenManager);

        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
    }

    /**
     * Renders the login template with the given parameters. Overwrite this function in
     * an extended controller to provide additional data for the login template.
     *
     * @param array $data
     *
     * @return Response
     */
    protected function renderLogin(array $data)
    {
        if ($this->getUser()) {
            $routeParams = $this->request->query->all();
            if (array_key_exists('dest_url', $routeParams) && $routeParams['dest_url']) {
                $url = $routeParams['dest_url'];
            } else {
                $url = $this->router->generate('home');
            }

            return $this->redirect($url);
        }

        return $this->render('@FOSUser/Security/login.html.twig', $data);
    }

}