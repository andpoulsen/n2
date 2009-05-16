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
## ************** STYLE FRAGMENT CLASS ************** ##
## ************************************************** ##
## ************************************************** ##
// ************************************************** \\


class StyleFragment extends Object {
	private $fragmentid, $info;
	private $name, $loaded, $type, $css;

	// Constructor
	public function __construct($id = '', $fragmentinfo = 'template', $buildLikeAdmin = false) {
		global $STYLE, $query, $wtcDB, $lang;

		$this->css = '';

		if(ADMIN OR $buildLikeAdmin) {
			if(is_array($fragmentinfo)) {
				$this->info = $fragmentinfo;
				$this->fragmentid = $this->info['fragmentid'];
			}

			else if(!empty($id)) {
				$this->fragmentid = $id;
				$this->queryInfoById();
			}

			else {
				new WtcBBException($lang['error_noInfo']);
			}
		}

		else {
			// if not in ADMIN... build our fragements!
			if(!Style::$fragments) {
                               Style::$fragments = $STYLE->buildFragmentsByName('%');
			}

			$this->name = $id;
			$this->type = $fragmentinfo;

			if(!isset(Style::$fragments[$this->type][$this->name])) {
				new WtcBBException($lang['error_noTemplate']);
			}

			$this->fragmentid = Style::$fragments[$this->type][$this->name]->getFragmentId();
			$this->info = Style::$fragments[$this->type][$this->name]->getInfo();
			$this->loaded = $this->build();
                        
			// stylesheet? o_0
			if(preg_match('/^stylesheet/i', $this->getVarName())) {
				$stylesheetName = './css/' . $this->getFragmentId() . '_' . preg_replace('/\s+/', '-', $this->getVarName()) . '.css';

				if(!file_exists($stylesheetName)) {
					file_put_contents($stylesheetName, $this->dump());
				}

				$this->css = $stylesheetName;
			}
		}
	}


	// Public Methods
	// Deletes...
	public function destroy($justDoIt = false) {
		global $query, $lang;

		if($justDoIt) {
			new Delete('styles_fragments', 'fragmentid', $this->fragmentid, '', true, true);
		}

		else {
			new Delete('styles_fragments', 'fragmentid', $this->fragmentid, '');
		}

		// update styles now...
		Style::buildStyles();

		if(!$justDoIt) {
			$backTo = '';

			if($this->getFragmentType() == 'style') {
				$backTo = 'admin.php?file=style&amp;colors=' . $this->getStyleId() . '&amp;t=' . $this->getDefaultId();
			}

			else if($this->getFragmentType() == 'template') {
				$backTo = 'admin.php?file=style&amp;templates=' . $this->getStyleId() . '&amp;groupid=' . $this->getGroupId() . '#'. $this->getGroupId();
			}

			else if($this->getFragmentType() == 'variable') {
				$backTo = 'admin.php?file=style&amp;repVars=' . $this->getStyleId();
			}

			new WtcBBThanks($lang['admin_thanks_msg'], $backTo);
		}
	}

	// Updates fragment... Accepts an array of fields and values
	public function update($arr) {
		global $query, $wtcDB, $lang;

		$update = $wtcDB->massUpdate($arr);

		// Execute!
		new Query($query['styles_fragments']['update'], Array(1 => $update, 2 => $this->fragmentid), 'query', false);

		// update styles now...
		Style::buildStyles();
	}

	// Parses the template conditionals
	public function parseTemplate($template) {
		$template = addslashes($template);

		// look for template cond matches...
		while(preg_match('{<if\((.*)\)>(.*)(<else />(.*)</if>|</if>|<else /></if>)}isU', $template, $matches)) {
			if($matches[3] == '<else /></if>' OR $matches[3] == '</if>') {
				$template = str_replace($matches[0], '" . ((' . stripslashes($matches[1]) . ') ? ("' . $matches[2] . '") : ("")) . "', $template);
			}

			else {
				$template = str_replace($matches[0], '" . ((' . stripslashes($matches[1]) . ') ? ("' . $matches[2] . '") : ("' . $matches[4] . '")) . "', $template);
			}
		}

		return $template;
	}

	// Accessors
	public function getFragmentId() {
		return $this->fragmentid;
	}

	public function getStyleId() {
		return $this->info['styleid'];
	}

	public function getGroupId() {
		return $this->info['groupid'];
	}

	public function getGroupName() {
		global $generalGroups;

		return $generalGroups['styles_fragments'][$this->getGroupId()]->getGroupName();
	}

	public function getName() {
		return $this->info['fragmentName'];
	}

	public function getVarName() {
		return $this->info['fragmentVarName'];
	}

	public function getFragmentType() {
		return $this->info['fragmentType'];
	}

	public function getFragment() {
		return $this->info['fragment'];
	}

	public function getFragmentPHP() {
                return $this->info['template_php'];
	}

	public function getDisOrder() {
		return $this->info['disOrder'];
	}

	public function getCssFilePath() {
		return $this->css;
	}

	public function getDefaultId() {
		return $this->info['defaultid'];
	}

	public function getInfo() {
		return $this->info;
	}

	// Accessing fragments
	private function build() {
		// global view...
		extract($GLOBALS);

		// get the template...
		if($this->type == 'template') {
			eval('$retval = "' . $this->getFragmentPHP() . '";');
			$retval = str_replace("\'", "'", $retval); // i guess i did it for a reason
			//$retval = stripslashes($retval); // why did i do this in the first place? o_0
		}

		else {
			$retval = $this->getFragment();
		}

		return $retval;
	}

	public function dump() {
		return $this->loaded;
	}

	public function output() {
		print($this->loaded);
	}


	// Protected Methods
	// gets info if ID is given
	protected function queryInfoById() {
		global $query, $lang, $wtcDB;

		$getFragment = new Query($query['styles_fragments']['get'], Array(1 => $this->fragmentid));

		$this->info = parent::queryInfoById($getFragment);
	}


	// Static Methods
	// Public
	// inserts fragment... key is database field, value is database value in array
	public static function insert($arr, $escape = false) {
		global $wtcDB, $query;

		$db = $wtcDB->massInsert($arr, $escape);

		new Query($query['styles_fragments']['insert'], Array(1 => $db['fields'], 2 => $db['values']), 'query', false);

		$retval = $wtcDB->lastInsertId();

		// update styles now...
		Style::buildStyles();

		return $retval;
	}
}
