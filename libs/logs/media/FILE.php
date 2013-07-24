<?php
namespace phpsec;

require_once 'template.php';
require_once (__DIR__ . '/../../core/time.php');

class FILE extends Template
{
	protected $fileConfig = null;
	protected $fp = null;
	
	public function __construct($config)
	{
		$this->fileConfig = $config;
		
		$this->fp = fopen($config['filename'], $config['mode']);
	}
	
	public function log($args)
	{
		$message = $this->changeTemplate($args);
		
		fwrite($this->fp, $message);
		fclose($this->fp);
	}
	
	protected function changeTemplate($args)
	{
		$this->setDefaults();
		
		$i = 0;
		$message = "";
		
		foreach(  Template::$template as $value)
		{
			if (isset($args[$i]) && $args[$i] !== "")
			{
				$value = $args[$i];
			}
			
			$message = $message . "[" . $value . "]" . "\t\t";
			$i = $i+1;
		}
		
		$message = $message . "\n";
		
		return $message;
	}
}

?>