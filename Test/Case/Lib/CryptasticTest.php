 <?php
App::uses('Cryptastic', 'Rutil.Lib');

class CryptasticTest extends CakeTestCase{

	/*public function test(){


	}*/

	/*public function test(){

		
	}*/

	public function testGenerateIV()	{
		$iv = Cryptastic::generateIV();

		$result = Cryptastic::encrypt('mensagem secreta', 'minha senha', $iv);
		
		$this->assertTrue(sizeof($result) > 0);

		$result = Cryptastic::decrypt($result, 'minha senha');

		$this->assertEqual($result, 'mensagem secreta');

	}
}