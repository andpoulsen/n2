<?php
/*
 * wtcBB Community Software (Open Source Freeware Version)
 * Copyright (C) 2004-2007. All Rights Reserved. wtcBB Software Solutions.
 * http://www.wtcbb.com
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 * http://www.wtcbb.com/wtcbb-license-general-public-license 
 *
 * For support visit: http://forums.wtcbb.com
 *
 * Powered by wtcBB - http://www.wtcbb.com
 * Protected by ChargebackFile - http://www.chargebackfile.com
 * 
 */
// ************************************************** \\
## ************************************************** ##
## **************** wtcBB BB Code ******************* ##
## ************************************************** ##
## ************************************************** ##
// ************************************************** \\


// Do CONSTANTS
define('AREA', 'ADMIN-MAIN');
define('FILE_ACTION', 'Main');

// require files
require_once('./includes/global_admin.php');

new AdminHTML('header', 'Welcome to wtcBB 2 Admin Panel!', true);
new AdminHTML('tableBegin', 'wtcBB 2 Admin Panel', true, Array('form' => true, 'action' => 'admin.php?file=user&amp;do=search'));

new AdminHTML('tableRow', Array(
	'title' => 'Search Username:',
	'desc' => '',
	'type' => 'text',
	'name' => 'user[username]',
	'value' => $editinfo['username']
), true);

// Get some info...
// mysql version
$mysqlVersion = new Query('SELECT VERSION() AS version');
$mysqlVersion = $mysqlVersion->fetchArray();

// want our day...
$m = new WtcDate('m');
$d = new WtcDate('d');
$y = new WtcDate('Y');
$today = WtcDate::mktime(0, 0, 0, $m->getDate(), $d->getDate(), $y->getDate());

// new users in last 24 hours...
$newUsers = new Query('SELECT COUNT(*) AS total FROM ' . WTC_TP . 'userinfo WHERE joined >= "' . $today . '"');
$newUsers = $newUsers->fetchArray();

// unique users visited...
$uniqueUsers = new Query('SELECT COUNT(*) AS total FROM ' . WTC_TP . 'userinfo WHERE lastactivity >= "' . $today . '"');
$uniqueUsers = $uniqueUsers->fetchArray();

// attachment size
$attachSize = new Query('SELECT SUM(fileSize) AS total FROM ' . WTC_TP . 'attachments');
$attachSize = $attachSize->fetchArray();

// total users
$totalUsers = new Query('SELECT COUNT(*) AS total FROM ' . WTC_TP . 'userinfo');
$totalUsers = $totalUsers->fetchArray();

// total threads
$totalThreads = new Query('SELECT COUNT(*) AS total FROM ' . WTC_TP . 'threads');
$totalThreads = $totalThreads->fetchArray();

// new threads today
$newThreads = new Query('SELECT COUNT(*) AS total FROM ' . WTC_TP . 'threads WHERE thread_timeline >= "' . $today . '"');
$newThreads = $newThreads->fetchArray();

// total posts
$totalPosts = new Query('SELECT COUNT(*) AS total FROM ' . WTC_TP . 'posts');
$totalPosts = $totalPosts->fetchArray();

// new posts today
$newPosts = new Query('SELECT COUNT(*) AS total FROM ' . WTC_TP . 'posts WHERE posts_timeline >= "' . $today . '"');
$newPosts = $newPosts->fetchArray();

// total users online
$usersOnline = new Query('SELECT COUNT(*) AS total FROM ' . WTC_TP . 'sessions WHERE userid != 0');
$usersOnline = $usersOnline->fetchArray();

// total guests online
$guestsOnline = new Query('SELECT COUNT(*) AS total FROM ' . WTC_TP . 'sessions WHERE userid = 0');
$guestsOnline = $guestsOnline->fetchArray();


new AdminHTML('tableCells', '', true, Array(
	'cells' => Array(
		'<strong>PHP Version:</strong> ' . phpversion() => Array(),
		'<strong>MySQL Version:</strong> ' . $mysqlVersion['version'] => Array()
	)
));

new AdminHTML('tableCells', '', true, Array(
	'cells' => Array(
		'<strong>Registrations Today:</strong> ' . $newUsers['total'] => Array(),
		'<strong>Users Visited Today:</strong> ' . $uniqueUsers['total'] => Array()
	)
));

new AdminHTML('tableCells', '', true, Array(
	'cells' => Array(
		'<strong>Total Users:</strong> ' . $totalUsers['total'] => Array(),
		'<strong>Total Attachment File Size:</strong> ' . round($attachSize['total'] / 1000000, 2) . 'MB' => Array()
	)
));

new AdminHTML('tableCells', '', true, Array(
	'cells' => Array(
		'<strong>Total Threads:</strong> ' . $totalThreads['total'] => Array(),
		'<strong>New Threads Today:</strong> ' . $newThreads['total'] => Array()
	)
));

new AdminHTML('tableCells', '', true, Array(
	'cells' => Array(
		'<strong>Total Posts:</strong> ' . $totalPosts['total'] => Array(),
		'<strong>New Posts Today:</strong> ' . $newPosts['total'] => Array()
	)
));

new AdminHTML('tableCells', '', true, Array(
	'cells' => Array(
		'<strong>Total Online:</strong> ' . ($usersOnline['total'] + $guestsOnline['total']) . ' (' . $usersOnline['total'] . ' Members, ' . $guestsOnline['total'] . ' Guests)' => Array('colspan' => 2, 'class' => 'center')
	)
));

new AdminHTML('tableEnd', '', true, Array('form' => true));



new AdminHTML('tableBegin', 'Credits', true, Array('form' => false));

new AdminHTML('tableCells', '', true, Array(
	'cells' => Array(
		'<strong>Lead Developer:</strong>' => Array(),
		'<a href="http://www.wtcbb.com/forums/index.php?file=profile&u=1">Andrew Gallant</a>' => Array()
	)
));

new AdminHTML('tableCells', '', true, Array(
	'cells' => Array(
		'<strong>Graphic and Interface Designer:</strong>' => Array(),
		'<a href="http://www.wtcbb.com/forums/index.php?file=profile&u=740">Shelley Cunningham</a>' => Array()
	)
));

new AdminHTML('tableCells', '', true, Array(
	'cells' => Array(
		'<strong>Contributors:</strong>' => Array(),
		'<a href="http://www.wtcbb.com/forums/index.php?file=profile&u=71">Jackson Owens</a> and <a href="http://www.wtcbb.com/forums/index.php?file=profile&u=47">Justin Shreve</a>' => Array()
	)
));

new AdminHTML('tableEnd', '', true, Array('form' => false));
new AdminHTML('footer', '', true);


?>