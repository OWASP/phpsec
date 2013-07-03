<?php
namespace phpsec;
ob_start();


/**
 * Required Files.
 */
require_once "../../../libs/core/Download.php";
require_once "../../../libs/core/Time.php";


class DownloadManagerTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	 * Function to test if we are able to determine the correct file type.
	 */
	public function testMIME()
	{
		$type = DownloadManager::MIME("myTestFile.txt");
		
		$this->assertTrue($type == "text/plain");
	}
	
	
	/**
	 * To test if we can determine if a file has been modified since the last user visit.
	 */
	public function testIsModifiedSince()
	{
		$currentTime = Time::time();
		$modifiedSince = gmdate('D, d M Y H:i:s', $currentTime) . ' GMT';
		
		$_SERVER["HTTP_IF_MODIFIED_SINCE"] = $modifiedSince;
		
		$this->assertTrue(DownloadManager::IsModifiedSince( __FILE__ ));
	}
	
	
	/**
	 * To check if we can retrive the range specified by the user.
	 */
	public function testCalculate_HTTP_Range()
	{
		$start = 500;
		$end = 999;
		$_SERVER["HTTP_RANGE"] = "bytes=" . $start . "-" . $end . ",1024-2048";
		
		$extremes = DownloadManager::calculate_HTTP_Range();
		
		$this->assertTrue( ($extremes[0] == $start) && ($extremes[1] == $end));
	}
	
	
	/**
	 * To check if we can give the user a file in the range they requested. i.e. if they reqeuested only bytes 'm - n", then only that much bytes must be provided.
	 */
	public function testFeed()
	{
		$start = 0;
		$end = 26;
		$_SERVER["HTTP_RANGE"] = "bytes=" . $start . "-" . $end . ",1024-2048";
		
		$this->assertTrue(DownloadManager::Feed( __FILE__ ));
	}
	
	
	/**
	 * To check if we can send some data to the client.
	 */
	public function testFeedData()
	{
		$this->assertTrue(DownloadManager::FeedData( "\n\n\n-->>>Hey this is the test string for function FeedData.<<<--\n\n\n", "myfile.txt"));
	}
}

?>
