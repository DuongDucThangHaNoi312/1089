<?php

namespace App\Command;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitializeDepartmentOrderNumberCommand extends Command
{
    protected static $defaultName = 'app:department:init-order-number';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);

        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $departments = $this->em->getRepository(City\Department::class)->getAllDepartmentsOrderByCity();
        $cities      = [];

        /** @var City\Department $department */
        foreach ($departments as $department) {
            $cityId = $department->getCity()->getId();
            if ( ! in_array($cityId, array_keys($cities))) {
                $department->setOrderByNumber(1);
                $cities[$cityId] = 1;
            } else {
                $department->setOrderByNumber(++$cities[$cityId]);
            }
        }

        $this->em->flush();

        $io->success('Departments updated successfully');
    }
}
