<?php
/**
 * Redissue Syntax Plugin: Insert a link to redmine's issue
 *
 * @author Algorys
 */

if (!defined('DOKU_INC')) die();
//require 'vendor/php-redmine-api/lib/autoload.php';
require 'redmine/redmine.php';

class syntax_plugin_redissue extends DokuWiki_Syntax_Plugin {
    const RI_IMPERSONATE = 4;

    public function getType() {
        return 'substition';
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

    function getDataFromAlias($server) {
        $json_file = file_get_contents(__DIR__.'/server.json');
        $json_data = json_decode($json_file, true);
        if(isset($json_data[$server])) {
            return $json_data[$server];
        } else {
            return null;
        }
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        switch($state){
            case DOKU_LEXER_SPECIAL :
                $data = array(
                        'state'=>$state,
                        'id'=> 0,
                        'text'=>$this->getLang('redissue.text.default')
                    );
                // Redmine Server
                preg_match("/server *= *(['\"])(.*?)\\1/", $match, $server);
                //$server_url = $this->getConf('redissue.url');
                if (count($server) != 0) {
                    $server_data = $this->getDataFromAlias($server[2]);
                    if( ! is_null($server_data)){
                        $data['server_url'] = $server_data['url'];
                        $data['server_token'] = $server_data['api_token'];
                    }
                }
                if (!isset($data['server_token'])) {
                    $data['server_token'] = $this->getConf('redissue.API');
                }
                if (!isset($data['server_url'])) {
                    $data['server_url'] = $this->getConf('redissue.url');
                }
                
                // Issue Id
                preg_match("/id *= *(['\"])#(\\d+)\\1/", $match, $id);
                if( count($id) != 0 ) {
                    $data['id'] = $id[2];
                } else {
                    return array(
                            'state'=>$state,
                            'error'=>true,
                            'text'=>'<p style="color: red;">REDISSUE plugin: option "id" must be filled ! </p>',
                        );
                }
                // Title
                preg_match("/title *= *(['\"])(.*?)\\1/", $match, $title);
                if( count($title) != 0 ) {
                    $data['title'] = $title[2];
                }
                // Short
                $data['short'] = $this->getConf('redissue.short');
                preg_match("/short *= *(['\"])([0-1])\\1/", $match, $over_short);
                if( $over_short ){
                    $data['short'] = $over_short[2];
                }
                // Text Access
                preg_match("/text *= *(['\"])(.*?)\\1/", $match, $text);
                if( count($text) != 0 ) {
                    $data['text'] = $text[2];
                }
                // Project Id
                preg_match("/project *= *(['\"])(.*?)\\1/", $match, $project);
                if( count($project) != 0 ) {
                    $data['project_id'] = $project[2];
                }
                // Tracker Id
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

    // Dokuwiki Renderer
    function render($mode, Doku_Renderer $renderer, $data) {	
        if($mode != 'xhtml') return false;

        if($data['error']) {
            $renderer->doc .= $data['text'];
            return true;
        }
        $renderer->info['cache'] = false;
        switch($data['state']) {
            case DOKU_LEXER_SPECIAL:
                $this->renderRedissue($renderer, $data);
                break;
            case DOKU_LEXER_EXIT:
                $renderer->doc .= '</div></div>';
            case DOKU_LEXER_ENTER:
            case DOKU_LEXER_UNMATCHED:
                $renderer->doc .= $renderer->_xmlEntities($data['text']);
                break;
        }
        return true;
    }

    function renderRedissue($renderer, $data) {
        $redmine = new DokuwikiRedmine();
        if(empty($data['server_token'])){
            $this->renderIssueLink($renderer, $data, $data['text']);
        } else {
            $url = $data['server_url'];
            $redmine->connect($url, $data['server_token']);
            // Get Id user of the Wiki if Impersonate
            if ($this->getConf('redissue.view') == self::RI_IMPERSONATE) {
                $redUser = $_SERVER['REMOTE_USER'];
                // Attempt to collect information with this user
                $redmine->client->setImpersonateUser($redUser);
            }
            if(array_key_exists('project_id', $data) && array_key_exists('tracker_id', $data)) {
                $issues = $redmine->client->issue->all([
                    'project_id' => $data['project_id'],
                    'tracker_id' => $data['tracker_id']
                ]);
                if(isset($issues['issues'])) {
                    for ($i = 0; $i < count($issues['issues']); $i++) {
                        $data['id'] = $issues['issues'][$i]['id'];
                        $this->displayIssue($renderer, $data, $redmine);
                    }
                } else {
                    $renderer->doc .= '<p style="color: red;">REDISSUE plugin: "project" ID or "tracker" ID is invalid ! Redissue display single issue instead !</p>';
                    $this->displayIssue($renderer, $data, $redmine);
                }
            } else {
                $this->displayIssue($renderer, $data, $redmine);
            }
        }
    }

    function isBootstrap() {
        $bootstrap = False;
        if ($this->getConf('redissue.theme') == 8){
            $bootstrap = True;
        }
        return $bootstrap; 
    }

    function renderIssueLink($renderer, $data, $subject) {
        // Check if user override title.
        if($data['title']) {
            $cur_title = $data['title'];
        }else{
            $cur_title = '[#'.$data['id'].'] ' . $subject;
        }
        if ($this->isBootstrap()){
            $renderer->doc .= '<a title="'.$this->getLang('redissue.link.issue').'" href="' . $this->getIssueUrl($data['id'], $data) . '"><img src="' . $this->getImg() . '" class="redissue"/></a>';
            $renderer->doc .= '<a class="btn btn-primary redissue" role="button" data-toggle="collapse" href="#collapse-'.$data['id'].'" aria-expanded="false" aria-controls="collapse-'.$data['id'].'">';
            $renderer->doc .= $cur_title;
            $renderer->doc .= '</a> ';
            $renderer->doc .= '<div class="collapse" id="collapse-'.$data['id'].'">';
        } else {
            $renderer->doc .= '<a title="'.$this->getLang('redissue.link.issue').'" href="' . $this->getIssueUrl($data['id'], $data) . '"><img src="' . $this->getImg() . '" class="redissue"/>';
            $renderer->doc .= $cur_title;
            $renderer->doc .= '</a> ';
        }
    }

    function getIssueUrl($id, $data) {
        return $data['server_url'].'/issues/'.$id;
    }
    
    function getImg() {
        // If empty (False) get the second part
        return $this->getConf('redissue.img') ?: 'lib/plugins/redissue/images/redmine.png' ;
    }

    function displayIssue($renderer, $data, $redmine) {
        $issue = $redmine->getIssue($data['id']);
        $url = $data['server_url'];

        if($issue == 'Syntax error') {
            $renderer->doc .= '<p style="color: red;">REDISSUE plugin: Server exist in JSON config but seems not valid ! Please check your <b>url</b> or your <b>API Key</b> !</p>';
        // If rights
        } elseif (isset($issue['issue'])) {
            // ISSUE INFOS
            $project = $issue['issue']['project'];
            $project_identifier = $redmine->getProjectIdentifier($project['name']);
            $tracker = $issue['issue']['tracker'];
            $status = $issue['issue']['status']['name'];
            $author = $issue['issue']['author'];
            $assigned = $issue['issue']['assigned_to'];
            $subject = $issue['issue']['subject'];
            $description = $issue['issue']['description'];
            $done_ratio = $issue['issue']['done_ratio'];
            // RENDER ISSUE LINK
            $isClosed = $redmine->getIsClosedValue($issue['issue']['status']['id']);
            $renderer->doc .= '<p>';
            $this->renderIssueLink($renderer, $data, $subject);
 
            // GENERAL RENDERER 
            $priority = $issue['issue']['priority'];
            $color_prio = $redmine->getPriorityColor($priority['id']);
            if(!$isClosed){
                if($this->isBootstrap()){
                    $renderer->doc .= ' <span class="label label-success">' . $status . '</span>';
                }else{
                $renderer->doc .= ' <span class="badge-prio color-'.$color_prio.'">'.$priority['name'].'</span>';
                $renderer->doc .= ' <span class="badge-prio tracker">'. $tracker['name'].'</span>';
                    $renderer->doc .= ' <span class="badge-prio open">' . $status . '</span>';
                }
            } else {
                if($this->isBootstrap()){
                    $renderer->doc .= ' <span class="label label-default">' . $status . '</span>';
                }else{
                    $renderer->doc .= ' <span class="badge-prio closed">' . $status . '</span>';
                }
            }
            
            if($this->isBootstrap()) {
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
            } else {
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
            $this->renderIssueLink($renderer, $data, $data['text']);
        }
    }

}
