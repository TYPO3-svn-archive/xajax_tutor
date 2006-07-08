<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2006 Lars Quitsch (lars@quitsch.org)
 * All rights reserved
 *
 * This script is part of the Typo3 project. The Typo3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'xaJax Tutorial' for the 'xajax_tutor' extension.
 *
 * @version	$Id: index.php 271 2006-03-23 00:34:26Z tzf4vy $
 * @author	Lars Quitsch <lars@quitsch.org>
 */

// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ('conf.php');
require ($BACK_PATH . 'init.php');
require ($BACK_PATH .'template.php');

/**********************************************************
 *
 * Include the xajax class library:
 *
 *********************************************************/
require (t3lib_extMgm::extPath('xajax') . 'class.tx_xajax.php');

$LANG->includeLLFile('EXT:xajax_tutor/mod1/locallang.xml');
require_once (PATH_t3lib . 'class.t3lib_scbase.php');
// This checks permissions and exits if the user has no permission for entry
$BE_USER->modAccess($MCONF,1);
// DEFAULT initialization of a module [END]

class tx_xajaxtutor_module1 extends t3lib_SCbase	{
	var $pageinfo;
	var $xajax;
	
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		parent::init();
		// Instantiate the tx_xajax object
		$this->xajax = new tx_xajax();
		// $this->xajax->debugOn();
		/**
		 *	Register the names of the PHP functions you want to be able to call through xajax
		 *
		 * $xajax->registerFunction(array('functionNameInJavescript', &$object, 'methodName'));
		 */
		$this->xajax->registerFunction(array("tx_xajaxtutor_myAjaxFunction",&$this,"myAjaxFunction"));
		$this->xajax->registerFunction(array("tx_xajaxtutor_changeValue",&$this,"changeValue"));
		$this->xajax->registerFunction(array("tx_xajaxtutor_processFormData",&$this,"processFormData"));
	} // function init()

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('simple'),
				'2' => $LANG->getLL('form'),
			)
		);
		parent::menuConfig();
	} // function menuConfig()

	// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module. Write the content to $this->content
	 */
	function main() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id) || ($BE_USER->user['uid'] && !$this->id)) {
			// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form id="mainForm" action="" method="POST">';
			// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL) {
						document.location = URL;
					}
				</script>

				' . $this->xajax->getJavascript(t3lib_div::resolveBackPath($BACK_PATH . '../' . t3lib_extMgm::siteRelPath('xajax')));	// Add xajaxs javascriptcode to the header'
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
						if (top.fsMod) top.fsMod.recentIds["web"] = ' . intval($this->id) . ';
					</script>
				';
			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']). '<br />' . $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path') . ': ' . t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'], 50);
			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header('xaJax Tutorial');
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content .= $this->doc->divider(5);
			// Render content:
			$this->moduleContent();

			// ShortCut
			if ($BE_USER->mayMakeShortcut()) {
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			} // if ($BE_USER->mayMakeShortcut())

			$this->content.=$this->doc->spacer(10);
		} else {
			// If no access or if ID == zero
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->spacer(10);
		} // if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id) || ($BE_USER->user['uid'] && !$this->id))

	} // function main()

	/**
	 * Prints out the module HTML
	 */
	function printContent()	{
		global $BACK_PATH;
		$this->xajax->processRequests();	// Before your script sends any output, have xajax handle any requests
		$this->content.=$this->doc->endPage();
		echo $this->content;
	} // function printContent()

	/**
	 * Your own xajax funktions
	 *
	 * For more tx_xajax_response()-functions have a look at the tx_xajax_response.inc.php
	 *
	 * Currently xajax supports 17 kinds of command messages, including some common ones such as:
	 *
	 * Assign - sets the specified attribute of an element in your page
	 * Append - appends data to the end of the specified attribute of an element in your page
	 * Prepend - prepends data to the beginning of the specified attribute of an element in your page
	 * Replace - searches for and replaces data in the specified attribute of an element in your page
	 * Script - runs the supplied JavaScript code
	 * Alert - shows an alert box with the supplied message text
	 */

	function myAjaxFunction($arg)	{
		// do some stuff based on $arg like query data from a database and
		// put it into a variable like $ajax_content
		$ajax_content = $arg;	// here will use the argument as output
		// Instantiate the tx_xajax_response object
		$objResponse = new tx_xajax_response();
		$objResponse->addAssign('insertAjax', 'innerHTML', $ajax_content);
		//return the XML response generated by the tx_xajax_response object
		return $objResponse->getXML();
	} // function myAjaxFunction($arg)

	function changeValue($arg)	{
		// do some stuff based on $arg like query data from a database and
		// put it into a variable like $ajax_content
		$ajax_content = $arg;	// here will use the argument as output
		// Instantiate the tx_xajax_response object
		$objResponse = new tx_xajax_response();
		$objResponse->addAssign('change', 'value', $ajax_content);
		//return the XML response generated by the tx_xajax_response object
		return $objResponse->getXML();
	} // function changeValue($arg)

	function processFormData($arg)	{
		// Debug the content of the "submitted" Form
		$xajax_content = '<br /><br /><strong>submitted values</strong><br /><br />
						' . t3lib_div::view_array($arg);	// here will output the Array, it looks like the $_POST/$_GET, which would be generated.
		// Instantiate the tx_xajax_response object
		$objResponse = new tx_xajax_response();
		$objResponse->addAssign('formDiv', 'innerHTML', $xajax_content);
		//return the XML response generated by the tx_xajax_response object
		return $objResponse->getXML();
	} // function processFormData($arg)

	/**
	 * Generates the module content
	 */
	function moduleContent()	{

		switch ((string)$this->MOD_SETTINGS['function']) {
			case '1':
				// insert container with the id of the first addAssign() parameter
				// create function call for your own function: xajax_(yourFunctionName)($arguments)
				$content = '<br /><input type="BUTTON" value="Insert in DIV" onclick="xajax_tx_xajaxtutor_myAjaxFunction(\'Hello World!\');">
						<div id="insertAjax" style="border-color:#000000; border-width:1px; border-style:solid; padding:4px; width:100px"></div>
						<br /><br />
						';
				$this->content .= $this->doc->section('Set inner HTML', $content, 0, 1);
				$content2 = '<br />
							<input type="BUTTON" value="Change Field Value" onclick="xajax_tx_xajaxtutor_changeValue(\'This Text just changed!\');">
							<div><input type="text" value="text to change" id="change" /></div>';
				$this->content .= $this->doc->section('Change field value', $content2, 0, 1);
				break;
			case '2':
				// You need to close the Form tag from the function menue, otherwise xajax won't find the correct form ID
				$content .= '</form>';
				$content .= '<div>
						<form id="testForm" name="testForm">
							<input type="text" name="textfield" id="textfield" />
							Textfield
							<br />
							<br />
							<input type="checkbox" name="checkbox1" id="checkbox1" value="check1" />checkbox1<br />
							<input type="checkbox" name="checkbox2" id="checkbox2" value="check1" />checkbox2<br />
							<input type="checkbox" name="checkbox3" id="checkbox3" value="check1" />checkbox3<br />
							<br />
							<select name="selectfield[]" id="selectfield" multiple>
								<option>no value</option>
								<option value="option1">value 1</option>
								<option value="option2">value 2</option>
								<option value="option3">value 3</option>
							</select>
							Selectbox (multiple)
							<br />
							<input type="file" name="file" id="file" /> Fileupload <br /><br />
						</form>
						<input id="submitButton" type="BUTTON" value="Submit" onclick=\'xajax_tx_xajaxtutor_processFormData(xajax.getFormValues("testForm"));\' />
					</div>
					<div id="formDiv"></div>';
				$this->content .= $this->doc->section('xaJax Form example', $content, 0, 1);
				break;
		} // switch ((string)$this->MOD_SETTINGS['function'])

	} // function moduleContent()

} // class tx_xajaxtutor_module1 extends t3lib_SCbase

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/xajax_tutor/mod1/index.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/xajax_tutor/mod1/index.php']);
} // if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/xajax_tutor/mod1/index.php'])

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_xajaxtutor_module1');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
	include_once ($INC_FILE);	
} // foreach ($SOBE->include_once as $INC_FILE)

$SOBE->main();
$SOBE->printContent();

?>