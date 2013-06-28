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
	
	public function testCalculate_HTTP_Range()
	{
		$start = 500;
		$end = 999;
		$_SERVER["HTTP_RANGE"] = "bytes=" . $start . "-" . $end . ",1024-2048";
		
		$extremes = DownloadManager::calculate_HTTP_Range();
		
		$this->assertTrue( ($extremes[0] == $start) && ($extremes[1] == $end));
	}
	
	public function testFeed()
	{
		$start = 0;
		$end = 26;
		$_SERVER["HTTP_RANGE"] = "bytes=" . $start . "-" . $end . ",1024-2048";
		
		$this->assertTrue(DownloadManager::Feed( __FILE__ ));
	}
	
	public function testFeedData()
	{
		$this->assertTrue(DownloadManager::FeedData( "\n\n\n-->>>Hey this is the test string for function FeedData.<<<--\n\n\n", "myfile.txt"));
	}
}

?>