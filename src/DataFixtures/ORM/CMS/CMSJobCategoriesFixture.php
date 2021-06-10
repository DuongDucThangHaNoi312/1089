<?php

namespace App\DataFixtures\ORM\CMS;

use App\DataFixtures\ORM\MultilineBaseFixture;
use App\Entity\CMSJobCategory;
use Doctrine\ORM\EntityManagerInterface;

class CMSJobCategoriesFixture extends MultilineBaseFixture
{

    /**
     * CMSJobCategoriesFixture constructor.
     *
     * ./bin/console doctrine:fixtures:load --group=CMSJobCategoriesFixture --append
     *
     * In order to import this fixture only, and not to purge the database we need --append option.
     * However there could have duplicated items, so we need to delete the table ourselves.
     *
     * @param EntityManagerInterface $em
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(EntityManagerInterface $em)
    {
        $stmt = $em->getConnection()->prepare('DELETE FROM cmsjob_category');
        $stmt->execute();
    }

    public function getFileName()
    {
        return "CMSJobCategories.csv";
    }

    public function getObject()
    {
        return CMSJobCategory::class;
    }

    public function getBasePath()
    {
        return parent::getBasePath() . 'CMS/';
    }

    /**
     * @param mixed|CMSJobCategory $object
     * @param array $value
     * @param array $header
     */
    public function create(&$object, $value = array(), $header = array())
    {
        $object->setName($value[$header['name']]);
        $object->setGridImage($value[$header['gridImage']]);
        $object->setDescription($value[$header['description']]);
    }
}
