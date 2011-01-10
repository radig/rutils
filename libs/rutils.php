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
}