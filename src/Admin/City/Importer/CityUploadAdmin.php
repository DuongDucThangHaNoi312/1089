<?php

namespace App\Admin\City\Importer;

use App\Service\CityUploadImporter;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class CityUploadAdmin extends AbstractAdmin
{
    /** @var CityUploadImporter $importer */
    private $importer;

    public function __construct($code, $class, $baseControllerName, CityUploadImporter $importer)
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
        'cityPrefix',
        'stateName',
        'stateAbbreviation',
        'county1Name',
        'county2Name',
        'county3Name',
        'county4Name',
        'county5Name'
    );

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('fileName')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
//            ->add('file')
            ->add('fileName')
            ->add('createdAt')
            ->add('result')
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
            ->add('file', FileType::class, [
                'data_class' => null,
                'help' => 'CSV Headers must be cityName, cityPrefix, stateName, stateAbbreviation, county1Name, county2Name, county3Name, county4Name, county5Name'
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('fileName')
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
        $object->removeUploadedFile();
        $checkUploadedFile = $file;
        $checkUploadedFileFormat = array_shift($checkUploadedFile);
        if ($checkUploadedFileFormat != $this->_file_format) {
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
