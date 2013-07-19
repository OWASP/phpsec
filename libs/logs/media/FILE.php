<?php
namespace phpsec;

require_once 'template.php';

class FILE extends Template
{
	protected $fileConfig = null;
	protected $fp = null;
	
	public function __construct($config)
	{
		$this->fileConfig = $config;
		
		$this->fp = fopen($config['filename'], $config['mode']);
	}
	
	public function log($message)
	{
		$message = $this->changeFormatToTemplate($message);
		
		fwrite($this->fp, $message);
		fclose($this->fp);
	}
	
	public function changeFormatToTemplate( $message )
	{
		$message = $message . "\n";
		
		return $message;
	}
}

?>