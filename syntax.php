<?php
/**
 * Redissue Syntax Plugin: Insert a link to redmine's issue
 *
 * @author Algorys
 */

if (!defined('DOKU_INC')) die();
require 'vendor/php-redmine-api/lib/autoload.php';
//require_once "json.php";

class syntax_plugin_redissue extends DokuWiki_Syntax_Plugin {
    const RI_IMPERSONATE = 4;

    // Get url of redmine issue
    function _getIssueUrl($id) {
        if(isset($data['server_url'])){
	        return $data['server_url'].'/issues/'.$id;
        } else {
            return $this->getConf('redissue.url').'/issues/'.$id;
        }
    }
    
    function _getImgName() {
        // If empty (False) get the second part
        return $this->getConf('redissue.img') ?: 'lib/plugins/redissue/images/redmine.png' ;
    }

    public function getType() {
        return 'substition';
    }
    /**
     * @return string Paragraph type
     */

    public function getDataFromAlias($server) {
        $json_file = file_get_contents(__DIR__.'/server.json');
        $json_data = json_decode($json_file, true);
        if(isset($json_data[$server])) {
            return $json_data[$server];
        } else {
            return null;
        }
    }

    public function getPType() {
        return 'normal';
    }
    // Keep syntax inside plugin
    function getAllowedTypes() {
        return array('container', 'baseonly', 'substition','protected','disabled','formatting','paragraphs');
    }

    public function getSort() {
        return 198;
    }
 
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<redissue[^>]*/>', $mode,'plugin_redissue');
        $this->Lexer->addEntryPattern('<redissue[^>/]*>(?=.*</redissue>)', $mode,'plugin_redissue');
    }
    function postConnect() {
        $this->Lexer->addExitPattern('</redissue>', 'plugin_redissue');
    }
    // Do the regexp
    function handle($match, $state, $pos, Doku_Handler $handler) {
        switch($state){
            case DOKU_LEXER_SPECIAL :
            case DOKU_LEXER_ENTER :
                $data = array(
                        'state'=>$state,
                        'id'=> 0,
                        'text'=>$this->getLang('redissue.text.default')
                    );

                preg_match("/server *= *(['\"])(.*?)\\1/", $match, $server);
                $server_url = $this->getConf('redissue.url');
                if ($server) {
                    $server_data = $this->getDataFromAlias($server[2]);
                    if( ! is_null($server_data)){
                        $data['server_url'] = $server_data['url'];
                        $data['server_token'] = $server_data['api_token'];
                    }
                }
                // Looking for id
                preg_match("/id *= *(['\"])#(\\d+)\\1/", $match, $id);
                if( count($id) != 0 ) {
                    $data['id'] = $id[2];
                } else {
                    return array(
                            'state'=>$state,
                            'error'=>true,
                            'text'=>'##ERROR &lt;redissue&gt;: id attribute required##'
                        );
                }
                // Looking for override title
                preg_match("/title *= *(['\"])(.*?)\\1/", $match, $title);
                if( count($title) != 0 ) {
                    $data['title'] = $title[2];
                }
                // Looking for short version
                $data['short'] = $this->getConf('redissue.short');
                preg_match("/short *= *(['\"])([0-1])\\1/", $match, $over_short);
                if( $over_short ){
                    $data['short'] = $over_short[2];
                }
                // Looking for text link
                preg_match("/text *= *(['\"])(.*?)\\1/", $match, $text);
                if( count($text) != 0 ) {
                    $data['text'] = $text[2];
                }
                // Looking for project id
                preg_match("/project *= *(['\"])(.*?)\\1/", $match, $project);
                if( count($project) != 0 ) {
                    $data['project_id'] = $project[2];
                }
                // looking for tracker_id
                preg_match("/tracker *= *(['\"])(.*?)\\1/", $match, $tracker);
                if( count($tracker) != 0 ) {
                    $data['tracker_id'] = $tracker[2];
                }

                return $data;
            case DOKU_LEXER_UNMATCHED :
                return array('state'=>$state, 'text'=>$match);
            default:
                return array('state'=>$state, 'bytepos_end' => $pos + strlen($match));
        }
    }

    function _render_custom_link($renderer, $data, $issue_id, $subject, $bootstrap) {
        // Check if user override title.
        if($data['title']) {
            $cur_title = $data['title'];
        }else{
            $cur_title = '[#'.$issue_id.'] ' . $subject;
        }
        if ($bootstrap){
            $renderer->doc .= '<a title="'.$this->getLang('redissue.link.issue').'" href="' . $this->_getIssueUrl($issue_id) . '"><img src="' . $this->_getImgName($data['img']) . '" class="redissue"/></a>';
            $renderer->doc .= '<a class="btn btn-primary redissue" role="button" data-toggle="collapse" href="#collapse-'.$issue_id.'" aria-expanded="false" aria-controls="collapse-'.$issue_id.'">';
            $renderer->doc .= $cur_title;
            $renderer->doc .= '</a> ';
        } else {
            $renderer->doc .= '<a title="'.$this->getLang('redissue.link.issue').'" href="' . $this->_getIssueUrl($issue_id) . '"><img src="' . $this->_getImgName($data['img']) . '" class="redissue"/>';
            $renderer->doc .= $cur_title;
            $renderer->doc .= '</a> ';
        }
        if($bootstrap){
            $renderer->doc .= '<div class="collapse" id="collapse-'.$issue_id.'">';
        }
    }

    function _render_default_link($renderer, $data, $bootstrap) {
        $this->_render_custom_link($renderer, $data, $data['id'], $data['text'], $bootstrap);
    }

    function _color_prio($client, $id_priority) {
        $all_prio = $client->api('issue_priority')->all();
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
    
    function _project_identifier($client, $project_name) {
            $project_id = $client->api('project')->getIdByName($project_name);
            $project = $client->api('project')->show($project_id);
            $project_identifier = $project['project']['identifier'];
            return $project_identifier;
    }

    // Main render_link
    function _render_link($renderer, $data) {
        // Check Bootstrap
        $bootstrap = False;
        if ($this->getConf('redissue.theme') == 8){
            $bootstrap = True;
        }
        // Check API_KEY
        $apiKey = ($this->getConf('redissue.API'));
        if (isset($data['server_token'])) {
            $apiKey = $data['server_token'];
        }
        if(empty($apiKey)){
            $this->_render_default_link($renderer, $data, $bootstrap);
        } else {
            $url = $this->getConf('redissue.url');
            if (isset($data['server_url'])) {
                $url = $data['server_url'];
            }
            $client = new Redmine\Client($url, $apiKey);
            // Get Id user of the Wiki if Impersonate
            $view = $this->getConf('redissue.view');
            if ($view == self::RI_IMPERSONATE) {
                $redUser = $_SERVER['REMOTE_USER'];;
                // Attempt to collect information with this user
                $client->setImpersonateUser($redUser);
            }
            if(array_key_exists('project_id', $data) && array_key_exists('tracker_id', $data)) {
                $issues = $client->issue->all([
                    'project_id' => $data['project_id'],
                    'tracker_id' => $data['tracker_id']
                ]);
                if(isset($issues['issues'])) {
                    for ($i = 0; $i < count($issues['issues']); $i++) {
                        $this->_display_issue($renderer, $data, $bootstrap, $client, $issues['issues'][$i]['id']);
                    }
                } else {
                    $renderer->doc .= '<p style="color: red;">REDISSUE: "project" ID or "tracker" ID is invalid ! Redissue display single issue instead !</p>';
                    $this->_display_issue($renderer, $data, $bootstrap, $client, $data['id']);
                }
            } else {
                $this->_display_issue($renderer, $data, $bootstrap, $client, $data['id']);
            }
        }
    }

    function _display_issue($renderer, $data, $bootstrap, $client, $issue_id) {
        // Issue Id
        $issue = $client->api('issue')->show($issue_id);

        // If server is wrong
        if($issue == 'Syntax error') {
            $renderer->doc .= '<p><b>Redissue ERROR:</b> Server exist in JSON config but seems not valid ! Please check your <b>url</b> or your <b>API Key</b> !</p>';
        // If server is good
        } elseif (isset($issue['issue'])) {
            // REDMINE DATA --- Get Info from the Issue
            $project = $issue['issue']['project'];
            $project_identifier = $this->_project_identifier($client, $project['name']);
            $tracker = $issue['issue']['tracker'];
            $status = $issue['issue']['status']['name'];
            $author = $issue['issue']['author'];
            $assigned = $issue['issue']['assigned_to'];
            $subject = $issue['issue']['subject'];
            $description = $issue['issue']['description'];
            $done_ratio = $issue['issue']['done_ratio'];
            // RENDERER_MAIN_LINK ---- Get the Id Status
            $myStatusId = $issue['issue']['status']['id'];
            $statuses = $client->api('issue_status')->all();
            // Browse existing statuses
            for($i = 0; $i < count($statuses['issue_statuses']); $i++) {
                $foundStatus = $statuses['issue_statuses'][$i];
                if($foundStatus['id'] == $myStatusId) {
                    // Get is_closed value
                    $isClosed = $foundStatus['is_closed'];
                }
            }

            // RENDERER
            $renderer->doc .= '<p>';
            $this->_render_custom_link($renderer, $data, $issue_id, $subject, $bootstrap);
 
            // PRIORITIES --- Get priority and define color
            $priority = $issue['issue']['priority'];
            $id_priority = $priority['id'];
            $color_prio = $this->_color_prio($client, $id_priority);
            if(!$isClosed){
                if($bootstrap){
                    $renderer->doc .= ' <span class="label label-success">' . $status . '</span>';
                }else{
                $renderer->doc .= ' <span class="badge-prio color-'.$color_prio.'">'.$priority['name'].'</span>';
                $renderer->doc .= ' <span class="badge-prio tracker">'. $tracker['name'].'</span>';
                    $renderer->doc .= ' <span class="badge-prio open">' . $status . '</span>';
                }
            } else {
                if($bootstrap){
                    $renderer->doc .= ' <span class="label label-default">' . $status . '</span>';
                }else{
                    $renderer->doc .= ' <span class="badge-prio closed">' . $status . '</span>';
                }
            }
            
            // GENERAL_RENDERER --- If all is ok
            if($bootstrap) {
                $renderer->doc .= ' <span class="label label-'.$color_prio.'">'.$priority['name'].'</span>';
                $renderer->doc .= ' <span class="label label-primary">'. $tracker['name'].'</span>';
                $renderer->doc .= '<div class="well">';
                $renderer->doc .= '<div class="issue-info"><dl class="dl-horizontal">';
                $renderer->doc .= '<dt><icon class="glyphicon glyphicon-info-sign">&nbsp;</icon>Projet :</dt>';
                $renderer->doc .= '<dd><a href="'.$url.'/projects/'.$project_identifier.'">'.$project['name'].'</a></dd>';
                $renderer->doc .= '<dt>Auteur :</dt>';
                $renderer->doc .= '<dd>'.$author['name'].' </dd>';
                $renderer->doc .= '<dt>Assigné à :</dt>';
                $renderer->doc .= '<dd>'.$assigned['name'].' </dd>';
                $renderer->doc .= '</dl></div>'; // ./ Issue-info
                $renderer->doc .= '<h4>Description</h4><p>'.$description.'</p>';
                $renderer->doc .= '<div class="progress">';
                $renderer->doc .= '<span class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width:'.$done_ratio.'%">';
                $renderer->doc .= '<span class="doku">'.$done_ratio.'% Complete</span>';
                $renderer->doc .= '</span></div>'; // ./progress
                $renderer->doc .= '</div>'; // ./ well 
                $renderer->doc .= '</div>';
            }else{ //Not Bootstrap
                $renderer->doc .= '<div ';
                if($data['short'] > 0) {
                    $renderer->doc .= 'style="display:none;"';
                }
                $renderer->doc .= 'class="issue-doku border-'.$color_prio.'">';
                $renderer->doc .= '<div>';
                $renderer->doc .= '<span><b>'.$this->getLang('redissue.project').' : </b></span>';
                $renderer->doc .= '<a href="'.$url.'/projects/'.$project_identifier.'"> '.$project['name'].'</a>';
                $renderer->doc .= '<span><b> '.$this->getLang('redissue.author').' : </b></span>';
                $renderer->doc .= ''.$author['name'].'';
                $renderer->doc .= '<br>';
                $renderer->doc .= '<span><b> '.$this->getLang('redissue.assigned').' :</b></span>';
                $renderer->doc .= '<a> '.$assigned['name'].' </a>';
                $renderer->doc .= '</span></div>'; // ./ Issue-info
                $renderer->doc .= '<div class="issue-description">';
                $renderer->doc .= '<h4>'.$this->getLang('redissue.desc').' :</h4>';
                $renderer->doc .= '<p>'.$description.'</p>';
                $renderer->doc .= '</div>';
                $renderer->doc .= '<div class="progress">';
                $renderer->doc .= '<span class="doku">'.$done_ratio.'% Complete</span>';
                $renderer->doc .= '</div>'; // ./progress
                $renderer->doc .= '</div>';
            }
            $renderer->doc .= '</p>';
        } else {
            // If the user has no Rights
            $this->_render_default_link($renderer, $data, $boostrap);
            if ($data['state'] == DOKU_LEXER_SPECIAL OR $data['state'] == DOKU_LEXER_ENTER){
                $renderer->doc .= '<div><div class="norights">';
            }
        }
    }

    // Dokuwiki Renderer
    function render($mode, Doku_Renderer $renderer, $data) {	
        if($mode != 'xhtml') return false;

        if($data['error']) {
            $renderer->doc .= $data['text'];
            return true;
        }
        $renderer->info['cache'] = false;
        switch($data['state']) {
            case DOKU_LEXER_SPECIAL :
                $this->_render_link($renderer, $data);
                break;
            case DOKU_LEXER_ENTER :
                $this->_render_link($renderer, $data);
                break;
            case DOKU_LEXER_EXIT:
                $renderer->doc .= '</div></div>';
            case DOKU_LEXER_UNMATCHED :
                $renderer->doc .= $renderer->_xmlEntities($data['text']);
                break;
        }
        return true;
    }
}
