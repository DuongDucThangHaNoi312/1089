<?php

namespace App\Service;

use App\Entity\City\JobTitle;
use App\Entity\JobTitle\Lookup\JobCategory;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Repository\City\JobTitleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Phpml\Classification\SVC;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Dataset\ArrayDataset;
use Phpml\Dataset\CsvDataset;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Metric\Accuracy;
use Phpml\ModelManager;
use Phpml\Pipeline;
use Phpml\SupportVectorMachine\Kernel;
use Phpml\Tokenization\WordTokenizer;

class JobTitleML
{

    /**
     *
     */
    CONST QUERY_LIMIT = 100;

    /**
     * @var EntityManagerInterface
     */
    private $em;


    /**
     * JobTitleML constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param null $output
     * @param int $features
     * @param string $modelName
     *
     * @throws \Phpml\Exception\InvalidArgumentException
     */
    public function generateJobTitleCategoryRecommenderModel($output = null, $features = 1, $modelName = "job_title_department_category_recommender")
    {

        if ( ! $output) {
            $output = 'build/machine_learning/model';
        }

        $dataset = $this->getDataset('category');
        $samples = $dataset->getSamples();
        $targets = $dataset->getTargets();

        /** SPLIT CATEGORIES */
        $sampleList = [
            'category1' => [],
            'category2' => [],
            'category3' => [],
            'category4' => [],
            'category5' => []
        ];

        $targetList = [
            'category1' => [],
            'category2' => [],
            'category3' => [],
            'category4' => [],
            'category5' => []
        ];


        /* Split samples to NOT contain duplicate */
        foreach ($samples as $index => $jobTitle) {
            $category = $targets[$index];

            foreach ($sampleList as $key => $jobTitles) {
                if ( ! in_array($jobTitle, $sampleList[$key])) {
                    $sampleList[$key][] = $jobTitle;
                    $targetList[$key][] = $category;
                    break;
                }
            }
        }

        /* Each sample should contain all jobTitle, and target contains empty category if not yet existed */
        foreach ($sampleList as $key => $jobTitles) {
            foreach ($samples as $index => $jobTitle) {
                if ( ! in_array($jobTitle, $sampleList[$key])) {

                    $sampleList[$key][] = $jobTitle;
                    $targetList[$key][] = '';
                }
            }
        }

        foreach ($sampleList as $key => $sample) {
            $target = $targetList[$key];
            if (count($sample) && count($target)) {
                $dataset = new ArrayDataset($sample, $target);

                $this->generateSVCModelNoTestData($dataset, $output, $key, $features);
            }
        }
    }

    /**
     * @param null $output
     * @param string $modelName
     *
     * @throws \Phpml\Exception\InvalidArgumentException
     */
    public function generateJobTitleLevelRecommenderModel($output = null, $modelName = "job_title_level_recommender")
    {
        if ( ! $output) {
            $output = 'build/machine_learning/model';
        }

        $dataset = $this->getDataset('level');
        $this->generateSVCModelNoTestData($dataset, $output, $modelName);
    }

    /**
     * @param $type
     *
     * @return ArrayDataset
     * @throws \Phpml\Exception\InvalidArgumentException
     */
    private function getDataset($type)
    {
        $samples      = [];
        $targets      = [];
        $index        = 0;
        $count        = self::QUERY_LIMIT;
        $jobTitleRepo =  $this->em->getRepository(JobTitle::class);

        while ($count == self::QUERY_LIMIT) {
            $nameAndLevels = $jobTitleRepo->findJobTitleMLData($type, $index * self::QUERY_LIMIT, self::QUERY_LIMIT);

            $samples = array_merge($samples, array_column($nameAndLevels, 'sample'));
            $targets = array_merge($targets, array_column($nameAndLevels, 'target'));

            $count = count($nameAndLevels);
            $index++;
        }

        return new ArrayDataset($samples, $targets);
    }

    public function generateSVCModelNoTestData(ArrayDataset $dataset, string $output, string $modelName, $features = 1)
    {
        // Create a 0.1 random sample
        $randomSplit = new StratifiedRandomSplit($dataset, 0.5);
        // Create Pipeline for Data Transformation, Train and Predict
        $pipeline = new Pipeline([
            new TokenCountVectorizer($tokenizer = new WordTokenizer()),
            new TfIdfTransformer()
        ], new SVC(Kernel::RBF, 10000));

        $start = microtime(true);
        $pipeline->train($randomSplit->getTrainSamples(), $randomSplit->getTrainLabels());
        $stop = microtime(true);

        // Predict the Test Samples
        $predicted = $pipeline->predict($randomSplit->getTestSamples());

        echo 'Model: ' . $modelName . PHP_EOL;
        echo 'Train: ' . round($stop - $start, 4) . 's' . PHP_EOL;
        echo 'Estimator: ' . get_class($pipeline->getEstimator()) . PHP_EOL;
        echo 'Tokenizer: ' . get_class($tokenizer) . PHP_EOL;
        echo 'Trained Samples: ' . count($randomSplit->getTrainSamples()) . PHP_EOL;
        echo 'Test Samples: ' . count($randomSplit->getTestSamples()) . PHP_EOL;
        echo 'Accuracy: ' . Accuracy::score($randomSplit->getTestLabels(), $predicted) . PHP_EOL;
        echo '-------------' . PHP_EOL;

        // Export Model
        $modelManager = new ModelManager();
        $modelManager->saveToFile($pipeline, $output . '/' . $modelName . '.phpml');
    }

    /**
     * @param ArrayDataset $dataset
     * @param string $output
     * @param string $modelName
     * @param int $features
     *
     * @throws \Phpml\Exception\FileException
     * @throws \Phpml\Exception\InvalidArgumentException
     * @throws \Phpml\Exception\SerializeException
     */
    public function generateSVCModel(ArrayDataset $dataset, string $output, string $modelName, $features = 1)
    {
        $randomSplit = new StratifiedRandomSplit($dataset, 0.1);

        // Create Pipeline for Data Transformation, Train and Predict
        $pipeline = new Pipeline([
            new TokenCountVectorizer($tokenizer = new WordTokenizer()),
            new TfIdfTransformer()
        ], new SVC(Kernel::RBF, 10000));

        $start = microtime(true);
        $pipeline->train($randomSplit->getTrainSamples(), $randomSplit->getTrainLabels());
        $stop = microtime(true);

        // Predict the Test Samples
        $predicted = $pipeline->predict($randomSplit->getTestSamples());

        echo 'Model: ' . $modelName . PHP_EOL;
        echo 'Train: ' . round($stop - $start, 4) . 's' . PHP_EOL;
        echo 'Estimator: ' . get_class($pipeline->getEstimator()) . PHP_EOL;
        echo 'Tokenizer: ' . get_class($tokenizer) . PHP_EOL;
        echo 'Trained Samples: ' . count($randomSplit->getTrainSamples());
        echo 'Test Samples: ' . count($randomSplit->getTestSamples());
        echo 'Accuracy: ' . Accuracy::score($randomSplit->getTestLabels(), $predicted) . PHP_EOL;
        echo '-------------' . PHP_EOL;

        // Export Model
        $modelManager = new ModelManager();
        $modelManager->saveToFile($pipeline, $output . '/' . $modelName . '.phpml');
    }

    /**
     * @param string $jobTitle
     * @param string $modelFilePath
     *
     * @return mixed
     * @throws \Phpml\Exception\FileException
     * @throws \Phpml\Exception\SerializeException
     */
    public function recommendLevel(string $jobTitle, string $modelFilePath = 'build/machine_learning/model/job_title_level_recommender.phpml')
    {
        $modelManager = new ModelManager();
        $model        = $modelManager->restoreFromFile($modelFilePath);

        $result       = $model->predict([$jobTitle]);
        $level        = $result && count($result) ? $result[0] : '';

        return $level;
    }

    /**
     * @param string $jobTitle
     * @param string $department
     * @param string $modelFilePath
     *
     * @return array
     * @throws \Phpml\Exception\FileException
     * @throws \Phpml\Exception\SerializeException
     */
    public function recommendCategory(string $jobTitle, string $department, string $modelFilePath = 'build/machine_learning/model/job_title_department_category_recommender.phpml')
    {
        $modelManager = new ModelManager();
        $model        = $modelManager->restoreFromFile($modelFilePath);

        $result       = $model->predict([$department . " " . $jobTitle]);
        $category     = $result && count($result) ? $result[0] : '';

        return $category;
    }


    /**
     * @param JobTitle $jobTitle
     *
     * @throws \Phpml\Exception\FileException
     * @throws \Phpml\Exception\SerializeException
     */
    public function initializeJobTitle(JobTitle $jobTitle)
    {
        $jobTitleName = $jobTitle->getName();
        $department   = $jobTitle->getDepartment() ? $jobTitle->getDepartment() : '';

        $levelName = $this->recommendLevel($jobTitleName);
        if ($levelName) {
            $level = $this->em->getRepository(JobLevel::class)->findOneByName($levelName);
            if ($level) {
                $jobTitle->setLevel($level);
            }
        }

        $categoryRepo = $this->em->getRepository(JobCategory::class);
        foreach ([1, 2, 3, 4, 5] as $i) {
            $categoryName = $this->recommendCategory($jobTitleName, $department, "build/machine_learning/model/category$i.phpml");
            if ($categoryName) {
                $category = $categoryRepo->findOneByName($categoryName);
                if ($category) {
                    $jobTitle->addCategory($category);
                }
            }
        }
    }
}