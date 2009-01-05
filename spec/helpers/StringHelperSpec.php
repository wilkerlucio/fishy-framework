<?php

require_once dirname(__FILE__) . "/../../helpers/StringHelper.php";

class DescribeStringHelper extends PHPSpec_Context
{
	public function itShouldAbleToTestIfAStringStartsWithAnotherString()
	{
		$haystack = "This is a complete string";
		
		$this->spec(Fishy_StringHelper::starts_with($haystack, "This"))->should->beTrue();
		$this->spec(Fishy_StringHelper::starts_with($haystack, "not"))->should->beFalse();
		$this->spec(Fishy_StringHelper::starts_with($haystack, "this"))->should->beFalse();
	}
	
	public function itShouldAbleToTestIfAStringEndsWithAnotherString()
	{
		$haystack = "This is a complete string";

		$this->spec(Fishy_StringHelper::ends_with($haystack, "string"))->should->beTrue();
		$this->spec(Fishy_StringHelper::ends_with($haystack, "not"))->should->beFalse();
		$this->spec(Fishy_StringHelper::ends_with($haystack, "String"))->should->beFalse();
	}
	
	public function itShouldCamelizeString()
	{
		$original = "this_is_normal_string";
		
		$this->spec(Fishy_StringHelper::camelize($original))->should->be("This_Is_Normal_String");
	}
	
	public function itShouldNormalizeStringToUrlSafeOne()
	{
		$this->spec(Fishy_StringHelper::normalize("Hi i'm to be normalized"))->should->be("hi-i'm-to-be-normalized");
	}
	
	public function itShouldApplyVariablesIntoSimpleTemplate()
	{
		$template = "Hi #name welcome to our site, its your #number visit to us";
		$data = array("name" => "Foo", "number" => 20);
		
		$this->spec(Fishy_StringHelper::simple_template($template, $data))->should->be("Hi Foo welcome to our site, its your 20 visit to us");
	}
	
	public function itShouldGenerateRandomString()
	{
		$this->spec(strlen(Fishy_StringHelper::random(30)))->should->be(30);
	}
	
	public function itShouldFillWithLeadingZeros()
	{
		$this->spec(Fishy_StringHelper::zero_fill(2, 3))->should->be("002");
	}
	
	public function itShouldAddHttpIntoUrlIfNescessary()
	{
		$this->spec(Fishy_StringHelper::force_http("www.google.com"))->should->be("http://www.google.com");
		$this->spec(Fishy_StringHelper::force_http("yahoo.com"))->should->be("http://yahoo.com");
		$this->spec(Fishy_StringHelper::force_http("http://www.google.com"))->should->be("http://www.google.com");
	}
}
