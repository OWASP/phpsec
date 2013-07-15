<?php

namespace phpsec;

/**
 * Classes for taint checking.
 */
abstract class Tainted
{
	static public $TaintChecking = true;
	
	protected $Tainted = true;
	
	static function Is(Tainted $Object)
	{
		return $Object->Tainted;
	}

	public function decontaminate()
	{
		$this->Tainted = false;
	}

	public function contaminate()
	{
		$this->Tainted = true;
	}
}

class TaintedString extends Tainted
{
	private $data;

	public function __construct($data=null)
	{
		$this->data=$data;
	}

	public function __toString()
	{
		if (Tainted::$TaintChecking and $this->Tainted)
			trigger_error("Trying to use tainted variable without decontamination.");
		return $this->data;
	}
}