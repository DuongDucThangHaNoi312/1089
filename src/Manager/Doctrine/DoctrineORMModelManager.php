<?php

namespace App\Manager\Doctrine;

class DoctrineORMModelManager extends \Miracode\StripeBundle\Manager\Doctrine\DoctrineORMModelManager
{
    public function convert($object) {
        $model = $this->createModel($object);
        $this->modelTransformer->transform($object, $model);
        return $model;
    }
}
