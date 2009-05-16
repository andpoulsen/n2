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
## *************** CRONS CACHE CLASS **************** ##
## ************************************************** ##
## ************************************************** ##
// ************************************************** \\


class Crons extends Cache {	
	// Constructor
	public function __construct() {
		global $_CACHE;
		
		$this->cacheType = 'crons';
		$this->cacheArray = 1;
		$this->formCache();
		
		// update cache...
		if(!isset($_CACHE['crons'])) {
			$this->insert();
		}
		
		else {
			$this->update();
		}
	}
	

	// Protected Methods
	protected function formCache() {
		global $query, $wtcDB;
		
		$getCrons = new Query($query['cron']['get_all']);
		$this->cacheInfo = Array();
		
		while($cron = $wtcDB->fetchArray($getCrons)) {
			$this->cacheInfo[$cron['cronid']] = new Cron('', $cron);
			
			$this->cacheContents .= '$' . $this->cacheType . '[\'' . $cron['cronid'] . '\']';
			$this->cacheContents .= ' = ';
			$this->cacheContents .= 'new Cron(\'\', Array(' . Cache::writeArray($cron) . '));' . "\n";
		}
		
		$this->cacheInfo = serialize($this->cacheInfo);
	}
}
