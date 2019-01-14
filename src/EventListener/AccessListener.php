<?php

namespace GepurIt\AccessManagerBundle\EventListener;

use GepurIt\AccessManagerBundle\Annotations\Access;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class AccessListener
 * @package AppBundle\EventListener
 */
class AccessListener
{
    /** @var AnnotationReader $reader */
    private $reader;

    /** @var  ContainerInterface $auth */
    private $auth;

    /**
     * AccessListener constructor.
     * @param Reader $reader
     * @param AuthorizationCheckerInterface $auth
     */
    public function __construct(Reader $reader, AuthorizationCheckerInterface $auth)
    {
        $this->reader = $reader;
        $this->auth = $auth;
    }

    /**
     * @param FilterControllerEvent $event
     * @throws \ReflectionException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        list($controllerObject, $methodName) = $controller;

        /** @var Access $methodAnnotation */
        $controllerReflection = new \ReflectionObject($controllerObject);
        $reflectionMethod = $controllerReflection->getMethod($methodName);
        $methodAnnotation = $this->reader->getMethodAnnotation($reflectionMethod, Access::class);
        if (null !== $methodAnnotation) {
            $this->denyAccessUnlessGranted($methodAnnotation);
        }

        /** @var Access $classAnnotation */
        $classAnnotation = $this->reader->getClassAnnotation(
            new \ReflectionClass(ClassUtils::getClass($controllerObject)),
            Access::class
        );
        if (null !== $classAnnotation) {
            $this->denyAccessUnlessGranted($classAnnotation);
        }
    }

    /**
     * @param Access $annotation
     * @throws AccessDeniedException
     */
    private function denyAccessUnlessGranted(Access $annotation)
    {
        if ($this->auth->isGranted($annotation->level, $annotation->resource)) {
            return;
        }
        $exception = new AccessDeniedException($annotation->message);
        $exception->setAttributes($annotation->level);
        $exception->setSubject($annotation->resource);

        throw $exception;
    }
}