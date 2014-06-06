<?php

/**
* ownCloud - Activity App
*
* @author Frank Karlitschek
* @copyright 2013 Frank Karlitschek frank@owncloud.org
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

// some housekeeping
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('activity');

// Read the next 30 items for the endless scrolling
$count = 30;
$page = \OCA\Activity\Data::getPageFromParam() - 1;
$filter = \OCA\Activity\Data::getFilterFromParam();

$activity = \OCA\Activity\Data::read($page * $count, $count, $filter);

// show the next 30 entries
$tmpl = new \OCP\Template('activity', 'activities.part', '');
$tmpl->assign('activity', $activity);
$tmpl->printPage();