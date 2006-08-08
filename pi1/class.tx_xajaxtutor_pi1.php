<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2006 Joerg Schoppet (joerg@schoppet.de)
 * (c) 2006 Elmar Hinz (elmar.hinz@team-red.net)
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
 * @version	$Id: class.tx_xajaxtutor_pi1.php 280 2006-03-25 17:30:06Z tzf4vy $
 * @author	Joerg Schoppet <joerg@schoppet.de>
 * @author	Elmar Hinz <elmar.hinz@team-red.net>
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');

/**
 * Plugin 'xaJax Tutorial' for the 'xajax_tutor' extension.
 *
 * @author	Joerg Schoppet <joerg@schoppet.de>
 * @author	Elmar Hinz <elmar.hinz@team-red.net>
 * @package	TYPO3
 * @subpackage	xajax_tutor
 */

class tx_xajaxtutor_pi1 extends tslib_pibase {
	var $prefixId = 'tx_xajaxtutor_pi1';
	var $scriptRelPath = 'pi1/class.tx_xajaxtutor_pi1.php';
	var $extKey = 'xajax_tutor';
	var $conf;
	/**
	 * Vars for xajax
	 */

	function main($content, $conf) {
		// Loading TypoScript array into object variable:
		$this->conf = $conf;
		// Loading language-labels
		$this->pi_loadLL();
		// Init FlexForm configuration for plugin:
		$this->pi_initPIflexForm();
		// Make the plugin not cachable
		$this->pi_USER_INT_obj = 1;
		// Get the switching-mode
		$this->mode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'option', 'general');
		// Initialise the return variable
		$content = '';
		$sForm = '';
		$sFormResult = '';
		
		switch ($this->mode) {
			case 'withoutXajax':

				if (isset($this->piVars['submit_button'])) {
					$sFormResult = $this->sGetFormResult();
				} // if (isset($this->piVars['submit_button']))

				$sForm = $this->sGetForm();
				$content = $sForm . $sFormResult;
				break;
			case 'withXajax':

				/**
				 * Instantiate the xajax object and configure it
				 */
				// Include xaJax
				require_once (t3lib_extMgm::extPath('xajax') . 'class.tx_xajax.php');
				// Make the instance
				$this->xajax = t3lib_div::makeInstance('tx_xajax');
				// nothing to set, we send to the same URI
				# $this->xajax->setRequestURI('xxx');
				// Decode form vars from utf8 ???
				$this->xajax->decodeUTF8InputOn();
				// Encode of the response to utf-8 ???
				$this->xajax->setCharEncoding('utf-8');
				// To prevent conflicts, prepend the extension prefix
				$this->xajax->setWrapperPrefix($this->prefixId);
				// Do you wnat messages in the status bar?
				$this->xajax->statusMessagesOn();
				// Turn only on during testing
				#$this->xajax->debugOn();
				// Register the names of the PHP functions you want to be able to call through xajax
				// $xajax->registerFunction(array('functionNameInJavascript', &$object, 'methodName'));
				$this->xajax->registerFunction(array('processFormData', &$this, 'processFormData'));
				// If this is an xajax request, call our registered function, send output and exit
				$this->xajax->processRequests();
				// Else create javascript and add it to the header output
				$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = $this->xajax->getJavascript(t3lib_extMgm::siteRelPath('xajax'));
				// The form goes here
				$content .= $this->sGetForm();
				
				// The result box goes here
				if (!t3lib_div::_GP('xajax')) {
					// We make an empty result box on the first call to send our xajax responses to
					$content .= '<div id="formResult"></div>';
				} else {
					// This fallback will only be used if JavaScript doesn't work
					// Responses of xajax exit before this
					$content .= sprintf(
						'<div id="formResult">%s</div>',
						$this->sGetFormResult()
					);
				} // if (!t3lib_div::_GP('xajax'))

				break;
		} // switch ($this->mode)

		return $this->pi_wrapInBaseClass($content);
	} // function main($content, $conf)

	function sGetForm()	{
		$onSubmit = '';
		$onClick = '';

		if ($this->mode=='withXajax') {
			// form should not be send if xajax is on
			// (If javascript is disabled it works the normal way.)
			$onSubmit = ' onsubmit="return false;" ';
			// submit should call xajax instead.
			$onClick = ' onClick="' . $this->prefixId . 'processFormData(xajax.getFormValues(\'xajax_form\'))" ';
		} // if ($this->mode=='withXajax')

		$sReturn = '';
		$sReturn = sprintf(
			'<form %s action="%s" method="POST" enctype="multipart/form-data" id="xajax_form">
				<fieldset>
					<legend><strong>%s:</strong>&nbsp;</legend>
					<label for="%s">%s</label>
					<br />
					<textarea id="%s" name="%s[%s]" rows="5" cols="30"></textarea>
					<br />
					<input type="hidden" name="no_cache" value="1" />
					<input %s type="submit" name="%s[submit_button]" value="%s" />
					<input type="reset" />
				</fieldset>
			</form>
			<br />',
			$onSubmit,
			$this->pi_getPageLink(
				$GLOBALS['TSFE']->id,
				'',
				array('L' => $GLOBALS['TSFE']->config['config']['sys_language_uid'])
			),
			$this->pi_getLL('testform', '', TRUE),
			'mytext',
			$this->pi_getLL('textarea_label', '', TRUE),
			'mytext',
			$this->prefixId,
			'mytext',
			$onClick,
			$this->prefixId,
			$this->pi_getLL('submit_button', '', TRUE)
		);
		return $sReturn;
	} // function sGetForm()

	function sGetFormResult()	{
		$sResult = '%s';
		$sContent = '<strong>' . $this->pi_getLL('form_result', '', TRUE) .'</strong>';
		$sContent .= t3lib_div::view_array($this->piVars);

		if ($this->mode=='withoutXajax')	{
			$sResult = '<div id="formResult">%s</div>';
		} // if ($this->mode=='withoutXajax')
		
		$sResult = sprintf(
			$sResult,
			$sContent
		);
		return $sResult;
	} // function sGetFormResult()

	/**
	 * Your registered xaJax functions go here
	 */
	function processFormData($data)	{
		// We put our incoming data to the regular piVars
		$this->piVars = $data[$this->prefixId];
		// and proceed as a normal controller ...
		// We want to update the display for the part sGetFormResult
		$content = $this->sGetFormResult();
		// Once having prepared the content we still need to send it to the browser ...
		// Instantiate the tx_xajax_response object
		$objResponse = new tx_xajax_response();
		// Add the content to or result box
		$objResponse->addAssign('formResult', 'innerHTML', $content);
		//return the XML response
		return $objResponse->getXML();
		/**
		 * The $xajax->processRequests will send it and exit hereafter.
		 * To learn about 17 kindes of the tx_xajax_response()-functions
		 * have a look at the tx_xajax_response.inc.php
		 */
	} // function processFormData($data)

} // class tx_xajaxtutor_pi1 extends tslib_pibase

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/xajax_tutor/pi1/class.tx_xajaxtutor_pi1.php'])	{
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/xajax_tutor/pi1/class.tx_xajaxtutor_pi1.php']);
} // if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/xajax_tutor/pi1/class.tx_xajaxtutor_pi1.php'])

?>
