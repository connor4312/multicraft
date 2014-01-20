<?php

//   -------------------------------------------------------------------------------
//  |                  net2ftp: a web based FTP client                              |
//  |              Copyright (c) 2003-2009 by David Gartner                         |
//  |                                                                               |
//  | This program is free software; you can redistribute it and/or                 |
//  | modify it under the terms of the GNU General Public License                   |
//  | as published by the Free Software Foundation; either version 2                |
//  | of the License, or (at your option) any later version.                        |
//  |                                                                               |
//   -------------------------------------------------------------------------------





// **************************************************************************************
// **************************************************************************************
// **                                                                                  **
// **                                                                                  **

function getActivePlugins() {

// --------------
// This function modifies the global variable $net2ftp_globals["activePlugins"], which contains an array 
// with all active plugin names
//
// Which plugin is active depends on 2 things:
// 1 - if the plugin is enabled or disabled (see the ["use"] field in getPluginProperties())
// 2 - the $net2ftp_globals["state"] and $net2ftp_globals["state2"] variables, as well as other specific variables (see this function)
// --------------

// -------------------------------------------------------------------------
// Global variables
// -------------------------------------------------------------------------
	global $net2ftp_globals;
	$pluginProperties = getPluginProperties("ALL");
	$plugincounter = 0;
	$activePlugins = array();
	if (isset($_POST["textareaType"]) == true) { $textareaType = $_POST["textareaType"]; }

// -------------------------------------------------------------------------
// Plugins to always activate
// -------------------------------------------------------------------------


// -------------------------------------------------------------------------
// Plugins to activate depending on the $state and $state2 variables
// -------------------------------------------------------------------------

// -------------------------------------------------------------------------
// Plugins to activate depending on other variables
// -------------------------------------------------------------------------
	if ($net2ftp_globals["state"] == "edit" && isset($textareaType) == true && $textareaType != "" && array_key_exists($textareaType, $pluginProperties) == true) {
		if ($pluginProperties[$textareaType]["use"] == "yes") { $activePlugins[$plugincounter] = $textareaType; $plugincounter++; } 
	}

	return $activePlugins;

} // end function getActivePlugins

// **                                                                                  **
// **                                                                                  **
// **************************************************************************************
// **************************************************************************************





// **************************************************************************************
// **************************************************************************************
// **                                                                                  **
// **                                                                                  **

function isActivePlugin($plugin) {

// --------------
// This function checks if a plugin is active or not
// --------------

	global $net2ftp_globals;
	return in_array($plugin, $net2ftp_globals["activePlugins"]);

} // end function isActivePlugin

// **                                                                                  **
// **                                                                                  **
// **************************************************************************************
// **************************************************************************************






// **************************************************************************************
// **************************************************************************************
// **                                                                                  **
// **                                                                                  **

function getPluginProperties() {

// --------------
// This function returns an array with all plugin properties
// --------------

// -------------------------------------------------------------------------
// Global variables
// -------------------------------------------------------------------------
	global $net2ftp_globals, $net2ftp_settings;

    $pluginProperties = array();
	return $pluginProperties;

} // end function getPluginProperties

// **                                                                                  **
// **                                                                                  **
// **************************************************************************************
// **************************************************************************************









// **************************************************************************************
// **************************************************************************************
// **                                                                                  **
// **                                                                                  **

function net2ftp_plugin_includePhpFiles() {

// --------------
// This function includes PHP files which are required by the active plugins
// The list of current active plugins is stored in $net2ftp_globals["activePlugins"]
// --------------

// -------------------------------------------------------------------------
// Global variables and settings
// -------------------------------------------------------------------------
	global $net2ftp_globals;
	$pluginProperties = getPluginProperties();

// -------------------------------------------------------------------------
// Initial checks and initialization
// -------------------------------------------------------------------------
	if ($net2ftp_globals["activePlugins"] == "") { return ""; }

// -------------------------------------------------------------------------
// For all plugins...
// -------------------------------------------------------------------------
	for ($pluginnr=0; $pluginnr<sizeof($net2ftp_globals["activePlugins"]); $pluginnr++) {

// Get the plugin related data
		$currentPlugin = $pluginProperties[$net2ftp_globals["activePlugins"][$pluginnr]];

// Check if the plugin should be used
		if ($currentPlugin["use"] != "yes" || $currentPlugin["includePhpFiles"][1] == "") { continue; }

// -------------------------------------------------------------------------
// Include PHP files
// -------------------------------------------------------------------------
		for ($i=1; $i<=sizeof($currentPlugin["includePhpFiles"]); $i++) {
			require_once($net2ftp_globals["application_pluginsdir"] . "/" . $currentPlugin["includePhpFiles"][$i]);
		} // end for

	} // end for

} // End function net2ftp_plugin_includePhpFiles

// **                                                                                  **
// **                                                                                  **
// **************************************************************************************
// **************************************************************************************





// **************************************************************************************
// **************************************************************************************
// **                                                                                  **
// **                                                                                  **

function net2ftp_plugin_printJavascript() {

// --------------
// This function includes PHP files which are required by the active plugins
// The list of current active plugins is stored in $net2ftp_globals["activePlugins"]
// --------------

// -------------------------------------------------------------------------
// Global variables and settings
// -------------------------------------------------------------------------
	global $net2ftp_globals;
	$pluginProperties = getPluginProperties();

// -------------------------------------------------------------------------
// Initial checks and initialization
// -------------------------------------------------------------------------
	if ($net2ftp_globals["activePlugins"] == "") { return ""; }

// -------------------------------------------------------------------------
// For all plugins...
// -------------------------------------------------------------------------
	for ($pluginnr=0; $pluginnr<sizeof($net2ftp_globals["activePlugins"]); $pluginnr++) {

// Get the plugin related data
		$currentPlugin = $pluginProperties[$net2ftp_globals["activePlugins"][$pluginnr]];

// Check if the plugin should be used
		if ($currentPlugin["use"] != "yes") { continue; }

// -------------------------------------------------------------------------
// Print Javascript code
// -------------------------------------------------------------------------
		echo $currentPlugin["printJavascript"];

	} // end for

} // End function net2ftp_plugin_printJavascript

// **                                                                                  **
// **                                                                                  **
// **************************************************************************************
// **************************************************************************************





// **************************************************************************************
// **************************************************************************************
// **                                                                                  **
// **                                                                                  **

function net2ftp_plugin_printCss() {

// --------------
// This function includes PHP files which are required by the active plugins
// The list of current active plugins is stored in $net2ftp_globals["activePlugins"]
// --------------

// -------------------------------------------------------------------------
// Global variables and settings
// -------------------------------------------------------------------------
	global $net2ftp_globals;
	$pluginProperties = getPluginProperties();

// -------------------------------------------------------------------------
// Initial checks and initialization
// -------------------------------------------------------------------------
	if ($net2ftp_globals["activePlugins"] == "") { return ""; }

// -------------------------------------------------------------------------
// For all plugins...
// -------------------------------------------------------------------------
	for ($pluginnr=0; $pluginnr<sizeof($net2ftp_globals["activePlugins"]); $pluginnr++) {

// Get the plugin related data
		$currentPlugin = $pluginProperties[$net2ftp_globals["activePlugins"][$pluginnr]];

// Check if the plugin should be used
		if ($currentPlugin["use"] != "yes") { continue; }

// -------------------------------------------------------------------------
// Print CSS code
// -------------------------------------------------------------------------
		echo $currentPlugin["printCss"];

	} // end for

} // End function net2ftp_plugin_printCss

// **                                                                                  **
// **                                                                                  **
// **************************************************************************************
// **************************************************************************************





// **************************************************************************************
// **************************************************************************************
// **                                                                                  **
// **                                                                                  **

function net2ftp_plugin_printBodyOnload() {

// --------------
// This function includes PHP files which are required by the active plugins
// The list of current active plugins is stored in $net2ftp_globals["activePlugins"]
// --------------

// -------------------------------------------------------------------------
// Global variables and settings
// -------------------------------------------------------------------------
	global $net2ftp_globals;
	$pluginProperties = getPluginProperties();

// -------------------------------------------------------------------------
// Initial checks and initialization
// -------------------------------------------------------------------------
	if ($net2ftp_globals["activePlugins"] == "") { return ""; }

// -------------------------------------------------------------------------
// For all plugins...
// -------------------------------------------------------------------------
	for ($pluginnr=0; $pluginnr<sizeof($net2ftp_globals["activePlugins"]); $pluginnr++) {

// Get the plugin related data
		$currentPlugin = $pluginProperties[$net2ftp_globals["activePlugins"][$pluginnr]];

// Check if the plugin should be used
		if ($currentPlugin["use"] != "yes") { continue; }

// -------------------------------------------------------------------------
// Print <body onload=""> code
// -------------------------------------------------------------------------
		echo $currentPlugin["printBodyOnload"];

	} // end for

} // End function net2ftp_plugin_printBodyOnload

// **                                                                                  **
// **                                                                                  **
// **************************************************************************************
// **************************************************************************************

?>
