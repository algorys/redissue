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
        $event->data[] = array (
            'type' => 'format',
            'title' => $this->getLang('redissue.button'),
            'icon' => '../../plugins/redissue/images/redmine.png',
            'open' => ' <redissue id="#issue" text="text_if_no_rights_or_API">',
            'close' => '</redissue>',
        );
    } // insert_button
}
