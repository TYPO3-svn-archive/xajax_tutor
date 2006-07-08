<?php
/**
 * @version	$Id: ext_tables.php 271 2006-03-23 00:34:26Z tzf4vy $
 * @author	Joerg Schoppet <joerg@schoppet.de>
 */

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
} // if (!defined('TYPO3_MODE'))

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1'] = 'layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';
t3lib_extMgm::addPlugin(
	array(
		'LLL:EXT:xajax_tutor/locallang.xml:tt_content.list_type',
		$_EXTKEY . '_pi1',
	),
	'list_type'
);
t3lib_extMgm::addPiFlexFormValue(
	$_EXTKEY . '_pi1',
	'FILE:EXT:xajax_tutor/flexform.xml'
);

if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModule(
		'web',
		'txxajaxtutorM1',
		'',
		t3lib_extMgm::extPath($_EXTKEY) . 'mod1/'
	);
} // if (TYPO3_MODE == 'BE')

?>