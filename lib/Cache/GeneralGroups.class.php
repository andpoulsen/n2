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
## *************** GROUPS CACHE CLASS *************** ##
## ************************************************** ##
## ************************************************** ##
// ************************************************** \\


class GeneralGroups extends Cache {	
	// Constructor
	public function __construct() {
		global $_CACHE;
		
		$this->cacheType = 'generalGroups';
		$this->cacheArray = 1;
		$this->formCache();
		
		// update cache...
		if(!isset($_CACHE['generalGroups'])) {
			$this->insert();
		}
		
		else {
			$this->update();
		}
	}
	

	// Protected Methods
	protected function formCache() {
		global $query, $wtcDB;
		
		$getGroups = new Query($query['groups']['get_all']);
		$this->cacheInfo = Array();
		
		while($group = $wtcDB->fetchArray($getGroups)) {
			$this->cacheInfo[$group['groupType']][$group['groupid']] = new Group('', $group);
			
			$this->cacheContents .= '$' . $this->cacheType . '[\'' . $group['groupType'] . '\'][\'' . $group['groupid'] . '\']';
			$this->cacheContents .= ' = ';
			$this->cacheContents .= 'new Group(\'\', Array(' . Cache::writeArray($group) . '));' . "\n";
		}
		
		$this->cacheInfo = serialize($this->cacheInfo);
	}
}
