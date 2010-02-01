<?php

class DbDatetimeTest extends FunctionalTest {
	
	function setUp() {
		parent::setUp();
		$this->adapter = DB::getConn();
		$this->supportDbDatetime = method_exists($this->adapter, 'datetimeIntervalClause');
	}
	
	function testDbDatetimeFormat() {
		if($this->supportDbDatetime) {

			$result = DB::query('SELECT ' . $this->adapter->formattedDatetimeClause('1973-10-14 10:30:00', '%H:%i, %d/%m/%Y'))->value();
			$this->assertEquals($result, date('H:i, d/m/Y', strtotime('1973-10-14 10:30:00')), 'nice literal time');

			$result = DB::query('SELECT ' . $this->adapter->formattedDatetimeClause('now', '%d'))->value();
			$this->assertEquals($result, date('d'), 'todays day');

			$result = DB::query('SELECT ' . $this->adapter->formattedDatetimeClause('Created', '%U') . ' AS test FROM SiteTree WHERE URLSegment = \'home\'')->value();
			$this->assertEquals($result, date('U', strtotime(Dataobject::get_one('SiteTree',"URLSegment = 'home'")->Created)), 'SiteTree[home]->Created as timestamp');

		}
	}
	
	function testDbDatetimeInterval() {
		if($this->supportDbDatetime) {

			$result = DB::query('SELECT ' . $this->adapter->datetimeIntervalClause('1973-10-14 10:30:00', '+18 Years'))->value();
			$this->assertEquals($result, '1991-10-14 10:30:00', 'add 18 years');

			$result = DB::query('SELECT ' . $this->adapter->datetimeIntervalClause('now', '+1 Day'))->value();
			$this->assertEquals($result, date('Y-m-d H:i:s', strtotime('+1 Day')), 'tomorrow');

			$result = DB::query('SELECT ' . $this->adapter->datetimeIntervalClause('Created', '-15 Minutes') . ' AS test FROM SiteTree WHERE URLSegment = \'home\'')->value();
			$this->assertEquals($result, date('Y-m-d H:i:s', strtotime(Dataobject::get_one('SiteTree',"URLSegment = 'home'")->Created) - 900), '15 Minutes before creating SiteTree[home]');

		}
	}
	
	function testDbDatetimeDifference() {
		if($this->supportDbDatetime) {

			$result = DB::query('SELECT ' . $this->adapter->datetimeDifferenceClause('1974-10-14 10:30:00', '1973-10-14 10:30:00'))->value();
			$this->assertEquals($result/86400, 365, '1974 - 1973 = 365 * 86400 sec');

			$result = DB::query('SELECT ' . $this->adapter->datetimeDifferenceClause(date('Y-m-d H:i:s', strtotime('-15 seconds')), 'now'))->value();
			$this->assertEquals($result, -15, '15 seconds ago - now');

			$result = DB::query('SELECT ' . $this->adapter->datetimeDifferenceClause('now', $this->adapter->datetimeIntervalClause('now', '+45 Minutes')))->value();
			$this->assertEquals($result, -45 * 60, 'now - 45 minutes ahead');

			$result = DB::query('SELECT ' . $this->adapter->datetimeDifferenceClause('LastEdited', 'Created') . ' AS test FROM SiteTree WHERE URLSegment = \'home\'')->value();
			$lastedited = Dataobject::get_one('SiteTree',"URLSegment = 'home'")->LastEdited;
			$created = Dataobject::get_one('SiteTree',"URLSegment = 'home'")->Created;
			$this->assertEquals($result, strtotime($lastedited) - strtotime($created), 'age of HomePage record in seconds since unix epoc');

		}
	}
	
}
