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
## ******* ATTACHMENT EXTENSIONS CACHE CLASS ******** ##
## ************************************************** ##
## ************************************************** ##
// ************************************************** \\


class AttachmentExtensions extends Cache {	
	// Constructor
	public function __construct() {
		global $_CACHE;
		
		$this->cacheType = 'exts';
		$this->cacheArray = 1;
		$this->formCache();
		
		// update cache...
		if(!isset($_CACHE['exts'])) {
			$this->insert();
		}
		
		else {
			$this->update();
		}
	}
	

	// Protected Methods
	protected function formCache() {
		global $query, $wtcDB;
		
		$getExts = new Query($query['ext']['get_all']);
		$this->cacheInfo = Array();
		
		while($ext = $wtcDB->fetchArray($getExts)) {
			$this->cacheInfo[$ext['storeid']] = new AttachmentExtension('', $ext);
			
			$this->cacheContents .= '$' . $this->cacheType . '[\'' . $ext['storeid'] . '\']';
			$this->cacheContents .= ' = ';
			$this->cacheContents .= 'new AttachmentExtension(\'\', Array(' . Cache::writeArray($ext) . '));' . "\n";
		}
		
		$this->cacheInfo = serialize($this->cacheInfo);
	}
}
