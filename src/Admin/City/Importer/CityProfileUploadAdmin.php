<?php

namespace App\Admin\City\Importer;

use App\Service\CityProfileUploadImporter;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class CityProfileUploadAdmin extends AbstractAdmin
{
    /** @var CityProfileUploadImporter $importer */
    private $importer;

    public function __construct($code, $class, $baseControllerName, CityProfileUploadImporter $importer)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->importer = $importer;
    }

    protected function configureRoutes(RouteCollection $restrictAction)
    {
        $restrictAction->remove('edit');
    }

    protected $_file_format=array(
        'cityName',
        'stateName',
        'countyName',
        'address',
        'zipCode',
        'passcode',
        'cityHallPhone',
        'jobsHotline',
        'yearFounded',
        'yearChartered',
        'yearIncorporated',
        'censusPopulation2000',
        'censusPopulation2010',
        'squareMiles',
        'countFTE',
        'hrDirectorFirstName',
        'hrDirectorLastName',
        'hrNamePrefix',
        'hrNameSuffix',
        'hrDirectorTitle',
        'hrDirectorPhone',
        'hrDirectorEmail',
        'homePageURL',
        'humanResourcesURL',
        'jobListingURL',
        'jobDescriptionURL',
        'orgChartURL',
        'laborAgreementsURL',
        'salaryTableURL',
        'timezone',
        'timezoneSummer',
        'mondayOpen',
        'mondayClose',
        'tuesdayOpen',
        'tuesdayClose',
        'wednesdayOpen',
        'wednesdayClose',
        'thursdayOpen',
        'thursdayClose',
        'fridayOpen',
        'fridayClose',
        'saturdayOpen',
        'saturdayClose',
        'sundayOpen',
        'sundayClose',
        'hoursDescriptionOther',
        'hoursDescription'
    );

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('fileName')
//            ->add('file')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('fileName')
            ->add('createdAt')
            ->add('result')
//            ->add('file')
//            ->add('_action', null, [
//                'actions' => [
//                    'show' => [],
//                    'edit' => [],
//                    'delete' => [],
//                ],
//            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
//            ->add('id')
//            ->add('fileName')
            ->add('file', FileType::class, [
                'data_class' => null,
                'help' => 'cityName, stateName, countyName, address, zipCode, passcode, cityHallPhone, jobsHotline, yearFounded, yearChartered, yearIncorporated, censusPopulation2000, censusPopulation2010, squareMiles, countFTE, hrDirectorFirstName, hrDirectorLastName, hrNamePrefix, hrNameSuffix, hrDirectorTitle, hrDirectorPhone, hrDirectorEmail, homePageURL, humanResourcesURL, jobListingURL, jobDescriptionURL, orgChartURL, laborAgreementsURL, salaryTableURL, timezone, timezoneSummer, (Monday-Sunday) Ex. mondayOpen/sundayClose, hoursDescriptionOther, hoursDescription'
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('fileName')
            ->add('file')
        ;
    }

    public function prePersist($object)
    {
        $this->manageFileUpload($object);
    }

    public function preUpdate($object)
    {
        $this->manageFileUpload($object);
    }

    private function manageFileUpload($object)
    {

        $file = $object->convertUploadedCsvToArray($object->getFile());
        $checkProfileUploadedFile = $file;
        $checkProfileUploadedFileFormat = array_shift($checkProfileUploadedFile);
        if ($checkProfileUploadedFileFormat != $this->_file_format) {
            $object->removeUploadedFile();
            $this->getRequest()->getSession()->getFlashBag()->add('error', 'CSV Headers are incorrect');
            $object->setFileName($object->getFile()->getClientOriginalName());
            $object->setResult('Error');
        } else {
            $object->setFileName($object->getFile()->getClientOriginalName());
            $result = $this->importer->import($file);
            if (false == $result) {
                $object->setResult('Error');
            } else {
                $object->setResult('Success');
            }
        }
    }
}
