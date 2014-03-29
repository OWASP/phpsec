<?php
namespace phpsec\framework;
abstract class Controller
{
	abstract function Start();
}
abstract class DefaultController extends Controller
{
	private $RestOfRequest=null;
	public $error = null;
	public $info = null;
	public function __construct($RestOfRequest)
	{
		$this->RestOfRequest=$RestOfRequest;
	}
	function Start()
	{
		return $this->Handle($this->RestOfRequest);
	}
	abstract function Handle($Request);
}