<?php
/**
 * Rutils - Radig Utilidades
 * 
 * Classe que reúne métodos utilitários
 *
 * PHP version 5
 *
 * Copyright 2011, Radig Soluções em TI. (http://www.radig.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2011, Radig Soluções em TI. (http://www.radig.com.br)
 * @link          http://www.radig.com.br
 * @package       radig
 * @subpackage    radig.utils.libs
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class Rutils
{
	/**
	 * Verifica se uma data passada é nula
	 * 
	 * São consideradas nulas datas com os valores:
	 * - ''
	 * - Iniciando por '0000-00-00'
	 * - NULL
	 * - false
	 * - 0
	 * 
	 * @param string $date a data que será avaliada
	 * 
	 * @return bool true se a data for nula, false caso contrário
	 */
	static function isNullDate($date)
	{
		// Empty || null
		if(empty($date))
		{
			return true;
		}
		
		// MySQL null date format
		if(is_int(strpos($date, '0000-00-00')))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Abre um arquivo especificado pelo parâmetro $filePath para leitura.
	 * Retorna o objeto que representa o arquivo em caso de sucesso, ou o 
	 * boolean false caso contrário.
	 * 
	 * @param string $filePath caminho do arquivo
	 * @return mixed SplFileObject em caso de sucesso | bool false caso contrário
	 */
	static function openFile($filePath)
	{
		if(is_string($filePath))
		{
			try
			{
				$fileInfo = new SplFileInfo($filePath);

				if($fileInfo->isFile())
				{
					$file = $fileInfo->openFile();

					return $file;
				}
				else
				{
					return false;
				}
			}
			catch( Exception $e )
			{
				trigger_error("Arquivo não pode ser importado\n" . $e->getMessage(), E_USER_WARNING);

				return false;
			}
		}
	}
	
	/**
	 * Retorna o nome do plugin dada uma string composta por Plugin.Model
	 * @param string $modelName
	 */
	static function getPluginName($modelName)
	{
		if(empty($modelName))
			return '';
	
		$exploded = explode('.', $modelName);
		
		return count($exploded) > 1 ? $exploded[0] : '';
	}
	
	/**
	* Retorna o nome do model dada uma string composta por Plugin.Model
	* @param string $modelName
	*/
	static function getModelName($modelName)
	{
		if(empty($modelName))
			return '';
	
		$exploded = explode('.', $modelName);
	
		return count($exploded) > 1 ? $exploded[1] : '';
	}
}