<?php
namespace phpsec;
ob_start();

require_once "../../../libs/core/Download.class.php";
require_once "../../../libs/core/Time.class.php";

class DownloadManagerTest extends \PHPUnit_Framework_TestCase
{
	public function testMIME()
	{
		$type = DownloadManager::MIME("myTestFile.txt");
		
		$this->assertTrue($type == "text/plain");
	}
	
	public function testIsModifiedSince()
	{
		$currentTime = Time::time();
		$modifiedSince = gmdate('D, d M Y H:i:s', $currentTime) . ' GMT';
		
		$_SERVER["HTTP_IF_MODIFIED_SINCE"] = $modifiedSince;
		
		$this->assertTrue(DownloadManager::IsModifiedSince( __FILE__ ));
	}
}

?>