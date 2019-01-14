<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 31.10.17
 */

namespace GepurIt\Tests\AccessManagerBundle;

use GepurIt\AccessManagerBundle\Annotations\Access;
use GepurIt\AccessManagerBundle\EventListener\AccessListener;
use Doctrine\Common\Annotations\Reader;
use GepurIt\Tests\AccessManagerBundle\Controller\TestController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class AccessListenerTest
 * @package AppBundle\Tests\EventListener
 */
class AccessListenerTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testNotController()
    {
        $reader = $this->getReaderMock();
        $auth = $this->getAuthorizationCheckerInterfaceMock();
        $event = $this->getFilterControllerEventMock();
        $event->expects($this->once())
            ->method('getController')
            ->willReturn(null);

        $accessListener = new AccessListener($reader, $auth);
        $accessListener->onKernelController($event);
    }

    /**
     * @throws \ReflectionException
     */
    public function testNoAnnotations()
    {
        $controller = [new TestController(), 'actionTest'];

        $reader = $this->getReaderMock();
        $auth = $this->getAuthorizationCheckerInterfaceMock();
        $event = $this->getFilterControllerEventMock();
        $event->expects($this->once())
            ->method('getController')
            ->willReturn($controller);


        $accessListener = new AccessListener($reader, $auth);
        $accessListener->onKernelController($event);
    }

    /**
     * @throws \ReflectionException
     */
    public function testOnMethodDenyShouldNotCheckClass()
    {
        $controller = [new TestController(), 'actionTest'];
        $level = 'level';
        $resource = 'resource';
        $message = 'message';

        $reader = $this->getReaderMock();
        $auth = $this->getAuthorizationCheckerInterfaceMock();
        $event = $this->getFilterControllerEventMock();
        $event->expects($this->once())
            ->method('getController')
            ->willReturn($controller);

        $methodListener = $this->getAccessAnnotationMock();
        $methodListener->level = $level;
        $methodListener->resource = $resource;
        $methodListener->message = $message;
        $reader->expects($this->once())
            ->method('getMethodAnnotation')
            ->with($this->isInstanceOf(ReflectionMethod::class), Access::class)
            ->willReturn($methodListener);
        $auth->expects($this->once())
            ->method('isGranted')
            ->with($level, $resource)
            ->willReturn(false);
        $reader->expects($this->never())
            ->method('getClassAnnotation');

        $accessListener = new AccessListener($reader, $auth);

        $this->expectException(AccessDeniedException::class);

        $accessListener->onKernelController($event);
    }

    /**
     * @throws \ReflectionException
     */
    public function testOnMethodAllowClassNotExists()
    {
        $controller = [new TestController(), 'actionTest'];
        $level = 'level';
        $resource = 'resource';
        $message = 'message';

        $reader = $this->getReaderMock();
        $auth = $this->getAuthorizationCheckerInterfaceMock();
        $event = $this->getFilterControllerEventMock();
        $event->expects($this->once())
            ->method('getController')
            ->willReturn($controller);

        $methodListener = $this->getAccessAnnotationMock();
        $methodListener->level = $level;
        $methodListener->resource = $resource;
        $methodListener->message = $message;
        $reader->expects($this->once())
            ->method('getMethodAnnotation')
            ->with($this->isInstanceOf(ReflectionMethod::class), Access::class)
            ->willReturn($methodListener);
        $reader->expects($this->once())
            ->method('getClassAnnotation')
            ->with($this->isInstanceOf(ReflectionClass::class), Access::class)
            ->willReturn(null);
        $auth->expects($this->once())
            ->method('isGranted')
            ->with($level, $resource)
            ->willReturn(true);

        $accessListener = new AccessListener($reader, $auth);

        $accessListener->onKernelController($event);
    }

    /**
     * @throws \ReflectionException
     */
    public function testClassDenyIfNoMethodAnnotationDefined()
    {
        $controller = [new TestController(), 'actionTest'];
        $level = 'level';
        $resource = 'resource';
        $message = 'message';

        $reader = $this->getReaderMock();
        $auth = $this->getAuthorizationCheckerInterfaceMock();
        $event = $this->getFilterControllerEventMock();
        $event->expects($this->once())
            ->method('getController')
            ->willReturn($controller);

        $classAnnotation = $this->getAccessAnnotationMock();
        $classAnnotation->level = $level;
        $classAnnotation->resource = $resource;
        $classAnnotation->message = $message;
        $reader->expects($this->once())
            ->method('getMethodAnnotation')
            ->with($this->isInstanceOf(ReflectionMethod::class), Access::class)
            ->willReturn(null);

        $reader->expects($this->once())
            ->method('getClassAnnotation')
            ->with($this->isInstanceOf(ReflectionClass::class), Access::class)
            ->willReturn($classAnnotation);

        $auth->expects($this->once())
            ->method('isGranted')
            ->with($level, $resource)
            ->willReturn(false);

        $accessListener = new AccessListener($reader, $auth);

        $this->expectException(AccessDeniedException::class);

        $accessListener->onKernelController($event);
    }

    /**
     * @throws \ReflectionException
     */
    public function testClassDenyIfMethodAnnotationAllows()
    {
        $controller = [new TestController(), 'actionTest'];
        $level = 'level';
        $resource = 'resource';
        $message = 'message';

        $reader = $this->getReaderMock();
        $auth = $this->getAuthorizationCheckerInterfaceMock();
        $event = $this->getFilterControllerEventMock();
        $event->expects($this->once())
            ->method('getController')
            ->willReturn($controller);

        $classAnnotation = $this->getAccessAnnotationMock();
        $classAnnotation->level = $level;
        $classAnnotation->resource = $resource;
        $classAnnotation->message = $message;

        $methodAnnotation = $this->getAccessAnnotationMock();
        $methodAnnotation->level = $level;
        $methodAnnotation->resource = $resource;
        $methodAnnotation->message = $message;
        $reader->expects($this->once())
            ->method('getMethodAnnotation')
            ->with($this->isInstanceOf(ReflectionMethod::class), Access::class)
            ->willReturn($methodAnnotation);

        $reader->expects($this->once())
            ->method('getClassAnnotation')
            ->with($this->isInstanceOf(ReflectionClass::class), Access::class)
            ->willReturn($classAnnotation);

        $auth->expects($this->at(0))
            ->method('isGranted')
            ->with($level, $resource)
            ->willReturn(true);

        $auth->expects($this->at(0))
            ->method('isGranted')
            ->with($level, $resource)
            ->willReturn(false);

        $accessListener = new AccessListener($reader, $auth);

        $this->expectException(AccessDeniedException::class);

        $accessListener->onKernelController($event);
    }

    /**
     * @throws \ReflectionException
     */
    public function testAllAnnotationsAllows()
    {
        $controller = [new TestController(), 'actionTest'];
        $level = 'level';
        $resource = 'resource';
        $message = 'message';

        $reader = $this->getReaderMock();
        $auth = $this->getAuthorizationCheckerInterfaceMock();
        $event = $this->getFilterControllerEventMock();
        $event->expects($this->once())
            ->method('getController')
            ->willReturn($controller);

        $classAnnotation = $this->getAccessAnnotationMock();
        $classAnnotation->level = $level;
        $classAnnotation->resource = $resource;
        $classAnnotation->message = $message;

        $methodAnnotation = $this->getAccessAnnotationMock();
        $methodAnnotation->level = $level;
        $methodAnnotation->resource = $resource;
        $methodAnnotation->message = $message;
        $reader->expects($this->once())
            ->method('getMethodAnnotation')
            ->with($this->isInstanceOf(ReflectionMethod::class), Access::class)
            ->willReturn($methodAnnotation);

        $reader->expects($this->once())
            ->method('getClassAnnotation')
            ->with($this->isInstanceOf(ReflectionClass::class), Access::class)
            ->willReturn($classAnnotation);

        $auth->expects($this->at(0))
            ->method('isGranted')
            ->with($level, $resource)
            ->willReturn(true);

        $auth->expects($this->at(1))
            ->method('isGranted')
            ->with($level, $resource)
            ->willReturn(true);

        $accessListener = new AccessListener($reader, $auth);

        $accessListener->onKernelController($event);
    }

    /**
     * @return Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getReaderMock()
    {
        $mock =  $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getMethodAnnotation',
                'getClassAnnotations',
                'getClassAnnotation',
                'getMethodAnnotations',
                'getPropertyAnnotation',
                'getPropertyAnnotations'
            ])
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthorizationCheckerInterface
     */
    private function getAuthorizationCheckerInterfaceMock()
    {
        $mock =  $this->getMockBuilder(AuthorizationCheckerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isGranted',
            ])
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterControllerEvent
     */
    private function getFilterControllerEventMock()
    {
        $mock =  $this->getMockBuilder(FilterControllerEvent::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getController',
            ])
            ->getMock();

        return $mock;
    }


    private function getAccessAnnotationMock()
    {
        $mock =  $this->getMockBuilder(Access::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        return $mock;
    }
}

