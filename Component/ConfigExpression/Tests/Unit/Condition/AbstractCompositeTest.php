<?php

namespace Labudzinski\TestFrameworkBundle\Component\ConfigExpression\Tests\Unit\Condition;

use Labudzinski\TestFrameworkBundle\Component\ConfigExpression\Condition;

class AbstractCompositeTest extends \PHPUnit_Framework_TestCase
{
    /** @var Condition\AbstractComposite */
    protected $condition;

    protected function setUp()
    {
        $this->condition = $this->getMockForAbstractClass(
            'Labudzinski\TestFrameworkBundle\Component\ConfigExpression\Condition\AbstractComposite'
        );
    }

    public function testInitializeSuccess()
    {
        $operands = [$this->createMock('Labudzinski\TestFrameworkBundle\Component\ConfigExpression\ExpressionInterface')];

        $this->assertSame($this->condition, $this->condition->initialize($operands));
        $this->assertAttributeEquals($operands, 'operands', $this->condition);
    }

    /**
     * @expectedException \Labudzinski\TestFrameworkBundle\Component\ConfigExpression\Exception\InvalidArgumentException
     * @expectedExceptionMessage Options must have at least one element
     */
    public function testInitializeFailsWithEmptyElements()
    {
        $this->condition->initialize([]);
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Labudzinski\TestFrameworkBundle\Component\ConfigExpression\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Invalid type of option "0". Expected "Labudzinski\TestFrameworkBundle\Component\ConfigExpression\ExpressionInterface", "string" given.
     */
    // @codingStandardsIgnoreEnd
    public function testInitializeFailsWithScalarElement()
    {
        $this->condition->initialize(['anything']);
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Labudzinski\TestFrameworkBundle\Component\ConfigExpression\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Invalid type of option "0". Expected "Labudzinski\TestFrameworkBundle\Component\ConfigExpression\ExpressionInterface", "stdClass" given.
     */
    // @codingStandardsIgnoreEnd
    public function testInitializeFailsWithWrongInstanceElement()
    {
        $this->condition->initialize([new \stdClass]);
    }
}
