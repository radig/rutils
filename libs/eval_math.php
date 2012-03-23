<?php
/**
 * EvalMath - PHP Class to safely evaluate math expressions
 * Copyright (C) 2005 Miles Kaufmann <http://www.twmagic.com/>
 *
 * Added use of bcmath lib to arbitrary precision operations,
 * support of localized values and strict precision mode based
 * on return string representations of float made by Radig TI
 * <http://radig.com.br>.
 *
 * Port to CakePHP style and tests cases included too.
 *
 * ===========================================================
 *
 * Use the EvalMath class when you want to evaluate mathematical expressions
 * from untrusted sources.  You can define your own variables and functions,
 * which are stored in the object.  Try it, it's fun!
 *
 * @method evalute($expr) Evaluates the expression and returns the result.
 * If an error occurs, trigger a warning and returns false.
 * If $expr is a function assignment, returns true on success.
 *
 * @method vars() Returns an associative array of all user-defined variables
 * and values.
 *
 * @method funcs() Returns an array of all user-defined functions.
 *
 * @property suppress_errors Set to true to turn off warnings when evaluating expressions
 *
 * @property last_error If the last evaluation failed, contains a string describing the error.
 * Useful when suppress_errors is on.
 *
 * @copyright Copyright 2005, Miles Kaufmann.
 * @license  Redistribution and use in source and binary forms, with or without
 *           modification, are permitted provided that the following conditions are met:
 *           1   Redistributions of source code must retain the above copyright
 *           notice, this list of conditions and the following disclaimer.
 *           2.  Redistributions in binary form must reproduce the above copyright
 *           notice, this list of conditions and the following disclaimer in the
 *           documentation and/or other materials provided with the distribution.
 *           3.  The name of the author may not be used to endorse or promote
 *           products derived from this software without specific prior written
 *           permission.
 *
 *           THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 *           IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *           WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *           DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT,
 *           INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 *           (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 *           SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 *           HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
 *           STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 *           ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *           POSSIBILITY OF SUCH DAMAGE.
 */
App::import('Lib', 'Rutils.Stack');
class EvalMath
{
	/**
	 * If errors will be triggereds or supresseds
	 *
	 * @var boolean
	 */
	public $suppress_errors = false;

	/**
	 * Last error message, if any
	 * @var string
	 */
	protected $last_error = null;

	/**
	 * BCMATH Precision value
	 * @var int
	 */
	protected $precision;

	/**
	 * User defineds variables and constants
	 * @var array
	 */
	protected $v = array();

	/**
	 * User defineds functions
	 * @var array
	 */
	protected $f = array();

	/**
	 * Built-in constants
	 *
	 * @var array
	 */
	protected $vb = array('e', 'pi');

	/**
	 * Built-in functions
	 *
	 * @var array
	 */
	protected $fb = array(
        'sin','sinh','arcsin','asin','arcsinh','asinh',
        'cos','cosh','arccos','acos','arccosh','acosh',
        'tan','tanh','arctan','atan','arctanh','atanh',
        'sqrt','abs','ln','log', 'log10');

	/**
	 * Hold current locale to restore before
	 * return values.
	 *
	 * @var int
	 */
	private $lcNumericOld;

	/**
	 * Initialize some parameters
	 *
	 * @param integer $precision Precision for operations with
	 * bc_math lib.
	 */
	public function __construct($precision = 3)
	{
		$this->lcNumericOld = setlocale(LC_NUMERIC, null);

		// make the variables a little more accurate
		$this->v['pi'] = pi();
		$this->v['e'] = exp(1);
		//set default precision of BC Math operations
		$this->precision = $precision;
	}

	/**
	 * Return last error message
	 *
	 * @return string $this->last_error
	 */
	public function getLastError()
	{
		return $this->last_error;
	}

	/**
	 * Restore initial values for EvalMath::v
	 * and EvalMath::f properties.
	 *
	 * @param string $type If 'vars', clear only v property,
	 * If 'funcs', clear only f property and if null or not
	 * defined, clear both properties.
	 *
	 * @return void
	 */
	public function clear($type = null)
	{
		if(strtolower($type) == 'vars' || $type === null)
			$this->v = array('pi' => pi(), 'e' => exp(1));

		if(strtolower($type) == 'funcs' || $type === null)
			$this->f = array();
	}

	/**
	* Get user defined variables
	*
	* @return array $vars
	*/
	public function vars()
	{
		$output = $this->v;

		unset($output['pi'], $output['e']);

		return $output;
	}

	/**
	 * Get user defined functions
	 *
	 * @return array $functions
	 */
	public function funcs()
	{
		$output = array();

		foreach ($this->f as $fnn => $dat)
		{
			$output[] = $fnn . '(' . implode(',', $dat['args']) . ')';
		}

		return $output;
	}

	/**
	 * Evaluate received expression with defined
	 * precision.
	 *
	 * @param string $expr
	 * @param int $precision
	 */
	public function evaluate($expr, $precision = null)
	{
		$this->last_error = null;
		$expr = trim($expr);

		$this->lcNumericOld = setlocale(LC_NUMERIC, null);
		setlocale(LC_NUMERIC, 'en_US');

		if($precision !== null)
		{
			$this->precision = $precision;
		}

		// strip semicolons at the end
		if (substr($expr, -1, 1) == ';')
		{
			$expr = substr($expr, 0, strlen($expr)-1);
		}

		// is it a variable assignment?
		if (preg_match('/^\s*([a-zA-Z]\w*)\s*=\s*(.+)$/', $expr, $matches))
		{
			// make sure we're not assigning to a constant
			if (in_array($matches[1], $this->vb))
			{
				return $this->error("Cannot assign to constant '$matches[1]'");
			}

			// get the result and make sure it's good
			if (($tmp = $this->pfx($this->nfx($matches[2]))) === false)
			{
				return $this->returnValue(false);
			}

			// if so, stick it in the variable array
			$this->v[$matches[1]] = $tmp;

			// and return the resulting value
			return $this->returnValue($this->v[$matches[1]]);
		}
		// is it a function assignment?
		elseif (preg_match('/^\s*([a-zA-Z]\w*)\s*\(\s*([a-zA-Z]\w*(?:\s*,\s*[a-zA-Z]\w*)*)\s*\)\s*=\s*(.+)$/', $expr, $matches))
		{
			// get the function name
			$fnn = $matches[1];

			// make sure it isn't built in
			if (in_array($matches[1], $this->fb))
			{
				return $this->error("Cannot redefine built-in function '$matches[1]()'");
			}

			// get the arguments
			$args = explode(",", preg_replace("/\s+/", "", $matches[2]));

			// see if it can be converted to postfix
			if (($stack = $this->nfx($matches[3])) === false)
			{
				return $this->returnValue(false);
			}

			// freeze the state of the non-argument variables
			for ($i = 0; $i < count($stack); $i++)
			{
				$token = $stack[$i];
				if (preg_match('/^[a-z]\w*$/', $token) and !in_array($token, $args))
				{
					if (array_key_exists($token, $this->v))
					{
						$stack[$i] = $this->v[$token];
					}
					else
					{
						return $this->error("Undefined variable '$token' in function definition");
					}
				}
			}

			$this->f[$fnn] = array('args' => $args, 'func' => $stack);

			return $this->returnValue(true);
		}
		else
		{
			$npr = $this->nfx($expr);

			if($npr !== false)
			{
				return $this->returnValue($this->pfx($npr));
			}

			return $this->returnValue(false);
		}
	}

	/**
	 * Convert infix to postfix notation
	 *
	 * @param string $expr
	 */
	protected function nfx($expr)
	{
		$index = 0;
		$stack = new Stack();
		$output = array(); // postfix form of expression, to be passed to pfx()
		$expr = trim($expr);

		$ops   = array('+', '-', '*', '/', '^', '_');
		$ops_r = array('+' => 0, '-'=> 0, '*' => 0, '/' => 0, '^' => 1); // right-associative operator?
		$ops_p = array('+' => 0, '-'=> 0, '*' => 1, '/' => 1, '_' => 1, '^' => 2); // operator precedence

		// we use this in syntax-checking the expression
		// and determining when a - is a negation
		$expecting_op = false;

		// make sure the characters are all good
		if (preg_match("/[^\w\s+\*\^\/\(\)\.,\-]/", $expr, $matches))
		{
			return $this->error("Illegal character '{$matches[0]}'");
		}

		// 1 Infinite Loop ;)
		while(1)
		{
			// get the first character at the current index
			$op = substr($expr, $index, 1);

			// find out if we're currently at the beginning of a number/variable/function/parenthesis/operand
			$ex = preg_match('/^([a-zA-Z]\w*\(?|\d+(?:\.\d*)?|\.\d+|\()/', substr($expr, $index), $match);

			// is it a negation instead of a minus?
			if ($op == '-' and !$expecting_op)
			{
				// put a negation on the stack
				$stack->push('_');
				$index++;
			}
			// we have to explicitly deny this, because it's legal on the stack
			elseif ($op == '_')
			{
				// but not in the input expression
				return $this->error("Illegal character '_'");
			}
			// are we putting an operator on the stack?
			elseif ((in_array($op, $ops) or $ex) and $expecting_op)
			{
				// are we expecting an operator but have a number/variable/function/opening parethesis?
				if ($ex)
				{
					// it's an implicit multiplication
					$op = '*';
					$index--;
				}

				// heart of the algorithm:
				while($stack->size() > 0 && ($o2 = $stack->nth()) && in_array($o2, $ops) && ($ops_r[$op] ? $ops_p[$op] < $ops_p[$o2] : $ops_p[$op] <= $ops_p[$o2]))
				{
					// pop stuff off the stack into the output
					$output[] = $stack->pop();
				}

				// many thanks: http://en.wikipedia.org/wiki/Reverse_Polish_notation#The_algorithm_in_detail
				// finally put OUR operator onto the stack
				$stack->push($op);
				$index++;
				$expecting_op = false;
			}
			// ready to close a parenthesis?
			elseif ($op == ')' and $expecting_op)
			{
				// pop off the stack back to the last (
				while (($o2 = $stack->pop()) != '(')
				{
					if (is_null($o2))
					{
						return $this->error("Unexpected ')'");
					}
					else
					{
						$output[] = $o2;
					}
				}

				// did we just close a function?
				if (preg_match("/^([a-zA-Z]\w*)\($/", $stack->nth(2), $matches))
				{
					// get the function name
					$fnn = $matches[1];
					// see how many arguments there were (cleverly stored on the stack, thank you)
					$arg_count = $stack->pop();
					// pop the function and push onto the output
					$output[] = $stack->pop();
					// check the argument count
					if (in_array($fnn, $this->fb))
					{
						if($arg_count > 1)
						{
							return $this->error("Too many arguments ($arg_count given, 1 expected)");
						}

					}
					elseif (array_key_exists($fnn, $this->f))
					{
						if ($arg_count != count($this->f[$fnn]['args']))
						{
							return $this->error("Wrong number of arguments ($arg_count given, " . count($this->f[$fnn]['args']) . " expected)");
						}
					}
					// did we somehow push a non-function on the stack? this should never happen
					else
					{
						return $this->error("Internal error");
					}
				}

				$index++;
			}
			// did we just finish a function argument?
			elseif ($op == ',' and $expecting_op)
			{
				while (($o2 = $stack->pop()) != '(')
				{
					if (is_null($o2))
					{
						// oops, never had a (
						return $this->error("Unexpected ','");
					}
					else
					{
						// pop the argument expression stuff and push onto the output
						$output[] = $o2;
					}
				}
				// make sure there was a function
				if (!preg_match("/^([a-zA-Z]\w*)\($/", $stack->nth(2), $matches))
				{
					return $this->error("Unexpected ','");
				}

				// increment the argument count
				$stack->push($stack->pop()+1);

				// put the ( back on, we'll need to pop back to it again
				$stack->push('(');
				$index++;
				$expecting_op = false;
			}
			elseif ($op == '(' and !$expecting_op)
			{
				// that was easy
				$stack->push('(');
				$index++;
				$allow_neg = true;
			}
			// do we now have a function/variable/number?
			elseif ($ex and !$expecting_op)
			{
				$expecting_op = true;
				$val = $match[1];

				// may be func, or variable w/ implicit multiplication against parentheses...
				if (preg_match("/^([a-zA-Z]\w*)\($/", $val, $matches))
				{
					// it's a func
					if (in_array($matches[1], $this->fb) or array_key_exists($matches[1], $this->f))
					{
						$stack->push($val);
						$stack->push(1);
						$stack->push('(');
						$expecting_op = false;
					}
					// it's a var w/ implicit multiplication
					else
					{
						$val = $matches[1];
						$output[] = $val;
					}
				}
				// it's a plain old var or num
				else
				{
					$output[] = $val;
				}

				$index += strlen($val);
			}
			// miscellaneous error checking
			elseif ($op == ')')
			{
				return $this->error("Unexpected ')'");
			}
			elseif (in_array($op, $ops) and !$expecting_op)
			{
				return $this->error("Unexpected operator '$op'");
			}
			// I don't even want to know what you did to get here
			else
			{
				return $this->error("An unexpected error occured");
			}

			if ($index == strlen($expr))
			{
				// did we end with an operator? bad.
				if (in_array($op, $ops))
				{
					return $this->error("Operator '$op' lacks operand");
				}
				else
				{
					break;
				}
			}

			// step the index past whitespace (pretty much turns whitespace
			while (substr($expr, $index, 1) == ' ')
			{
				// into implicit multiplication if no operator is there)
				$index++;
			}
		}

		// pop everything off the stack and push onto output
		while (!is_null($op = $stack->pop()))
		{
			// if there are (s on the stack, ()s were unbalanced
			if ($op == '(')
			{
				return $this->error("Expecting ')'");
			}

			$output[] = $op;
		}

		return $output;
	}

	/**
	 * Evaluate postfix notation
	 *
	 * @param array $tokens
	 * @param array $vars
	 *
	 * @return mixed boolean *false* in failures and *numeric* value in success
	 */
	protected function pfx($tokens, $vars = array())
	{
		bcscale($this->precision);

		if ($tokens === false)
			return false;

		$stack = new Stack();

		foreach ($tokens as $token)
		{
			// if the token is a binary operator, pop two values off the stack, do the operation, and push the result back on
			if (in_array($token, array('+', '-', '*', '/', '^')))
			{
				if (is_null($op2 = $stack->pop()) || is_null($op1 = $stack->pop()))
					return $this->error("Internal error");

				switch ($token)
				{
					case '+':
						$stack->push(bcadd($op1,$op2));
						break;
					case '-':
						$stack->push(bcsub($op1,$op2));
						break;
					case '*':
						$stack->push(bcmul($op1,$op2));
						break;
					case '/':
						if ($op2 == 0)
							return $this->error("Division by zero");

						$stack->push(bcdiv($op1,$op2));
						break;
					case '^':
						$stack->push(bcpow($op1, $op2));
						break;
				}
				// if the token is a unary operator, pop one value off the stack, do the operation, and push it back on
			}
			elseif ($token == "_")
			{
				// if the token is a function, pop arguments off the stack, hand them to the function, and push the result back on
				$stack->push(-1*$stack->pop());
			}
			elseif (preg_match("/^([a-z]\w*)\($/", $token, $matches))
			{
				// it's a function!
				$fnn = $matches[1];

				// built-in function:
				if (in_array($fnn, $this->fb))
				{
					if (is_null($op1 = $stack->pop()))
						return $this->error("Internal error");

					// for the 'arc' trig synonyms
					$fnn = preg_replace("/^arc/", "a", $fnn);

					if ($fnn == 'ln')
						$fnn = 'log';

					if ($fnn == 'sqrt')
						$fnn = 'bcsqrt';

					$val = $this->strictPrecision(call_user_func($fnn, $op1));

					$stack->push($val);
				}
				// user function
				elseif (array_key_exists($fnn, $this->f))
				{
					// get args
					$args = array();

					for ($i = count($this->f[$fnn]['args'])-1; $i >= 0; $i--)
					{
						if (is_null($args[$this->f[$fnn]['args'][$i]] = $stack->pop()))
						{
							return $this->error("Internal error");
						}
					}

					$stack->push($this->pfx($this->f[$fnn]['func'], $args)); // yay... recursion!!!!
				}
			}
			// if the token is a number or variable, push it on the stack
			else
			{
				if (is_numeric($token))
				{
					$stack->push($this->strictPrecision($token));
				}
				elseif (array_key_exists($token, $this->v))
				{
					$stack->push($this->strictPrecision($this->v[$token]));
				}
				elseif (array_key_exists($token, $vars))
				{
					$stack->push($this->strictPrecision($vars[$token]));
				}
				else
				{
					return $this->error("Undefined variable '$token'");
				}
			}
		}

		// when we're out of tokens, the stack should have a single element, the final result
		if ($stack->size() != 1)
			return $this->error("Internal error");

		return $stack->pop();
	}

	/**
	 * Enable strict length of string output, based on
	 * precision class attribute
	 *
	 * @param string $value Numeric value
	 *
	 * @return string $value Length normalized value
	 */
	private function strictPrecision($value)
	{
		if(is_float($value))
			$value = round($value, $this->precision);

		if(empty($value))
			$value = '0';

		if(($pos = strpos($value, '.')) !== false)
		{
			$append = str_pad(substr($value, $pos), $this->precision + 1, '0');
			$value = substr($value, 0, $pos) . $append;
		}
		else if($this->precision > 0)
		{
			$append = str_repeat('0', $this->precision);
			$value = $value . '.' . $append;
		}

		return $value;
	}

	/**
	 * Apply strict precision on value before
	 * return and restore numeric locale too.
	 *
	 * @param mixed $value Result expression, can be boolean, integers,
	 * floats or strings.
	 *
	 * @return mixed A numeric converted to string or boolean
	 */
	private function returnValue($value)
	{
		if(!is_bool($value))
			$value = $this->strictPrecision($value);

		$this->restoreLocale();

		return $value;
	}

	/**
	 * Trigger a warning error if needed
	 *
	 * @param string $msg Message error
	 *
	 * @return bool false
	 */
	private function error($msg)
	{
		$this->restoreLocale();
		$this->last_error = $msg;

		if (!$this->suppress_errors)
			trigger_error($msg, E_USER_WARNING);

		return false;
	}

	/**
	 * Restore numeric locale.
	 *
	 * @return void
	 */
	private function restoreLocale()
	{
		setlocale(LC_NUMERIC, $this->lcNumericOld);
	}
}