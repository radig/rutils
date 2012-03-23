<?php
App::import('Lib', 'Rutils.EvalMath');
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

	public function testClear()
	{
		$Eval = new EvalMath();

		$Eval->evaluate('a = 2');
		$Eval->evaluate('f(a) = 2a');

		$this->assertIdentical(array('a' => '2.000'), $Eval->vars());
		$Eval->clear('vars');
		$this->assertIdentical(array(), $Eval->vars());

		$this->assertIdentical(array('f(a)'), $Eval->funcs());
		$Eval->clear('funcs');
		$this->assertIdentical(array(), $Eval->funcs());

		$Eval->evaluate('a = 2');
		$Eval->evaluate('f(a) = 2a');
		$Eval->clear();
		$this->assertIdentical(array(), $Eval->funcs());
		$this->assertIdentical(array(), $Eval->vars());
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

	public function testEvaluateWithManyVarsAndHighPrecision()
	{
		$Eval = new EvalMath(4);
		$Eval->evaluate('a = 125.8116');
		$Eval->evaluate('b = 125.7599');
		$Eval->evaluate('c = 250');

		$result = $Eval->evaluate('((a-b)*10^6)/c');
		$this->assertIdentical(206.8, (float)$result);
		$this->assertIdentical('206.8000', $result);

		$Eval->clear();
		$Eval->evaluate('a = 25.2524');
		$Eval->evaluate('b = 25.1028');
		$Eval->evaluate('c = 25');

		$result = $Eval->evaluate('((a-b)*10^6)/c');
		$this->assertIdentical(5984.0, (float)$result);
		$this->assertIdentical('5984.0000', $result);

		$Eval->clear();
		$Eval->evaluate('a = 25.1917');
		$Eval->evaluate('b = 25.1028');
		$Eval->evaluate('c = 25');

		$result = $Eval->evaluate('((a-b)*10^6)/c');
		$this->assertIdentical(3556.0, (float)$result);
		$this->assertIdentical('3556.0000', $result);
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
		$this->assertIdentical('1.000', $result);

		$result = $Eval->evaluate('log10(1)');
		$this->assertIdentical('0.000', $result);

		$Eval->clear();
		$Eval->evaluate('f(x) = sqrt(x)');
		$result = $Eval->evaluate('f(4)');
		$this->assertIdentical('2.000', $result);

		$Eval->evaluate('g(x) = sqrt(2x) + ln(e)');
		$result = $Eval->evaluate('g(2)');
		$this->assertIdentical('3.000', $result);

		unset($Eval);
	}

	public function testIllegalOperations()
	{
		$Eval = new EvalMath();

		$this->expectError('Division by zero');
		$result = $Eval->evaluate('1 / 0');
		$this->assertEqual($Eval->getLastError(), 'Division by zero');
		$this->assertIdentical(false, $result);

		$this->expectError('Undefined variable \'a\'');
		$result = $Eval->evaluate('a');
		$this->assertEqual($Eval->getLastError(), 'Undefined variable \'a\'');
		$this->assertIdentical(false, $result);

		$this->expectError('Illegal character \'_\'');
		$result = $Eval->evaluate('_2');
		$this->assertEqual($Eval->getLastError(), 'Illegal character \'_\'');
		$this->assertIdentical(false, $result);

		$this->expectError('Cannot assign to constant \'e\'');
		$result = $Eval->evaluate('e = 1');
		$this->assertEqual($Eval->getLastError(), 'Cannot assign to constant \'e\'');
		$this->assertIdentical(false, $result);

		$this->expectError('Cannot redefine built-in function \'sin()\'');
		$result = $Eval->evaluate('sin(x) = 2*x');
		$this->assertEqual($Eval->getLastError(), 'Cannot redefine built-in function \'sin()\'');
		$this->assertIdentical(false, $result);

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

		$Eval->suppress_errors = false;

		$this->expectError('Unexpected \',\'');
		$result = $Eval->evaluate('a(1,2');
		$this->assertEqual($Eval->getLastError(), 'Unexpected \',\'');
		$this->assertIdentical(false, $result);

		$this->expectError('Illegal character \'#\'');
		$result = $Eval->evaluate('#');
		$this->assertEqual($Eval->getLastError(), 'Illegal character \'#\'');
		$this->assertIdentical(false, $result);

		$this->expectError('Wrong number of arguments (3 given, 2 expected)');
		$Eval->evaluate('f(x,y) = x+y');
		$result = $Eval->evaluate('f(2,3,4)');
		$this->assertEqual($Eval->getLastError(), 'Wrong number of arguments (3 given, 2 expected)');
		$this->assertIdentical(false, $result);

		$this->expectError('Too many arguments (2 given, 1 expected)');
		$result = $Eval->evaluate('log(2,1)');
		$this->assertEqual($Eval->getLastError(), 'Too many arguments (2 given, 1 expected)');
		$this->assertIdentical(false, $result);

		$this->expectError('Undefined variable \'z\' in function definition');
		$result = $Eval->evaluate('f(x,y) = x+y+z');
		$this->assertEqual($Eval->getLastError(), 'Undefined variable \'z\' in function definition');
		$this->assertIdentical(false, $result);

		unset($Eval);
	}
}