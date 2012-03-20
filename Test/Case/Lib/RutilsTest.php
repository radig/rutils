<?php
App::uses('Rutils', 'Rutil.Lib');

class RutilsTest extends CakeTestCase{

	public function testIsNullDate(){
		$Rutils = new Rutils();

		$result = $Rutils->isNullDate(NULL);
		$this->assertIdentical($result, true);

		$result = $Rutils->isNullDate('0000-00-00');
		$this->assertIdentical($result, true);

		$result = $Rutils->isNullDate('2012-03-19');
		$this->assertIdentical($result, false);
		
		
	}

	public function testOpenFile(){
		$Rutils = new Rutils();
		$path = sys_get_temp_dir();
		
		//teste 1
		$this->assertIdentical($Rutils->openFile('nah'), false);

		//teste 2
		$this->assertIdentical($Rutils->openFile(1), null);

		//teste 3
		$temp_file = tempnam($path, 'Tux');
		$fileTest = new SplFileInfo($temp_file);
		$file = $fileTest->openFile();
		$result = $Rutils->openFile($temp_file);
		$this->assertEqual($file, $result);
		unlink($temp_file);
	}
	
	 /**
     * @expectedException CakeException
     */
	public function testOpenFileException(){
		$Rutils = new Rutils();
		$path = sys_get_temp_dir();

		$temp_file = tempnam($path, 'Tux');
		$fileTest = new SplFileInfo($temp_file);
		$file = $fileTest->openFile();
		chmod($temp_file, 170);
		$result = $Rutils->openFile($temp_file);
		$this->assertEqual($file, $result);
		chmod($temp_file, 700);
		unlink($temp_file);
		
	}

	public function testGetPluginName(){
		$Rutils = new Rutils();

		$this->assertIdentical($Rutils->getPluginName(''), '');

		$result = $Rutils->getPluginName('PluginName.Rutil');
		$expected = 'PluginName';

		$this->assertIdentical($result, $expected);
		
	}

	public function testGetModelName(){
		$Rutils = new Rutils();

		$this->assertIdentical($Rutils->getModelName(''), '');

		$result = $Rutils->getModelName('PluginName.Rutil');
		$expected = 'Rutil';

		$this->assertIdentical($result, $expected);
		
	}
}