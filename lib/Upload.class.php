<?php
/*
 * "n2" - Forum Software - a nBBS v0.6 + wtcBB remix.
 * Copyright (C) 2009 Chris F. Ravenscroft
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 * 
 * Questions? We can be reached at http://www.nextbbs.com
 */
// ************************************************** \\
## ************************************************** ##
## ************************************************** ##
## ****************** UPLOAD FILE ******************* ##
## ************************************************** ##
## ************************************************** ##
// ************************************************** \\


class Upload {
	// validExts and mimeTypes Array should line up...
	private $validExts, $mimeTypes, $sizeLimit, $imgLimit, $ext;
	private $mime, $name, $size, $tmp_name, $destination, $extraErr;

	// Constructor
	public function __construct($exts, $mimes, $filesArr, $to = '', $filesizeLimit = 0, $imgRestrictions = NULL) {
		global $lang;

		$this->validExts = $exts;
		$this->mimeTypes = $mimes;
		$this->sizeLimit = $filesizeLimit;
		$this->imgLimits = $imgRestrictions;
		$this->fileInfo = $filesArr;
		$this->mime = $filesArr['type'];
		$this->name = basename($filesArr['name']);
		$this->size = $filesArr['size'];
		$this->tmp_name = $filesArr['tmp_name'];
		$this->destination = $to;
		$this->ext = Upload::extensionFromFileName($this->name);
		$this->extraErr = '<p class="smallMarTop">File Name: <strong>' . wtcspecialchars($this->name) . '</strong></p>';

/* CFR: Why???
		// fix destination...
		if(strpos(substr($this->destination, 1), '.') === false) {
			$this->destination .= $this->name;
		}
 */		
	}

	// this is so we can return an error string
	public function doUpload() {
		global $lang;

		$err = '';

		if(!@is_uploaded_file($this->tmp_name)) {
			$err = new WtcBBException($lang['error_upload_errorMovingUploadedFile'] . $this->extraErr, false);
		}

		if(empty($err) AND is_array($this->validExts) AND is_array($this->mimeTypes)) {
			$err = $this->checkExtsMimes();
		}

		if(empty($err) AND $this->sizeLimit) {
			$err = $this->checkFileSize();
		}

		if(empty($err) AND is_array($this->imgLimits) AND strpos($this->mime, 'image') !== false) {
			$err = $this->checkImageLimits();
		}

		// okay... we're good to go...
		if(empty($err) AND (empty($this->destination) OR !move_uploaded_file($this->tmp_name, SCRIPT_HOME . $this->destination))) {
			$err = new WtcBBException($lang['error_upload_errorMovingUploadedFile'] . $this->extraErr, false);
		}

		return $err;
	}

	// Public methods
	public function getFileContents() {
		if(!empty($this->destination)) {
			return file_get_contents($this->destination . $this->name);
		}

		else {
			return file_get_contents($this->tmp_name);
		}
	}

	public function getDestination() {
		return ($this->destination);
	}

	// in case we need to kill the current file...
	public function destroy() {
		if(!empty($this->destination)) {
			@unlink($this->getDestination());
		}
	}

	// image?
	public function isImage() {
		return !(stripos($this->mime, 'image') === false);
	}

	// Public static methods
	public static function extensionFromFileName($fileName) {
		preg_match('/\.([^.]+)$/', $fileName, $matches);

		return $matches[1];
	}

	// Private methods
	private function checkExtsMimes() {
		global $lang;

		$valid = false;

		foreach($this->validExts as $index => $ext) {
			if(strtolower($ext) == strtolower($this->ext) AND strtolower($this->mimeTypes[$index]) == strtolower($this->mime)) {
				$valid = true;
				break;
			}
		}

		if(!$valid) {
			return new WtcBBException($lang['error_upload_invalidAttachmentType'] . $this->extraErr, false);
		}
	}

	private function checkFileSize() {
		global $lang;

		if($this->size > $this->sizeLimit) {
			return new WtcBBException($lang['error_upload_filesizeTooBig'] . $this->extraErr, false);
		}
	}

	private function checkImageLimits() {
		global $lang;

		$image = getimagesize($this->tmp_name);
		$width = $image[0];
		$height = $image[1];

		if(($this->imgLimits['width'] > 0 AND $width > $this->imgLimits['width']) OR ($this->imgLimits['height'] > 0 AND $height > $this->imgLimits['height'])) {
			return new WtcBBException($lang['error_upload_imageHeightOrWidthTooBig'] . $this->extraErr, false);
		}
	}
}

?>