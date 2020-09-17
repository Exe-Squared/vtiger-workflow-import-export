<?php

/**
 * Created by PhpStorm.
 * User: Martin Allen
 * Date: 14/02/2019
 * Time: 15:47
 */


//Example Export Usage https://crm.example.com/workflowImportExport.php?mode=export&id=123 - will create workflows123.log in root directory
//Example Export Usage https://crm.example.com/workflowImportExport.php?mode=export&module=Contacts - will create workflowsContacts.log in root directory

//Example Import Usage https://crm.example.com/workflowImportExport.php?mode=import&file=123 - will read workflows123.log from root directory
//Example Import Usage https://crm.example.com/workflowImportExport.php?mode=import&file=Contacts - will read workflowsContacts.log from root directory

require_once 'include/utils/utils.php';
require 'modules/com_vtiger_workflow/VTWorkflowManager.inc';
require 'modules/com_vtiger_workflow/VTTaskManager.inc';
$wfm = new VTWorkflowManager($adb);
if (isset($_GET['mode']) && $_GET['mode'] == 'export') {
	if (isset($_GET['id'])) {
		$workflows = $wfm->retrieve($_GET['id']);
		$filename = $_GET['id'];
	} elseif (isset($_GET['module'])) {
		$workflows = $wfm->getWorkflowsForModule($_GET['module']);
		$filename = $_GET['module'];
	} else {
		echo "No id or module specified";
		exit();
	}

	if (is_array($workflows)) {
		foreach ($workflows as $workflow) {
			$workflowstrings[] = $wfm->serializeWorkflow($workflow);
		}
		foreach ($workflowstrings as $workflowstring) {
			//write to file
			//error_log(print_r($workflowstring, true), 3, "workflows" . $filename . ".log");
			file_put_contents("workflows".$filename.".log", $workflowstring.PHP_EOL, FILE_APPEND);
		}
	} else {
		$workflowstring = $wfm->serializeWorkflow($workflows);
		//error_log(print_r($workflowstring, true), 3, "workflows" . $filename . ".log");
		file_put_contents("workflows".$filename.".log", $workflowstring.PHP_EOL, FILE_APPEND);
	}

	echo "workflows".$filename.".log file written";

} elseif (isset($_GET['mode']) && $_GET['mode'] == 'import') {
	if (isset($_GET['file'])) {
		if (file_exists("workflows".$_GET['file'].".log")) {
			$workflowstrings = file("workflows".$_GET['file'].".log", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			foreach ($workflowstrings as $workflowstring) {
				$workflow = $wfm->deserializeWorkflow($workflowstring);
				$workflow->filtersavedinnew = 6;
				$wfm->save($workflow);
				echo 'Created workflow: '.$workflow->id;
			}
		} else {
			echo "File workflows".$_GET['file']."log does not exist";
		}
	} else {
		echo "No filename specified";
	}
} else {
	echo 'Do you want me to import or export??'.PHP_EOL;
	echo 'Use /workflowImportExport.php?mode=export&id=123 OR /workflowImportExport.php?mode=export&module=Contacts'.PHP_EOL;
	echo 'OR Use /workflowImportExport.php?mode=import&file=workflowsTasks.log'.PHP_EOL;
}