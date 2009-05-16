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
## ****************** CACHE CLASS ******************* ##
## ************************************************** ##
## ************************************************** ##
// ************************************************** \\


// Make sure you call cache classes
// AFTER new information has been inserted
// into database
class Cache {
	protected $cacheType, $cacheInfo, $cacheArray, $cacheContents;
	
	// Constructor
	public function __construct($cacheName) {
		$this->cacheType = '';
		$this->cacheInfo = '';
		$this->cacheArray = 0;
		$this->cacheContents = '';
		
		if($cacheName == 'Forums' OR $cacheName == 'OrderedForums') {
			return;
		}
		
		eval('new ' . $cacheName . '();');
	}
	
	
	// Protected Methods
	protected function insert() {
		global $query, $wtcDB;
		
		//$q = new Query($query['global']['cache_insert'], Array(1 => $this->cacheType, $this->cacheInfo, $this->cacheArray));
		
		$this->cacheFile();
	}
	
	protected function update() {
		global $query, $wtcDB;
		
		//new Query($query['global']['cache_update'], Array(1 => $this->cacheType, $this->cacheInfo, $this->cacheArray, $this->cacheType));
		
		$this->cacheFile();
	}
	
	// caches the file
	protected function cacheFile() {
		// write cache contents to file (as PHP code)
		//if(!empty($this->cacheContents)) {
			$this->cacheContents = '<?php
 /*

 * wtcBB Community Software (Open Source Freeware Version)

 * Copyright (C) 2004-2007. All Rights Reserved. wtcBB Software
Solutions.

 * http://www.wtcbb.com/

 *

 * Licensed under the terms of the GNU Lesser General Public License:

 * http://www.wtcbb.com/wtcbb-license-general-public-license

 *

 * For support visit:

 * http://forums.wtcbb.com/

 *

 * Powered by wtcBB - http://www.wtcbb.com/

 * Protected by ChargebackFile - http://www.chargebackfile.com/

 *

*/
 ' . "\n\n" . $this->cacheContents . "\n" . '?>';
			file_put_contents('./cache/' . $this->cacheType . '.cache.php', $this->cacheContents);
		//}
	}
	
	// MUST BE OVERLOADED
	protected function formCache() {}
	
	// STATIC METHODS
	// this will load cache data
	public static function load($cacheItemToRetrieve) {
		extract($GLOBALS);
		
		$path = './cache/' . $cacheItemToRetrieve . '.cache.php';
		
		if(file_exists($path)) {
			require_once($path);
			
			if(!isset(${$cacheItemToRetrieve})) {
				$retval = Array(0);
			}
			
			else {
				$retval = ${$cacheItemToRetrieve};
			}
		}
		
		else {
			$retval = false;
		}
		
		return $retval;
	}
	
	// this makes an array writable
	public static function writeArray($array) {
		$retval = '';
		$before = '';
		
		foreach($array as $key => $val) {
			$retval .= $before . '\'' . str_replace("'", "\'", $key) . '\' => \'' . str_replace("'", "\'", $val) . '\'';
			$before = ', ';
		}
		
		return $retval;
	}
}
