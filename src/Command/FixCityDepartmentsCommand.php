<?php

namespace App\Command;

use App\Entity\City\Department;
use App\Entity\City\JobTitle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixCityDepartmentsCommand extends Command
{
    protected static $defaultName = 'app:department:fix';

    private $em;

    public function __construct(EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Fix department name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
//        $arg1 = $input->getArgument('arg1');
//
//        if ($arg1) {
//            $io->note(sprintf('You passed an argument: %s', $arg1));
//        }
//
//        if ($input->getOption('option1')) {
//            // ...
//        }


        $csvFilePath = 'src/DataFixtures/CSV/City/department-20200520.csv';

        $counter  = 0;
        $notfound = [];
        if (($handle = fopen($csvFilePath, "r")) !== false) {

            $io->note('Starting fixing department names');

            $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
            $repo = $this->em->getRepository(Department::class);

            $cityWarned = [];
            while (($row = fgetcsv($handle)) !== false) {

                if ($counter == 0) {
                    $counter++;
                    continue;
                }

                $id     = $row[0];
                $cityId = $row[1];
                $name   = $row[2];
                $slug   = $row[3];


                /** @var Department $department */
                $department = $repo->find($id);

                if ($department) {
                    if ($name != $department->getName()) {

                        $department->setName($name);
                        $department->setSlug($slug);
                        $counter++;

                        if ( ! in_array($cityId, $cityWarned)) {
                            $cityWarned[] = $cityId;
                            $io->warning("Departments of city $cityId have been updated.");
                        }

                    }
                } else {
                    $notfound[] = $id;
                }


            }
        }

        $this->em->flush();

        $io->warning('These deparments not found in new DB: ' . implode(', ', $notfound));
        $io->success(--$counter . ' department names have been fixed.');


        // City 28553 has departments deleted & recreated
        $departmentMapping = [
            586 => 1634,
            587 => 1636,
            588 => 1637,
            589 => 1635,
            590 => 1638,
            591 => 1639,
            592 => 1633,
            593 => 1640,
            594 => 1641,
        ];
        $jobTitleFilePath  = 'src/DataFixtures/CSV/City/job_title_city_28553.csv';

        $counter  = 0;
        $notfound = [];
        if (($handle = fopen($jobTitleFilePath, "r")) !== false) {

            $io->note('Starting fixing job titles of city 28553');
            $jtRepo   = $this->em->getRepository(JobTitle::class);
            $deptRepo = $this->em->getRepository(Department::class);

            while (($row = fgetcsv($handle)) !== false) {

                if ($counter == 0) {
                    $counter++;
                    continue;
                }

                $jobTitleId   = $row[0];
                $departmentId = $row[1];


                /** @var JobTitle $jobTitle */
                $jobTitle = $jtRepo->find($jobTitleId);

                if ($jobTitle && array_key_exists($departmentId, $departmentMapping)) {
                    $department = $deptRepo->find($departmentMapping[$departmentId]);
                    $jobTitle->setDepartment($department);
                    $counter++;
                }
            }
        }
        $this->em->flush();
        
        $io->success(--$counter . ' job title departments have been fixed.');

    }
}
