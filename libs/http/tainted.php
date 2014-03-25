<?php
namespace phpsec;



/**
 * Abstract class for all tainted string
 */
abstract class Tainted
{



	/**
	 * Enables/Disables the taint checking function
	 * @var boolean		True enables the taint checking function. False disables it
	 */
	public static $TaintChecking = true;



	/**
	 * To indicate that the string is tainted
	 * @var boolean		True means the string is tainted. False otherwise
	 */
	protected $Tainted = true;



	/**
	 * To tell if the given Tainted Object is tainted or not
	 * @param \phpsec\Tainted $Object	The Tainted class object
	 * @return boolean			Returns true if the string is tainted. False otherwise
	 */
	public static function Is(Tainted $Object)
	{
		return $Object->Tainted;
	}



	/**
	 * To decontaminate a "Tainted" object
	 */
	public function decontaminate()
	{
		$this->Tainted = false;
	}



	/**
	 * To taint a string i.e. to contaminate it.
	 */
	public function contaminate()
	{
		$this->Tainted = true;
	}
}



/**
 * class for one tainted string
 */
class TaintedString extends Tainted
{



	/**
	 * String that is tainted
	 * @var string
	 */
	private $data;



	/**
	 * Constructor of the class.
	 * @param string $data		The string that is to be tainted
	 */
	public function __construct($data=null)
	{
		$this->data=$data;
	}



	/**
	 * Function to trigger error when trying to use a string that is tainted
	 * @return string	The string that is tainted
	 */
	public function __toString()
	{
		if (Tainted::$TaintChecking and $this->Tainted)	//If the string is tainted, then trigger the error
			trigger_error("Trying to use tainted variable without decontamination.");
		return $this->data;
	}
}