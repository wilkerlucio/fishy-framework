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

require_once dirname(__FILE__) . "/../../helpers/ArrayHelper.php";

class DescribeArrayHelper extends PHPSpec_Context
{
	private $range_data;
	
	public function beforeAll()
	{
		$this->range_data = range(1, 30);
	}
	
	public function itShouldReturnIndexOfExistingItemFromAnArrayWithBinarySearch()
	{
		$this->spec(Fishy_ArrayHelper::binary_search($this->range_data, 3))->should->be(2);
		$this->spec(Fishy_ArrayHelper::binary_search($this->range_data, 20))->should->be(19);
		$this->spec(Fishy_ArrayHelper::binary_search($this->range_data, 14))->should->be(13);
		$this->spec(Fishy_ArrayHelper::binary_search($this->range_data, 19))->should->be(18);
		$this->spec(Fishy_ArrayHelper::binary_search($this->range_data, 6))->should->be(5);
	}
	
	public function itShouldReturnNegativeNumberIfTheItemDontExistsIntoArray()
	{	
		$this->spec(Fishy_ArrayHelper::binary_search($this->range_data, 50))->should->be(-1);
	}
	
	public function itShouldConcatItemsIntoArrayWithArrayPush()
	{
		$a = array(1, 2, 3);
		$b = array(4, 5);
		
		Fishy_ArrayHelper::array_push($a, $b);
		
		$this->spec($a)->should->be(array(1, 2, 3, 4, 5));
	}
	
	public function itShouldSplitArrays()
	{
		$splited = Fishy_ArrayHelper::array_split($this->range_data, 2);
		
		$this->spec(count($splited))->should->be(2);
		$this->spec(count($splited[0]))->should->be(15);
		$this->spec(count($splited[1]))->should->be(15);
		
		$splited = Fishy_ArrayHelper::array_split($this->range_data, 4);
		
		$this->spec(count($splited))->should->be(4);
		$this->spec(count($splited[0]))->should->be(8);
		$this->spec(count($splited[1]))->should->be(8);
		$this->spec(count($splited[2]))->should->be(7);
		$this->spec(count($splited[3]))->should->be(7);
	}
	
	public function itShouldFindIndexOfElement()
	{
		$haystack = array("a", "b", "c", "d");
		
		$this->spec(Fishy_ArrayHelper::index_of($haystack, "a"))->should->be(0);
		$this->spec(Fishy_ArrayHelper::index_of($haystack, "b"))->should->be(1);
		$this->spec(Fishy_ArrayHelper::index_of($haystack, "c"))->should->be(2);
		$this->spec(Fishy_ArrayHelper::index_of($haystack, "d"))->should->be(3);
		$this->spec(Fishy_ArrayHelper::index_of($haystack, "e"))->should->be(-1);
	}
}
