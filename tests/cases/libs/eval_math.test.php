<?php
App::import('Rutils.EvalMath');

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
		
		$result = $Eval->evaluate('3 * 2.5');
		$this->assertIdentical('7.500', $result);
		
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
		
		try {
			$fail = $Eval->evaluate('a / 0');
		}
		catch(Exception $e)
		{
			$this->assertEqual($Eval->getLastError(), $e->getMessage());
		}
		
		$result = $Eval->evaluate('a / 2');
		$this->assertIdentical('1.000', $result);
		
		unset($Eval);
	}
	
	public function testEvaluateWithTwoVars()
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
	
	public function testIllegalOperations()
	{
		$Eval = new EvalMath();
		
		try {
			$fail = $Eval->evaluate('a / 0');
		}
		catch(Exception $e)
		{
			$this->assertEqual($Eval->getLastError(), $e->getMessage());
		}
		
		try {
			$result = $Eval->evaluate('_2');
		}
		catch(Exception $e)
		{
			$this->assertEqual($Eval->getLastError(), $e->getMessage());
		}
		
		try {
			$result = $Eval->evaluate('a');
		}
		catch(Exception $e)
		{
			$this->assertEqual($Eval->getLastError(), $e->getMessage());
		}
		
		try {
			$result = $Eval->evaluate('e = 1');
		}
		catch(Exception $e)
		{
			$this->assertEqual($Eval->getLastError(), $e->getMessage());
		}
		
		try {
			$result = $Eval->evaluate('sin(x) = 2*x');
		}
		catch(Exception $e)
		{
			$this->assertEqual($Eval->getLastError(), $e->getMessage());
		}
		
		try {
			$result = $Eval->evaluate('a(1,2');
		}
		catch(Exception $e)
		{
			$this->assertEqual($Eval->getLastError(), $e->getMessage());
		}
		
		unset($Eval);
	}
}