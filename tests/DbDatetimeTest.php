<?php

class DbDatetimeTest extends FunctionalTest {
	
	function setUp() {
		parent::setUp();
		$this->adapter = DB::getConn();
		$this->supportDbDatetime = method_exists($this->adapter, 'datetimeIntervalClause');
	}
	
	function testIfWebserverInSyncWithDbServer() {
		if($this->supportDbDatetime) {
			$query = 'SELECT ' . $this->adapter->formattedDatetimeClause('now', '%Y-%m-%d %H:%i:%s');
			$result = DB::query($query)->value();
			$this->assertEquals($result, date('Y-m-d H:i:s'));
		}
	}

	function testCorrectNow() {
		if($this->supportDbDatetime) {
			$query = 'SELECT ' . $this->adapter->formattedDatetimeClause('now', '%U');
			$result = DB::query($query)->value();
			$this->assertEquals($result, time());
		}
	}

	function testDbDatetimeFormat() {
		if($this->supportDbDatetime) {

			$query = 'SELECT ' . $this->adapter->formattedDatetimeClause('1973-10-14 10:30:00', '%H:%i, %d/%m/%Y');
			$result = DB::query($query)->value();
			$this->assertEquals($result, date('H:i, d/m/Y', strtotime('1973-10-14 10:30:00')), 'nice literal time');

			$query = 'SELECT ' . $this->adapter->formattedDatetimeClause('now', '%d');
			$result = DB::query($query)->value();
			$this->assertEquals($result, date('d'), 'todays day');

			$query = 'SELECT ' . $this->adapter->formattedDatetimeClause('"Created"', '%U') . ' AS test FROM "SiteTree" WHERE "URLSegment" = \'home\'';
			$result = DB::query($query)->value();
			$this->assertEquals($result, date('U', strtotime(Dataobject::get_one('SiteTree',"\"URLSegment\" = 'home'")->Created)), 'SiteTree[home]->Created as timestamp');

		}
	}
	
	function testDbDatetimeInterval() {
		if($this->supportDbDatetime) {

			$query = 'SELECT ' . $this->adapter->datetimeIntervalClause('1973-10-14 10:30:00', '+18 Years');
			$result = DB::query($query)->value();
			$this->assertEquals($result, '1991-10-14 10:30:00', 'add 18 years');

			$query = 'SELECT ' . $this->adapter->datetimeIntervalClause('now', '+1 Day');
			$result = DB::query($query)->value();
			$this->assertEquals($result, date('Y-m-d H:i:s', strtotime('+1 Day')), 'tomorrow');

			$query = 'SELECT ' . $this->adapter->datetimeIntervalClause('"Created"', '-15 Minutes') . ' AS "test" FROM "SiteTree" WHERE "URLSegment" = \'home\'';
			$result = DB::query($query)->value();
			$this->assertEquals($result, date('Y-m-d H:i:s', strtotime(Dataobject::get_one('SiteTree',"\"URLSegment\" = 'home'")->Created) - 900), '15 Minutes before creating SiteTree[home]');

		}
	}
	
	function testDbDatetimeDifference() {
		if($this->supportDbDatetime) {

			$query = 'SELECT ' . $this->adapter->datetimeDifferenceClause('1974-10-14 10:30:00', '1973-10-14 10:30:00');
			$result = DB::query($query)->value();
			$this->assertEquals($result/86400, 365, '1974 - 1973 = 365 * 86400 sec');

			$query = 'SELECT ' . $this->adapter->datetimeDifferenceClause(date('Y-m-d H:i:s', strtotime('-15 seconds')), 'now');
			$result = DB::query($query)->value();
			$this->assertEquals($result, -15, '15 seconds ago - now');

			$query = 'SELECT ' . $this->adapter->datetimeDifferenceClause('now', $this->adapter->datetimeIntervalClause('now', '+45 Minutes'));
			$result = DB::query($query)->value();
			$this->assertEquals($result, -45 * 60, 'now - 45 minutes ahead');

			$query = 'SELECT ' . $this->adapter->datetimeDifferenceClause('"LastEdited"', '"Created"') . ' AS "test" FROM "SiteTree" WHERE "URLSegment" = \'home\'';
			$result = DB::query($query)->value();
			$lastedited = Dataobject::get_one('SiteTree',"\"URLSegment\" = 'home'")->LastEdited;
			$created = Dataobject::get_one('SiteTree',"\"URLSegment\" = 'home'")->Created;
			$this->assertEquals($result, strtotime($lastedited) - strtotime($created), 'age of HomePage record in seconds since unix epoc');

		}
	}
	
}
