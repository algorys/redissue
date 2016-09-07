<?php
/**
 * Metadata for configuration manager plugin
 * Additions for the redissue plugin
 */

$meta['redissue.url']   = array('string');
$meta['redissue.img']   = array('string');
$meta['redissue.theme'] = array('multichoice','_choices' => array(8,6));
$meta['redissue.API']   = array('string');
$meta['redissue.view']  = array('multichoice','_choices' => array(4,2));
$meta['redissue.short'] = array('onoff');
