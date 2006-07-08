<?php
/**
 * @version	$Id: ext_localconf.php 261 2006-03-22 07:26:13Z tzf4vy $
 * @author	Joerg Schoppet <joerg@schoppet.de>
 */

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
} // if (!defined('TYPO3_MODE'))

t3lib_extMgm::addPItoST43(
	$_EXTKEY,
	'pi1/class.tx_xajaxtutor_pi1.php',
	'_pi1',
	'list_type',
	0
);

?>