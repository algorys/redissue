<?php
/**
 * Redissue Syntax Plugin: Insert a link to redmine's issue
 *
 * @author Algorys
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_redissue extends DokuWiki_Syntax_Plugin {

    // Get url of redmine
    function geturl() {
	$redurl = $this->getConf('redmine.url');
	return $redurl.'/issues/';
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

    public function getSort() {
        return 198;
    }
 
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<redissue[^>]*>', $mode,'plugin_redissue');
    }

    function handle($match, $state, $pos, $handler) {
        error_log("redissue handle");
        switch($state){
            case DOKU_LEXER_SPECIAL :
                error_log("redissue dans lexer_enter");
                preg_match("/id *= *(['\"])#(\\d+)\\1/", $match, $id);
                $id = $id[2];
                $return = array(
                        'state'=>$state,
                        'id'=>$id
                    );
                error_log("redissue avec id = $id");
                return $return;
            case DOKU_LEXER_UNMATCHED :
                return array('state'=>$state, 'text'=>$match);
            default:
                return array('state'=>$state, 'bytepos_end' => $pos + strlen($match));
        }
    }

    function render($mode, $renderer, $data) {	
        error_log("redissue render");
        if($mode != 'xhtml') return false;

        error_log("redissue dans xhtml");
        $redurl = $this->geturl();
        $redurl = $redurl.$data['id'];
        switch($data['state']) {
            case DOKU_LEXER_SPECIAL :
                error_log("redissue ecrit le a");
                $renderer->doc .= '<a href=" ' . $redurl . '">test</a>';
            case DOKU_LEXER_UNMATCHED :
                break;
        }
        return true;
    }
}
