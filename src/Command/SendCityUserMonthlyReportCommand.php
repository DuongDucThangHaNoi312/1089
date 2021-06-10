<?php

namespace App\Command;

use App\Entity\AlertedJobAnnouncement;
use App\Entity\City;
use App\Entity\JobAnnouncement\View;
use App\Entity\User\CityUser;
use App\Service\EmailHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class SendCityUserMonthlyReportCommand
 * @package App\Command
 */
class SendCityUserMonthlyReportCommand extends Command
{
    use TimestampableEntity;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var EmailHelper
     */
    private $helper;

    /**
     * @var string
     */
    protected static $defaultName = 'app:city-user:send-monthly-report';


    /**
     * SendCityUserMonthlyReportCommand constructor.
     *
     * @param EntityManagerInterface $em
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $templating
     * @param null $name
     */
    public function __construct(EntityManagerInterface $em, \Swift_Mailer $mailer, EngineInterface $templating, EmailHelper $helper, $name = null)
    {
        parent::__construct($name);

        $this->em         = $em;
        $this->mailer     = $mailer;
        $this->templating = $templating;
        $this->helper     = $helper;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io                  = new SymfonyStyle($input, $output);
        $firstDayOfLastMonth = date('Y-m-d', strtotime('first day of last month'));
        $lastDayOfLastMonth  = date('Y-m-d', strtotime('last day of last month'));


        $cityRepo     = $this->em->getRepository(City::class);
        $jaViewRepo   = $this->em->getRepository(View::class);
        $cityUserRepo = $this->em->getRepository(CityUser::class);
        $ajaRepo      = $this->em->getRepository(AlertedJobAnnouncement::class);

        $count = $perPage = getenv('PAGE_SIZE');
        $page  = 1;
        $sent  = 0;

        while ($count == $perPage) {
            $offset    = ($page - 1) * $perPage;
            $cityUsers = $cityUserRepo->findCityUserForMonthlyReport($offset, $perPage);

            /** @var CityUser $c */
            foreach ($cityUsers as $c) {
                if ($c->getCity()) {
                    $cityId = $c->getCity()->getId();

                    $countInterestedJobSeekers             = $cityRepo->getCountOfUsersWhoSubmittedInterest($cityId, $firstDayOfLastMonth, $lastDayOfLastMonth);
                    $countOfJobSeekerReceivedNotifications = $ajaRepo->getCountOfAlertedJobSeekers($cityId, $firstDayOfLastMonth, $lastDayOfLastMonth);
                    $countJobAnnouncementPageClicks        = $jaViewRepo->getCountOfClicksOnJobAnnouncementPages($cityId, $firstDayOfLastMonth, $lastDayOfLastMonth);

                    $this->helper->sendCityUserMonthlyReport($c, $countInterestedJobSeekers, $countOfJobSeekerReceivedNotifications, $countJobAnnouncementPageClicks);
                }
                $sent++;
            }

            $page++;
            $count = count($cityUsers);
        }

        $io->success('Notifications sent successfully to ' . $sent . ' city user(s).');
    }
}
