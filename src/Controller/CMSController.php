<?php

namespace App\Controller;

use App\Entity\CMSBlock;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig_Environment;

class CMSController extends AbstractController
{
    /**
     * @Route("/cms/get-block/{slug}", name="cms_get_block")
     */
    public function getBlock($slug)
    {
        $cmsBlock = $this->getDoctrine()->getRepository(CMSBlock::class)->findOneBy(['slug' => $slug]);
        if (!$cmsBlock) {
            $content = '<div class="alert alert-danger">CMS Block with name "'. ucwords(str_replace("-", ' ', $slug)) . '" and slug "'.$slug.'" expected but not found. Please add it via the admin.</div>';
        } else {
            $content = $cmsBlock->getContent();
        }
        return new Response($content);
    }

    /**
     * @Route("/cms/test-email/{slug}/{email}", name="cms_get_block")
     * @param $slug
     * @param $email
     * @param \Swift_Mailer $mailer
     *
     * @param Twig_Environment $twig
     *
     * @return Response
     */
    public function testEmail($slug, $email, \Swift_Mailer $mailer, Twig_Environment $twig)
    {
        if(! $this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirect($this->generateUrl('access-denied'));
        }
        
        $context = [
            'subject'       => 'Test Email',
            'cmsBlockHtml'  => $slug,
            'finalWarning'  => false,
            'expiredString' => false
        ];

        $sent = $this->sendEmail($context, $email, $twig, $mailer);

        if ($sent) {
            $this->addFlash('success', 'Email sent!');
        }
        else {
            $this->addFlash('error', 'Error happened');
        }


        return $this->redirectToRoute('sonata_admin_dashboard');
    }

    private function sendEmail($context, $toEmail, $twig, \Swift_Mailer $mailer)
    {
        $cmsBlockRepo = $this->getDoctrine()->getRepository(CMSBlock::class);

        $htmlBlock            = $cmsBlockRepo->findOneBySlug($context['cmsBlockHtml']);
        if ($htmlBlock) {
            $context['htmlBlock'] = $htmlBlock ? $htmlBlock->getContent() : null;
            $textBlock            = isset($context['cmsBlockText']) ? $cmsBlockRepo->findOneBySlug($context['cmsBlockText']) : null;
            $context['textBlock'] = $textBlock ? $textBlock->getContent() : null;

            $template = $twig->load('emails/subscription/job_seeker_trial_status.html.twig');
            $subject  = $template->renderBlock('subject', $context);
            $textBody = $template->renderBlock('body_text', $context);

            $htmlBody = '';

            if ($template->hasBlock('body_html', $context)) {
                $htmlBody = $template->renderBlock('body_html', $context);
            }

            $message = (new Swift_Message())
                ->setSubject($subject)
                ->setFrom('no-reply@citygovjobs.com')
                ->setTo($toEmail);

            if ( ! empty($htmlBody)) {
                $message->setBody($htmlBody, 'text/html')
                        ->addPart($textBody, 'text/plain');
            } else {
                $message->setBody($textBody);
            }

            $mailer->send($message);
            return true;
        }

        return false;
    }
}
