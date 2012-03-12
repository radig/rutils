<?php
App::uses('Stack', 'Rutils.Lib');

class StackTest extends CakeTestCase
{
	public function testInitialValues()
	{
		$Stack = new Stack();
		
		$this->assertIdentical($Stack->size(), 0);
		
		$this->assertIdentical($Stack->nth(0), null);
		
		$result = $Stack->nth();
		$this->assertIdentical($result, null);
		
		unset($Stack);
	}
	
	public function testInsertAndRemove()
	{
		$Stack = new Stack();
		
		$Stack->push('Radig');
		
		$this->assertIdentical($Stack->size(), 1);
		$this->assertIdentical($Stack->nth(), 'Radig');
		
		$Stack->push('CakePHP');
		
		$this->assertIdentical($Stack->size(), 2);
		
		// testa com 1-lookahead
		$this->assertIdentical($Stack->nth(), 'CakePHP');
		// teste com 2-lookahead
		$this->assertIdentical($Stack->nth(2), 'Radig');
		
		$this->assertIdentical($Stack->pop(), 'CakePHP');
		$this->assertIdentical($Stack->pop(), 'Radig');
		$this->assertIdentical($Stack->pop(), null);
		
		unset($Stack);
	}
}