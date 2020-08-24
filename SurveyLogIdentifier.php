<?php
namespace SurveyLogIdentifierNamespace\SurveyLogIdentifier;


use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

use REDCap;

class SurveyLogIdentifier extends \ExternalModules\AbstractExternalModule {

    function redcap_save_record($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance)
    {
        
        // get id-field setting
        $id_field = AbstractExternalModule::getProjectSetting("id-field");
        if (is_null($id_field) || $id_field == "") $id_field = REDCap::getRecordIdField();

        // get prefix setting
        $prefix = AbstractExternalModule::getProjectSetting("id-prefix");
        if (is_null($prefix)) $prefix = "[survey respondent]: ";

        // get instrument(s) setting
        $instruments = AbstractExternalModule::getProjectSetting("instrument");
        if (is_null($instruments) || is_null($instruments[0])) $instruments = [""];

        $index = array_search($instrument, $instruments);
        if ($index === FALSE && $instruments[0] !== "") {
            return 0;
        }

        $log_event_table = method_exists('REDCap', 'getLogEventTable') ? REDCap::getLogEventTable($project_id) : "redcap_log_event";
        $msql = "SELECT * FROM $log_event_table WHERE project_id = $project_id ORDER BY log_event_id DESC LIMIT 1";
        $res = db_fetch_array(db_query($msql));
        $descs = array("Create survey response", "Update survey response");
        $params = array('project_id'=>$project_id, 'return format'=>'array', 'records'=>array($record), 'fields'=>array($id_field), 'events'=>array($event_id));
        $identifier = REDCap::getData($params)[$record][$event_id][$id_field];
        $id = $prefix . $identifier; 
        if (in_array($res["description"],  $descs)) {

            // Try to just update value in existing log entry
            $log_event_id_orig = $res["log_event_id"];
            $newsql = "UPDATE $log_event_table SET user='$id' WHERE log_event_id=$log_event_id_orig";
            $update_res = db_query($newsql);

            if (!$update_res) {
                // Create new log row
                $desc = $res["description"];
                $new_res = Logging::logEvent($res["sql_log"], 
                            $res["object_type"], 
                            $res["event"],  
                            $res["pk"], 
                            $res["data_values"], 
                            $description,  
                            $res["change_reason"], 
                            $id, 
                            $res["project_id"]);

                if ($new_res) {
                    // Delete original log row
                    $logid = $res["log_event_id"];
                    $del_sql = "DELETE FROM $log_event_table WHERE project_id = $project_id AND log_event_id = $logid;";
                    db_query($del_sql);
                } else {
                    // Fallback on logEvent from REDCap class
                    REDCap::logEvent($desc, $res["data_values"], $res["sql_log"], $res["pk"], "", $res["project_id"]);
                }

            }
        }
    }

}