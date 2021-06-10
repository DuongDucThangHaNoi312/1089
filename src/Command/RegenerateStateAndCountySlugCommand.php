<?php

namespace App\Command;

use App\Entity\City\County;
use App\Entity\City\State;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RegenerateStateAndCountySlugCommand extends Command
{
    protected static $defaultName = 'app:regenerate:state-and-county-slug';

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
        $io       = new SymfonyStyle($input, $output);
        $counties = $this->em->getRepository(County::class)->findAll();
        $states   = $this->em->getRepository(State::class)->findAll();

        /** @var County $county */
        foreach ($counties as $county) {
            $county->setSlug(null);
        }

        /** @var State $state */
        foreach ($states as $state) {
            $state->setSlug(null);
        }

        $this->em->flush();

        $io->success('All slugs of states and counties have been successfully regenerated!');
    }
}
