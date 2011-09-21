<?php
class Stack
{
	/**
	 * Array utilizado como container
	 * da pilha.
	 * 
	 * @var array
	 */
	private $stack = array();
	
	/**
	 * Número de elementos na pilha.
	 * 
	 * @var int
	 */
	private $elements = 0;

	/**
	 * Inseri um elemento no topo da pilha.
	 * 
	 * @param mixed $val
	 */
	public function push($val)
	{
		array_push($this->stack, $val);
		$this->elements++;
	}

	/**
	 * Remove e retorna o elemento no topo da pilha.
	 * 
	 * @return mixed Elemento da pilha, caso ela não esteja vazia
	 * ou null, caso esteja.
	 */
	public function pop()
	{
		if ($this->elements > 0)
		{
			$this->elements--;
			return array_pop($this->stack);
		}
		
		return null;
	}

	/**
	 * Retorna o n-ésimo elemento na pilha, em relação ao topo.
	 * Pode ser utilizado com um lookahead.
	 * 
	 * @param int $n
	 * 
	 * @return mixed $this->stack[nth] if it exist and null otherwise
	 */
	public function nth($n = 1)
	{
		if($this->elements >= $n)
		{
			if(isset($this->stack[$this->elements - $n]))
			{
				return $this->stack[$this->elements - $n];
			}
		}
		
		return null;
	}
	
	/**
	 * Retorna a quantidade de elementos na pilha.
	 * 
	 * @return int $elements
	 */
	public function size()
	{
		return $this->elements;
	}
}