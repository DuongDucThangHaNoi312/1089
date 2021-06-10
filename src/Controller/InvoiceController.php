<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\User;
use App\Entity\User\CityUser;
use App\Service\SubscriptionProcessorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class InvoiceController extends AbstractController
{
    /**
     * @Route("/invoice/{invoiceNumber}/city/{slug}/print", name="subscription_invoice_print")
     * @param City $city
     * @param $invoiceNumber
     * @param Request $request
     * @param SubscriptionProcessorInterface $subscriptionProcessor
     *
     * @return Response
     */
    public function printInvoice($invoiceNumber, City $city, Request $request, SubscriptionProcessorInterface $subscriptionProcessor)
    {
        if ( ! $this->isGranted('ROLE_JOBSEEKER') && ! $this->isGranted('ROLE_CITYUSER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_CITYUSER')) {
            $this->denyAccessUnlessGranted('view', $city);
        }

        $subscriptionProcessor->setFlashBag($request->getSession()->getFlashBag());
        $invoices = $subscriptionProcessor->retrieveInvoices($user->getCustomerId());

        $invoice = null;
        foreach ($invoices as $i) {
            if ($i->getNumber() == $invoiceNumber) {
                $invoice = $i;
                break;
            }
        }

        return $this->render('invoice/printable_invoice.html.twig', [
            'invoice' => $invoice,
            'city'    => $city,
        ]);
    }
}
