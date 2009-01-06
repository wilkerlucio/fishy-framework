<?php

require dirname(__FILE__) . "/../../libraries/Zip.php";
require dirname(__FILE__) . "/../../helpers/DirectoryHelper.php";

class DescribeZip extends PHPSpec_Context
{
	private $zip_path;
	private $expected_data;
	
	public function before()
	{
		$this->expected_data = array("first.txt", "second.txt", "third.txt", "deep/more.txt", "deep/image.jpg");
		$this->zip_path = dirname(__FILE__) . '/test.zip';
	}
	
	public function itShouldDispatchAnExceptionWhenTryToOpenAnInexistentOrUnaccessibleFile()
	{
		try {
			$zip = new Fishy_Zip('non_existent_file.zip');
			
			$this->spec(true)->should->beFalse();
		} catch(Fishy_Zip_Exception $e) {
			$this->spec(true)->should->beTrue();
		}
	}
	
	public function itShouldRetrieveAListOfContainingFiles()
	{
		$zip = new Fishy_Zip($this->zip_path);
		$list = $zip->list_of_files();
		
		foreach ($this->expected_data as $item) {
			$this->spec(in_array($item, $list))->should->beTrue();
		}
		
		$zip->close();
	}
	
	public function itShouldUnzipASingleFile()
	{
		$file = "first.txt";
		
		$zip = new Fishy_Zip($this->zip_path);
		$zip->extract($file);
		
		$this->spec(file_exists($file))->should->beTrue();
		
		unlink($file);
	}
	
	public function itShouldUnzipAllFiles()
	{
		$tmp_dir = dirname(__FILE__) . "/tmp";
		
		Fishy_DirectoryHelper::mkdir($tmp_dir);
		
		$zip = new Fishy_Zip($this->zip_path);
		$zip->extract_all($tmp_dir);
		
		foreach ($this->expected_data as $item) {
			$this->spec(file_exists($tmp_dir . '/' . $item))->should->beTrue();
		}
		
		Fishy_DirectoryHelper::rmdir($tmp_dir);
	}
}
