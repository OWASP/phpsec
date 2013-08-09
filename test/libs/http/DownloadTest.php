<?php
namespace phpsec;

/**
 * Required Files.
 */
require_once __DIR__ . "/../../../libs/http/download.php";
require_once __DIR__ . "/../../../libs/core/time.php";


class DownloadManagerTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Function to test if we are able to determine the correct file type.
	 */
	public function testMIME()
	{
		$type = DownloadManager::MIME("myTestFile.bmp");

		$this->assertSame('image/bmp', $type);
	}


	/**
	 * To check if we can give the user a file in the range they requested. i.e. if they reqeuested only bytes 'm - n", then only that much bytes must be provided.
	 */
	public function testDownload()
	{
		$this->markTestSkipped('Cannot test this in command line context');
		$start = 0;
		$end = 26;
		$_SERVER["HTTP_RANGE"] = "bytes=" . $start . "-" . $end . ",1024-2048";

		$this->assertTrue(DownloadManager::download(__FILE__));
	}


	/**
	 * To check if we can send some data to the client.
	 */
	public function testserveData()
	{
		$this->markTestSkipped('Cannot test this in command line context');
		$this->assertTrue(DownloadManager::serveData("\n\n\n-->>>Hey this is the test string for function FeedData.<<<--\n\n\n", "myfile.txt"));
	}
}
