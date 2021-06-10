<?php

namespace App\Command;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportCityStateCountyCommand extends Command
{
    protected static $defaultName = 'app:import:city-state-county';

    private $em;

    public function __construct(EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Fast and efficient import for large CSV file.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $csvFilePath = 'src/DataFixtures/CSV/City/Importer/UsCitiesStatesCountiesFixture.csv';

        $startTime = time();

        if (($handle = fopen($csvFilePath, "r")) !== false) {

            $io->note('Starting import of State, Counties, and Cities');

            $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

            $counter = 0;
            $header = [];
            $dataArray = [];
            
            /** @var City\State $state */
            $state = null;
            /** @var City\County $county */
            $county = null;
            /** @var City $city */
            $city = null;

            while (($row = fgetcsv($handle)) !== FALSE) {

                if ($counter == 0) {
                    $header = array_flip($row);
                    $counter++;
                    continue;
                }

                if (false == (isset($row[$header['city']]) && isset($row[$header['stateFull']]) && isset($row[$header['county']]))) {
                    continue;
                }

                if ($row[$header['city']] == '' || $row[$header['stateFull']] == '' || $row[$header['county']] == '') {
                    continue;
                }

                $dataArray[$row[$header['stateFull']]][$row[$header['city']]][$row[$header['county']]] = true;
            }

            $counter = 0;
            $batchSize = 1000;
            $completedCounty = [];

            foreach ($dataArray as $stateName => $cityCounty) {
                $state = new City\State();
                $state->setName(trim($stateName));
                $this->em->persist($state);
                $completedCounty[$stateName] = [];
                foreach ($cityCounty as $cityName => $county) {
                    $city = new City();
                    $city->setName(trim($cityName));
                    foreach (array_keys($county) as $countyName) {
                        $countyName = trim(ucwords(strtolower($countyName))).' County';
                        if (false == isset($completedCounty[$stateName][$countyName])) {
                            $newCounty = new City\County();
                            $newCounty->setName($countyName);
                            $newCounty->setState($state);
                            $this->em->persist($newCounty);
                            $completedCounty[$stateName][$countyName] = $newCounty;
                        }
                    }
                    $city->addCounty($completedCounty[$stateName][$countyName]);
                    $this->em->persist($city);
                    $counter++;
                    if ($counter % $batchSize == 0) {
                        $this->em->flush();
                    }
                }
            }
            $this->em->flush();
            $endTime = time();
            $duration = $endTime - $startTime;
            $io->success($counter.' Cities imported in '.$duration.' seconds.');
        }
    }
}
