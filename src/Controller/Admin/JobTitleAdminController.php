<?php

namespace App\Controller\Admin;

use App\Annotation\IgnoreSoftDelete;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class JobTitleAdminController extends CRUDController
{
    /**
     * @param $id
     * @IgnoreSoftDelete()
     * @return RedirectResponse
     */
    public function undeleteAction($id)
    {
        $jobTitle = $this->admin->getSubject();
        if (!$jobTitle) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }
        try {
            $jobTitle->setDeletedAt(null);
            $em = $this->getDoctrine()->getManager();
            $em->persist($jobTitle);
            $em->flush();
            $this->addFlash('sonata_flash_success', 'Successfully undeleted ' . $jobTitle->getName() . ' job title');
        }catch (\Exception $exception) {
            $message = sprintf("Error: %s job title could not be undeleted", $jobTitle->getName());
            $this->addFlash('sonata_flass_error', $message);
        }
        return new RedirectResponse(
            $this->admin->generateUrl('list', ['filter' => $this->admin->getFilterParameters()])
        );
    }
}