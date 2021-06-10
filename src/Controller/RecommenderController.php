<?php

namespace App\Controller;

use App\Service\JobTitleML;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecommenderController extends AbstractController
{
    /** @var JobTitleML $recommender */
    private $recommender;

    public function __construct(JobTitleML $recommender)
    {
        $this->recommender = $recommender;
    }

    /**
     * @Route("/recommender", name="recommender")
     * @param Request $request
     *
     * @return Response
     * @throws \Phpml\Exception\FileException
     * @throws \Phpml\Exception\SerializeException
     */
    public function index(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('jobTitle', TextType::class)
            ->add('department', TextType::class, ['required' => false])
            ->add('recommend', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        $level = null;
        $jobTitle = null;
        $categories = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $jobTitle = $data['jobTitle'];
            $department = $data['department'];

            if ($department) {
                //$categories[] = $this->recommender->recommendCategory($jobTitle, $department);
                $categories[] = $this->recommender->recommendCategory($jobTitle, $department, 'build/machine_learning/model/category1.phpml');
                $categories[] = $this->recommender->recommendCategory($jobTitle, $department, 'build/machine_learning/model/category2.phpml');
                $categories[] = $this->recommender->recommendCategory($jobTitle, $department, 'build/machine_learning/model/category3.phpml');
                $categories[] = $this->recommender->recommendCategory($jobTitle, $department, 'build/machine_learning/model/category4.phpml');
                $categories[] = $this->recommender->recommendCategory($jobTitle, $department, 'build/machine_learning/model/category5.phpml');
            }
            $level = $this->recommender->recommendLevel($jobTitle);
        }

        return $this->render('recommender.html.twig', [
            'form' => $form->createView(),
            'jobTitle' => $jobTitle,
            'level' => $level,
            'category' => implode(", ", array_filter(array_unique($categories)))
        ]);

    }

    /**
     * @Route("/generate", name="generate")
     * @param Request $request
     *
     * @return void
     * @throws \Phpml\Exception\FileException
     * @throws \Phpml\Exception\InvalidArgumentException
     * @throws \Phpml\Exception\SerializeException
     */
    public function generate(Request $request) {
        $this->recommender->generateJobTitleCategoryRecommenderModel(null, 2);
    }
}
