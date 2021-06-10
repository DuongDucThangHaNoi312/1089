<?php

namespace App\DataFixtures\ORM\CMS;

use App\DataFixtures\ORM\MultilineBaseFixture;
use Proxies\__CG__\App\Entity\CMSBlock;

class CMSBlocksFixture extends MultilineBaseFixture {

    public function getFileName()
    {
        return "CMSBlocks.csv";
    }

    public function getObject()
    {
        return CMSBlock::class;
    }

    public function getBasePath()
    {
        return parent::getBasePath() . 'CMS/';
    }

    /**
     * @param mixed|CMSBlock $object
     * @param array $value
     * @param array $header
     */
    public function create(&$object, $value = array(), $header = array())
    {
        $object->setName($value[$header['name']]);
        $object->setContent($value[$header['content']]);
        $object->setSlug($value[$header['slug']]);
    }
}