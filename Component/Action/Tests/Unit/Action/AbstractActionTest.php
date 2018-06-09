<?php

namespace Labudzinski\TestFrameworkBundle\Component\Action\Tests\Unit\Action;

use Labudzinski\TestFrameworkBundle\Component\Action\Action\AbstractAction;
use Labudzinski\TestFrameworkBundle\Component\Action\Event\ExecuteActionEvent;
use Labudzinski\TestFrameworkBundle\Component\Action\Event\ExecuteActionEvents;
use Labudzinski\TestFrameworkBundle\Component\Action\Tests\Unit\Action\Stub\ArrayCondition;
use Labudzinski\TestFrameworkBundle\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AbstractActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractAction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $action;

    /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $dispatcher;

    protected function setUp()
    {
        $this->action = $this->getMockBuilder('Labudzinski\TestFrameworkBundle\Component\Action\Action\AbstractAction')
            ->setConstructorArgs(array(new ContextAccessor()))
            ->getMockForAbstractClass();
        $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->action->setDispatcher($this->dispatcher);
    }

    protected function tearDown()
    {
        unset($this->action);
    }

    public function testSetCondition()
    {
        $condition = $this->getMockBuilder('Labudzinski\TestFrameworkBundle\Component\ConfigExpression\ExpressionInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->action->setCondition($condition);
        $this->assertAttributeEquals($condition, 'condition', $this->action);
    }

    /**
     * @param boolean $expectedAllowed
     * @param boolean|null $conditionAllowed
     * @dataProvider executeDataProvider
     */
    public function testExecute($expectedAllowed, $conditionAllowed = null)
    {
        $context = array('key' => 'value');

        if ($expectedAllowed) {
            $this->action->expects($this->once())
                ->method('executeAction')
                ->with($context);
            $this->dispatcher->expects($this->at(0))
                ->method('dispatch')
                ->with(ExecuteActionEvents::HANDLE_BEFORE, new ExecuteActionEvent($context, $this->action));
            $this->dispatcher->expects($this->at(1))
                ->method('dispatch')
                ->with(ExecuteActionEvents::HANDLE_AFTER, new ExecuteActionEvent($context, $this->action));
        } else {
            $this->action->expects($this->never())
                ->method('executeAction');
            $this->dispatcher->expects($this->never())
                ->method('dispatch');
        }

        if ($conditionAllowed !== null) {
            $condition = new ArrayCondition(array('allowed' => $conditionAllowed));
            $this->action->setCondition($condition);
        }

        $this->action->execute($context);
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return array(
            'no condition' => array(
                'expectedAllowed' => true
            ),
            'allowed condition' => array(
                'expectedAllowed'  => true,
                'conditionAllowed' => true
            ),
            'denied condition' => array(
                'expectedAllowed'  => false,
                'conditionAllowed' => false
            ),
        );
    }
}
