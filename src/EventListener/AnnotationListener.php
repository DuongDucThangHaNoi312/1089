<?php

namespace App\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class AnnotationListener {

    protected $reader;
    protected $em;

    /**
     * AnnotationListener constructor.
     * @param Reader $reader
     * @param EntityManager $entityManager
     */
    public function __construct(Reader $reader, EntityManager $entityManager) {
        $this->reader = $reader;
        $this->em = $entityManager;
    }

    /**
     * @param FilterControllerEvent $event
     * @throws \ReflectionException
     */
    public function onKernelController(FilterControllerEvent $event) {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        list($controller, $method, ) = $controller;

        $this->ignoreSoftDeleteAnnotation($controller, $method);
    }

    /**
     * @param $controller
     * @param $method
     * @param $annotation
     * @return array|bool
     * @throws \ReflectionException
     */
    private function readAnnotation($controller, $method, $annotation) {
        $classReflection = new \ReflectionClass(ClassUtils::getClass($controller));
        $classAnnotation = $this->reader->getClassAnnotation($classReflection, $annotation);

        $objectReflection = new \ReflectionObject($controller);
        $methodReflection = $objectReflection->getMethod($method);
        $methodAnnotation = $this->reader->getMethodAnnotation($methodReflection, $annotation);
        if (!$classAnnotation && !$methodAnnotation) {
            return false;
        }

        return [$classAnnotation, $classReflection, $methodAnnotation, $methodReflection];
    }

    /**
     * @param $controller
     * @param $method
     * @throws \ReflectionException
     */
    private function ignoreSoftDeleteAnnotation($controller, $method) {
        static $class = 'App\Annotation\IgnoreSoftDelete';

        if ($this->readAnnotation($controller, $method, $class)) {
            $this->em->getFilters()->disable('softdeleteable');
        }
    }

}
