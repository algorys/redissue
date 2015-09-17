<?php
/**
 * Redissue Syntax Plugin: Insert a link to redmine's issue
 *
 * @author Algorys
 */

if (!defined('DOKU_INC')) die();
require 'vendor/php-redmine-api/lib/autoload.php';


class syntax_plugin_redissue extends DokuWiki_Syntax_Plugin {
    const RI_IMPERSONATE = 4;

    // Get url of redmine
    function _getIssueUrl($id) {
	    return $this->getConf('redissue.url').'/issues/'.$id;
    }
    
    function _getImgName() {
        // If empty (False) get the second part
        return $this->getConf('redissue.img') ?: 'lib/plugins/redissue/images/redmine.png' ;
    }

    public function getType() {
        return 'container';
    }
    /**
     * @return string Paragraph type
     */

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
        $this->Lexer->addEntryPattern('<redissue[^>]*>(?=.*</redissue>)', $mode,'plugin_redissue');
    }
    function postConnect() {
        $this->Lexer->addExitPattern('</redissue>', 'plugin_redissue');
    }
    // Do the regexp
    function handle($match, $state, $pos, $handler) {
        switch($state){
            case DOKU_LEXER_SPECIAL :
            case DOKU_LEXER_ENTER :
                $data = array(
                        'state'=>$state,
                        'id'=> 0,
                        'text'=>$this->getLang('redissue.text.default')
                    );
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
                // Looking for text link
                preg_match("/text *= *(['\"])(.*?)\\1/", $match, $text);
                if( count($text) != 0 ) {
                    $data['text'] = $text[2];
                }

                return $data;
            case DOKU_LEXER_UNMATCHED :
                return array('state'=>$state, 'text'=>$match);
            default:
                return array('state'=>$state, 'bytepos_end' => $pos + strlen($match));
        }
    }

    function _render_custom_link($renderer, $data, $title, $cssClass) {
        $renderer->doc .= '<img src="' . $this->_getImgName($data['img']) . '" class="redissue"/> <a href="' . $this->_getIssueUrl($data['id']) . '" class="redissue '.$cssClass.'">' . $title . '</a>';
    }

    function _render_default_link($renderer, $data) {
        $this->_render_custom_link($renderer, $data, sprintf($data['text'], $data['id']));
    }
    
    // Main render_link
    function _render_link($renderer, $data) {
        $apiKey = ($this->getConf('redissue.API'));
        if(empty($apiKey)){
            $this->_render_default_link($renderer, $data);
        } else {
            $url = $this->getConf('redissue.url');
            $client = new Redmine\Client($url, $apiKey);
            // Get Id user of the Wiki if Impersonate
            $view = $this->getConf('redissue.view');
            if ($view == self::RI_IMPERSONATE) {
                $INFO = pageinfo();
                $redUser = $INFO['userinfo']['uid'];
                // Attempt to collect information with this user
                $client->setImpersonateUser($redUser);
            }
            $issue = $client->api('issue')->show($data['id']);
            if($issue) {
                // Get the Id Status
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
                // If isClosed not empty, change css
                $cssClass = $isClosed ? 'redissue-status-closed' : 'redissue-status-open';
                // Get other issue info
                $subject = $issue['issue']['subject'];
                $status = $issue['issue']['status']['name'];
                $this->_render_custom_link($renderer, $data, "[#" . $data['id'] . "][" . $status . "] " . $subject, $cssClass);
            } else {
                $this->_render_default_link($renderer, $data);
            }
        }
    }
    // Dokuwiki Renderer
    function render($mode, $renderer, $data) {	
        if($mode != 'xhtml') return false;

        if($data['error']) {
            $renderer->doc .= $data['text'];
            return true;
        }
        switch($data['state']) {
            case DOKU_LEXER_SPECIAL :
                $this->_render_link($renderer, $data);
                break;
            case DOKU_LEXER_ENTER :
                $this->_render_link($renderer, $data);
                $renderer->doc .= '<div class="redissue">';
                break;
            case DOKU_LEXER_EXIT:
                $renderer->doc .= '</div>';
            case DOKU_LEXER_UNMATCHED :
                $renderer->doc .= $renderer->_xmlEntities($data['text']);
                break;
        }
        return true;
    }
}
