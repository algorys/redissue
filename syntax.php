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
        $this->Lexer->addSpecialPattern('<redissue[^>]*/>', $mode,'plugin_redissue');
    }

    function handle($match, $state, $pos, $handler) {
        switch($state){
            case DOKU_LEXER_SPECIAL :
                $data = array(
                        'state'=>$state,
                        'id'=> 0,
                        'text'=> 'valeur par défaut'
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

    function render($mode, $renderer, $data) {	
        if($mode != 'xhtml') return false;

        $redurl = $this->geturl();
        $redurl = $redurl.$data['id'];
        switch($data['state']) {
            case DOKU_LEXER_SPECIAL :
                if($data['error']) {
                    $renderer->doc .= $data['text'];
                } else {
                    $renderer->doc .= '<a href=" ' . $redurl . '">' . $data['text'] . '</a>';
                }
            case DOKU_LEXER_UNMATCHED :
                break;
        }
        return true;
    }
}
