<?php


namespace App\Twig;

use App\Entity\AlertedJobAnnouncement;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AdminExtension extends AbstractExtension
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * undocumented function
     *
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('countOfAlertedByJobTitle', [$this, 'getCountOfAlertedByJobTitle']),
        ];
    }

    public function getCountOfAlertedByJobTitle($jobAnnouncement)
    {
        $alertedRepo = $this->em->getRepository(AlertedJobAnnouncement::class);
        $alerted     = $alertedRepo->getCountOfAlertedByJobTitle($jobAnnouncement->getJobTitle()->getId(),$jobAnnouncement->getStatus()->getId());

        return $alerted;
    }

}
