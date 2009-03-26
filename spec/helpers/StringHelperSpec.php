<?php

/*
 * Copyright 2009 Wilker Lucio <wilkerlucio@gmail.com>
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License. 
 */

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
		$this->spec(Fishy_StringHelper::normalize("Hi i'm to be normalized"))->should->be("hi-im-to-be-normalized");
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
	
	public function itShouldTruncateAString()
	{
		$string = "My string is getting to long for the text where I want to use it.";
		$truncated = Fishy_StringHelper::truncate($string, 30);
		
		$this->spec($truncated)->should->be("My string is getting to lon...");
	}
	
	public function itShouldTruncateAStringPreservingWords()
	{
		$string = "My string is getting to long for the text where I want to use it.";
		$truncated = Fishy_StringHelper::truncate($string, 30, true);
		
		$this->spec($truncated)->should->be("My string is getting to...");
	}
	
	public function itShouldTruncateAStringPreservingWordsWithLineBreak()
	{
		$string = "My string is getting to\nlong for the text where I want to use it.";
		$truncated = Fishy_StringHelper::truncate($string, 30, true);
		
		$this->spec($truncated)->should->be("My string is getting to...");
	}
	
	public function itShouldTruncateAStringPreservingWordsWithWindowsLineBreak()
	{
		$string = "My string is getting to\r\nlong for the text where I want to use it.";
		$truncated = Fishy_StringHelper::truncate($string, 30, true);
		
		$this->spec($truncated)->should->be("My string is getting to...");
	}
	
	public function itShouldTruncateAStringPreservingWordsWithTabulation()
	{
		$string = "My string is getting to\tlong for the text where I want to use it.";
		$truncated = Fishy_StringHelper::truncate($string, 30, true);
		
		$this->spec($truncated)->should->be("My string is getting to...");
	}
	
	public function itShouldNotTruncateTheStringIfTheStringInLesserThanGivenLength()
	{
		$string = "Im little";
		$truncated = Fishy_StringHelper::truncate($string, 30);
		
		$this->spec($truncated)->should->be("Im little");
	}
	
	public function itShouldAcceptACustomPaddingStringWhenTruncating()
	{
		$string = "My string is getting to long for the text where I want to use it.";
		$truncated = Fishy_StringHelper::truncate($string, 30, false, "--");
		
		$this->spec($truncated)->should->be("My string is getting to long--");
	}
}
