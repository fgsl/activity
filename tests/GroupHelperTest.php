<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Activity\Tests;

use OCA\Activity\DataHelper;
use OCA\Activity\Extension\LegacyParser;
use OCA\Activity\GroupHelper;
use OCA\Activity\GroupHelperDisabled;
use OCA\Activity\Parameter\Collection;
use OCP\Activity\IEvent;
use OCP\Activity\IManager;
use OCP\IL10N;

class GroupHelperTest extends TestCase {
	/** @var IManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $activityManager;
	/** @var DataHelper|\PHPUnit_Framework_MockObject_MockObject */
	protected $dataHelper;
	/** @var LegacyParser|\PHPUnit_Framework_MockObject_MockObject */
	protected $legacyParser;
	/** @var IL10N|\PHPUnit_Framework_MockObject_MockObject */
	protected $l;

	protected function setUp() {
		parent::setUp();

		$this->l = $this->createMock(IL10N::class);
		$this->activityManager = $this->createMock(IManager::class);
		$this->dataHelper = $this->createMock(DataHelper::class);
		$this->legacyParser = $this->createMock(LegacyParser::class);
	}

	/**
	 * @param array $methods
	 * @param bool $grouping
	 * @return GroupHelper|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getHelper(array $methods = [], $grouping = false) {
		if (empty($methods)) {
			if ($grouping) {
				return new GroupHelper(
					$this->l,
					$this->activityManager,
					$this->dataHelper,
					$this->legacyParser
				);
			} else {
				return new GroupHelperDisabled(
					$this->l,
					$this->activityManager,
					$this->dataHelper,
					$this->legacyParser
				);
			}
		} else {
			return $this->getMockBuilder($grouping ? 'OCA\Activity\GroupHelper' : 'OCA\Activity\GroupHelperDisabled')
				->setConstructorArgs([
					$this->activityManager,
					$this->dataHelper
				])
				->setMethods($methods)
				->getMock();
		}
	}

	public function testSetUser() {
		$helper = $this->getHelper();

		$this->dataHelper->expects($this->once())
			->method('setUser')
			->with('foobar');

		$helper->setUser('foobar');
	}

	public function testSetL10n() {
		$helper = $this->getHelper();

		$l = \OC::$server->getL10NFactory()->get('activity', 'de');
		$this->dataHelper->expects($this->once())
			->method('setL10n')
			->with($l);

		$helper->setL10n($l);
	}

	public function dataGetEventFromArray() {
		return [
			[
				[
					'app' => 'app1',
					'type' => 'type1',
					'affecteduser' => 'affecteduser1',
					'user' => 'user1',
					'timestamp' => 42,
					'subject' => 'subject1',
					'subjectparams' => json_encode(['par1']),
					'message' => 'message1',
					'messageparams' => json_encode(['par2']),
					'object_type' => 'object_type1',
					'object_id' => 123,
					'file' => 'file1',
					'link' => 'link1',
				],
			],
			[
				[
					'app' => 'app2',
					'type' => 'type2',
					'affecteduser' => 'affecteduser2',
					'user' => 'user2',
					'timestamp' => 23,
					'subject' => 'subject2',
					'subjectparams' => json_encode(['par2']),
					'message' => 'message2',
					'messageparams' => json_encode(['par3']),
					'object_type' => 'object_type2',
					'object_id' => 456,
					'file' => 'file2',
					'link' => 'link2',
				],
			],
		];
	}

	/**
	 * @dataProvider dataGetEventFromArray
	 * @param array $activity
	 */
	public function testGetEventFromArray(array $activity) {
		$event = $this->createMock(IEvent::class);
		$event->expects($this->once())
			->method('setApp')
			->with($activity['app'])
			->willReturnSelf();
		$event->expects($this->once())
			->method('setType')
			->with($activity['type'])
			->willReturnSelf();
		$event->expects($this->once())
			->method('setAffectedUser')
			->with($activity['affecteduser'])
			->willReturnSelf();
		$event->expects($this->once())
			->method('setAuthor')
			->with($activity['user'])
			->willReturnSelf();
		$event->expects($this->once())
			->method('setTimestamp')
			->with($activity['timestamp'])
			->willReturnSelf();
		$event->expects($this->once())
			->method('setSubject')
			->with($activity['subject'], json_decode($activity['subjectparams'], true))
			->willReturnSelf();
		$event->expects($this->once())
			->method('setMessage')
			->with($activity['message'], json_decode($activity['messageparams'], true))
			->willReturnSelf();
		$event->expects($this->once())
			->method('setObject')
			->with($activity['object_type'], $activity['object_id'], $activity['file'])
			->willReturnSelf();
		$event->expects($this->once())
			->method('setLink')
			->with($activity['link'])
			->willReturnSelf();

		$this->activityManager->expects($this->once())
			->method('generateEvent')
			->willReturn($event);

		$helper = $this->getHelper();
		$instance = $this->invokePrivate($helper, 'arrayToEvent', [$activity]);
		$this->assertSame($event, $instance);
	}
}
