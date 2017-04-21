<?php
/**
 * Redissue Syntax Plugin: Insert a link to redmine's issue
 *
 * @author Algorys
 */

if (!defined('DOKU_INC')) die();
require 'vendor/php-redmine-api/lib/autoload.php';

class DokuwikiRedmine {
    public $client;

    function connect($url, $apiKey){
        $this->client = new Redmine\Client($url, $apiKey);
    }

    function getProjectIdentifier($project_name) {
            $project_id = $this->client->api('project')->getIdByName($project_name);
            $project = $this->client->api('project')->show($project_id);
            return $project['project']['identifier'];
    }

    function getIssue($issue_id){
        return $this->client->issue->show($issue_id);
    }

    function getStatuses(){
        return $this->client->issue_status->all();
    }

    function getPriorityColor($id_priority) {
        $all_prio = $this->client->api('issue_priority')->all();
        $normal_prio = 0;
        // Get the normal index and current index
        for ($i = 0; $i < count($all_prio['issue_priorities']); $i++) {
            $current_prio = $all_prio['issue_priorities'][$i];
            if ($current_prio['is_default'] == 1) {
                $normal_prio = $i;
            }
            if($current_prio['id'] == $id_priority){
                $index_prio = $i;
            }
        }
        $min_prio = 0;
        $low_prio = $normal_prio - 1;
        $high_prio = $normal_prio + 1;
        $critical_prio = count($all_prio['issue_priorities']) - 1;
        if($index_prio == $normal_prio) {
           $color_prio = 'success';
        }
        elseif($index_prio == $min_prio) {
            $color_prio = 'info';
        }
        elseif($index_prio < $normal_prio && $index_prio > $min_prio) {
            $color_prio = 'primary';
        }
        elseif($index_prio > $normal_prio && $index_prio < $critical_prio) {
            $color_prio = 'warning';
        }
        else {
            $color_prio = 'danger';
        }
        return $color_prio;
    }

}

