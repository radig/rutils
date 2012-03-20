<?php
App::uses('Text', 'Rutil.Lib');

class TextTest extends CakeTestCase {
	public function testRemoveSpecials() {
		$this->assertEqual('The quick brown fox jumps over the lazy dog', Text::removeSpecials('The quick brown fox jumps over the lazy dog'));
		$this->assertEqual('Nao e sempre possivel achar uma expressao acentuada. A nao ser que ce apele com a gramatica.', Text::removeSpecials('Não é sempre possível achar uma expressão acentuada. A não ser que çe apele com a gramática.'));
	}
}