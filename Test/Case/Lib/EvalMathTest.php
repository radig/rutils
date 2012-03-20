<?php
App::uses('EvalMath', 'Rutil.Lib');


class EvalMathTest extends CakeTestCase
{
	public function testDefaultConfig()
	{
		$Eval = new EvalMath();
		
		$expected = array();
		
		$this->assertFalse($Eval->suppress_errors);
		$this->assertEqual($Eval->funcs(), $expected);
		$this->assertEqual($Eval->vars(), $expected);
		$this->assertIdentical($Eval->getLastError(), null);
		
		unset($Eval);
	}
	
	public function testNonDefaultConfig()
	{
		$Eval = new EvalMath(1);
		
		$result = $Eval->evaluate(1);
		$this->assertIdentical('1.0', $result);
		
		$result = $Eval->evaluate(-1);
		$this->assertIdentical('-1.0', $result);
		
		$result = $Eval->evaluate(1, 2);
		$this->assertIdentical('1.00', $result);
		
		$result = $Eval->evaluate(-1, 4);
		$this->assertIdentical('-1.0000', $result);
	
		unset($Eval);
	}
	
	public function testBasicsWithDefault()
	{
		$Eval = new EvalMath();
		
		$expected = array();
		
		$result = $Eval->evaluate(1);
		$this->assertIdentical('1.000', $result);
		
		$result = $Eval->evaluate(-1);
		$this->assertIdentical('-1.000', $result);
		
		$result = $Eval->evaluate('1 + 2');
		$this->assertIdentical('3.000', $result);
		
		$result = $Eval->evaluate('3 * 2.5');
		$this->assertIdentical('7.500', $result);
		
		$result = $Eval->evaluate('5 / 2.5');
		$this->assertIdentical('2.000', $result);
		
		unset($Eval);
	}
	
	public function testBasicsWithNonDefault()
	{
		$Eval = new EvalMath(1);
	
		$result = $Eval->evaluate('1.54 + 1.36', 0);
		$this->assertIdentical('2', $result);
		
		$result = $Eval->evaluate('1.54 + 1.36', 1);
		$this->assertIdentical('2.9', $result);
		
		$result = $Eval->evaluate('1.54 + 1.36', 2);
		$this->assertIdentical('2.90', $result);
	
		unset($Eval);
	}
	
	public function testEvaluateWithVar()
	{
		$Eval = new EvalMath();
		
		$expected = array();
		
		$Eval->evaluate('a = 2');
		$this->assertIdentical($Eval->vars(), array('a' => '2.000'));
		
		$result = $Eval->evaluate('a');
		$this->assertIdentical('2.000', $result);
		
		$result = $Eval->evaluate('a + 2');
		$this->assertIdentical('4.000', $result);
		
		$result = $Eval->evaluate('a - 3');
		$this->assertIdentical('-1.000', $result);
		
		$result = $Eval->evaluate('a * 3');
		$this->assertIdentical('6.000', $result);
		
		$result = $Eval->evaluate('a / 2');
		$this->assertIdentical('1.000', $result);
		
		unset($Eval);
	}
	
	public function testEvaluateWithManyVars()
	{
		$Eval = new EvalMath();
	
		$expected = array();
	
		$Eval->evaluate('a = 2');
		$Eval->evaluate('b = 3;');
		$this->assertIdentical($Eval->vars(), array('a' => '2.000', 'b' => '3.000'));
	
		$result = $Eval->evaluate('a');
		$this->assertIdentical('2.000', $result);
		
		$result = $Eval->evaluate('b');
		$this->assertIdentical('3.000', $result);
	
		$result = $Eval->evaluate('a + b');
		$this->assertIdentical('5.000', $result);
	
		$result = $Eval->evaluate('a - b');
		$this->assertIdentical('-1.000', $result);
	
		$result = $Eval->evaluate('a * b');
		$this->assertIdentical('6.000', $result);
		
		$result = $Eval->evaluate('a ^ b');
		$this->assertIdentical('8.000', $result);
	
		$result = $Eval->evaluate('a / b');
		$this->assertIdentical('0.666', $result);
		
		$Eval->evaluate('radig = 10');
		
		$result = $Eval->evaluate('radig * a');
		$this->assertIdentical('20.000', $result);
		
		$Eval->evaluate('c = 15');
		$Eval->evaluate('d = 2');
		
		$result = $Eval->evaluate('(c/10)*10^(1+d)');
		$this->assertIdentical('1500.000', $result);
	
		unset($Eval);
	}
	
	public function testEvaluateWithFunction()
	{
		$Eval = new EvalMath();
		
		$Eval->evaluate('f(x,y) = x^2 + y^2 - 2x*y + 1');
		$result = $Eval->evaluate('3*f(1,3);');
		$this->assertIdentical('15.000', $result);
		
		$result = $Eval->funcs();
		$this->assertIdentical(array('f(x,y)'), $result);
		
		$result = $Eval->evaluate('sin(pi/2)');
		$this->assertIdentical('0.000', $result);
		
		unset($Eval);
	}
	
	/**
     * @expectedException PHPUnit_Framework_Error
     */
	public function testIllegalOperations()
	{
		$Eval = new EvalMath();
		
		$result = $Eval->evaluate('1 / 0');
		$this->assertEqual($Eval->getLastError(), 'Division by zero');
		$this->assertIdentical(false, $result);
		
		$result = $Eval->evaluate('a');
		$this->assertEqual($Eval->getLastError(), 'Undefined variable \'a\'');
		$this->assertIdentical(false, $result);
		
		$result = $Eval->evaluate('_2');
		$this->assertEqual($Eval->getLastError(), 'Illegal character \'_\'');
		$this->assertIdentical(false, $result);
		
		$result = $Eval->evaluate('e = 1');
		$this->assertEqual($Eval->getLastError(), 'Cannot assign to constant \'e\'');
		$this->assertIdentical(false, $result);
		
		$result = $Eval->evaluate('sin(x) = 2*x');
		$this->assertEqual($Eval->getLastError(), 'Cannot redefine built-in function \'sin()\'');

		$result = $Eval->evaluate('array(1,2');
		$this->assertEqual($Eval->getLastError(), 'Unexpected \',\'');
		
		unset($Eval);
	}
	
	public function testInvalidExpressions()
	{
		$Eval = new EvalMath();
		
		$Eval->suppress_errors = true;
		
		$result = $Eval->evaluate('()');
		$this->assertEqual($Eval->getLastError(), 'Unexpected \')\'');
		
		$result = $Eval->evaluate('1)');
		$this->assertEqual($Eval->getLastError(), 'Unexpected \')\'');
		
		$result = $Eval->evaluate('((');
		$this->assertEqual($Eval->getLastError(), 'Expecting \')\'');
		
		$result = $Eval->evaluate('2+3+');
		$this->assertEqual($Eval->getLastError(), 'Operator \'+\' lacks operand');
		
		unset($Eval);
	}
}