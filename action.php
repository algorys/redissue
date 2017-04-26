<?php
/**
 * Redissue Action Plugin: Inserts a button into the toolbar
 *
 * @author Algorys
 */

if (!defined('DOKU_INC')) die();

class action_plugin_redissue extends DokuWiki_Action_Plugin {

    /**
     * Register the eventhandlers
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array ());
    }

    /**
     * Inserts the toolbar button
     */
    public function insert_button(Doku_Event $event, $param) {
        $syntax = array (
            'Single Issue'   => array(
                'icon' => '../../plugins/redissue/images/issue_single.png',
                'open'   => '<redissue id="',
                'close'  => '" />',
                'sample' => 'ISSUE_ID'
            ),
            'Multiple Issue' => array(
                'icon' => '../../plugins/redissue/images/issue_multiple.png',
                'open'   => '<redissue project="PROJECT_ID" tracker="',
                'close'  => '" />',
                'sample' => 'TRACKER_ID'
            ),
        );

        $redissue = array (
            'type' => 'picker',
            'title' => $this->getLang('redissue.button'),
            'icon' => '../../plugins/redissue/images/redmine.png',
            'list' => array(),
        );

        foreach ($syntax as $syntax_name => $syntax_data) {
            $redissue['list'] [] = array(
                'type' => 'format',
                'title' => $syntax_name,
                'icon' => $syntax_data['icon'],
                'open' => $syntax_data['open'],
                'close' => $syntax_data['close'],
                'sample' => $syntax_data['sample'],
            );
        }

        $event->data[] = $redissue;
    } // insert_button
}
