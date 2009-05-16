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
## ************ PERSONAL MESSAGE CLASS ************** ##
## ************************************************** ##
## ************************************************** ##
// ************************************************** \\


class PersonalMessage extends Object {
	private $messageid, $info;

	// Constructor
	public function __construct($id = '', $messageinfo = '') {
		global $lang;

		if(!empty($messageinfo) AND is_array($messageinfo)) {
			$this->info = $messageinfo;
			$this->messageid = $this->info['messageid'];
		}

		else if(!empty($id)) {
			$this->messageid = $id;
			$this->queryInfoById();
		}

		else {
			new WtcBBException($lang['error_noInfo']);
		}

		$this->info['readby'] = unserialize($this->info['readby']);
	}


	// Public Methods
	// Deletes...
	public function destroy() {
		global $query, $lang;

		new Delete('personal_msg', 'messageid', $this->messageid, '', true, true);
		//new Delete('attachments', 'postid', $this->postid, '', true, true);
	}

	// Updates message... Accepts an array of fields and values
	public function update($arr) {
		global $query, $wtcDB, $lang;

		$update = $wtcDB->massUpdate($arr);

		// Execute!
		new Query($query['personal_msg']['update'], Array(1 => $update, 2 => $this->messageid), 'query', false);
	}

	// Accessors
	public function getMessageId() {
		return $this->messageid;
	}

	public function getConvoId() {
		return $this->info['convoid'];
	}

	public function getFolderId() {
		return $this->info['folderid'];
	}

	public function getStarterName() {
		return $this->info['username'];
	}

	public function getStarterId() {
		return $this->info['userid'];
	}

	public function getMessage() {
		return $this->info['message'];
	}

	public function getMessageTextArea() {
		return wtcspecialchars($this->info['message']);
	}

	public function getTitle() {
		return $this->info['title'];
	}

	public function getHash() {
		return $this->info['pmHash'];
	}

	public function getIP() {
		return $this->info['ip_address'];
	}

	public function getTimeline() {
		return $this->info['msg_timeline'];
	}

	public function getReadBy() {
		return $this->info['readby'];
	}

	public function showSig() {
		return $this->info['sig'];
	}

	public function showSmilies() {
		return $this->info['smilies'];
	}

	public function showBBCode() {
		return $this->info['bbcode'];
	}

	public function getInfo() {
		return $this->info;
	}


	// Protected Methods
	// gets info if ID is given
	protected function queryInfoById() {
		global $query, $lang, $wtcDB;

		$getMessage = new Query($query['personal_msg']['get'], Array(1 => $this->messageid));

		$this->info = parent::queryInfoById($getMessage);
	}


	// Static Methods
	// Public
	// inserts message... key is database field, value is database value in array
	public static function insert($arr) {
		global $wtcDB, $query;

		$db = $wtcDB->massInsert($arr);

		new Query($query['personal_msg']['insert'], Array(1 => $db['fields'], 2 => $db['values']), 'query', false);

		return $wtcDB->lastInsertId();
	}
}
