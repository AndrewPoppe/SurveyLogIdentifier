<?php
namespace SurveyLogIdentifierNamespace\SurveyLogIdentifier;


use ExternalModules\AbstractExternalModule;
use ExternalModules\Framework;

use REDCap;

/**
 * @property Framework $framework
 * @see Framework
 */
class SurveyLogIdentifier extends AbstractExternalModule {

    public function redcap_save_record($projectId, $record, $instrument, $eventId, $groupId, $surveyHash, $responseId, $repeatInstance)
    {
        
        // get id-field setting
        $idField = $this->framework->getProjectSetting("id-field") ?? $this->framework->getRecordIdField();

        // get prefix setting
        $prefix = $this->framework->escape($this->framework->getProjectSetting("id-prefix")) ?? "[survey respondent]: ";

        // get instrument(s) setting
        $instruments = $this->framework->getProjectSetting("instrument") ?? [""];

        $index = array_search($instrument, $instruments);
        if ($index === false && !empty($instruments[0])) {
            return 0;
        }

        $logTable = $this->framework->getProject()->getLogTable();
        $sql = "SELECT * FROM $logTable WHERE project_id = ? ORDER BY log_event_id DESC LIMIT 1";
        $queryParams = [ $projectId ];
        $res = $this->framework->query($sql, $queryParams)->fetch_assoc();

        $descs = array("Create survey response", "Update survey response");
        
        $dataParams = array('project_id'=>$projectId, 'return format'=>'array', 'records'=>array($record), 'fields'=>array($idField), 'events'=>array($eventId));
        $identifier = REDCap::getData($dataParams)[$record][$eventId][$idField];
        $id = $prefix . $identifier;

        if (in_array($res["description"],  $descs)) {

            // Try to just update value in existing log entry
            $logEventIdOrig = $res["log_event_id"];
            $newSql = "UPDATE $logTable SET user = ? WHERE log_event_id = ?";
            $newParams = [ $id, $logEventIdOrig ];
            $updateRes = $this->framework->query($newSql, $newParams);

            if (!$updateRes) {
                // Fallback on logEvent from REDCap class
                $descriptionLog = $id . "\n" . $res["description"];
                $changesLog = $res["data_values"];
                $sqlLog = $res["sql_log"];
                $recordLog = $res["pk"];
                $eventLog = $eventId;
                $pidLog = $res["project_id"];

                REDCap::logEvent(
                    $descriptionLog,
                    $changesLog,
                    $sqlLog,
                    $recordLog,
                    $eventLog,
                    $pidLog
                );
            }
        }
    }

}
