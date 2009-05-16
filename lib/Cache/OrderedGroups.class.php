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
## ************************************************** ##
## *********** ORDERED GROUPS CACHE CLASS *********** ##
## ************************************************** ##
## ************************************************** ##
// ************************************************** \\


/**
 * This cache is designed specifically for groups
 * that can contain other groups.
 */

class OrderedGroups extends Cache {	
	// Constructor
	public function __construct() {
		global $_CACHE;
		
		$this->cacheType = 'orderedGroups';
		$this->cacheArray = 1;
		$this->formCache();
		
		// update cache...
		if(!isset($_CACHE['orderedGroups'])) {
			$this->insert();
		}
		
		else {
			$this->update();
		}
	}
	

	// Protected Methods
	protected function formCache() {
		global $query, $wtcDB, $generalGroups;
		
		$getGroups = new Query($query['groups']['get_all']);
		$this->cacheInfo = Array();
		$groupArr = Array();
		
		while($group = $wtcDB->fetchArray($getGroups)) {
			$groupArr[$group['groupType']][$group['parentid']][$group['groupid']] = $group['groupid'];
			
			$this->cacheContents .= '$' . $this->cacheType . '[\'' . $group['groupType'] . '\'][\'' . $group['parentid'] . '\'][\'' . $group['groupid'] . '\']';
			$this->cacheContents .= ' = ';
			$this->cacheContents .= '\'' . addslashes($group['groupid']) . '\';' . "\n";
		}
		
		// they should already be sorted... so let's get'em...
		$this->cacheInfo = serialize($groupArr);
	}
}
