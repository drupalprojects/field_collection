<?php

/**
 * @file
 * Field module functionality for the Field Collection module.
 */

/**
 * Implements hook_field_widget_info_alter().
 *
 * Allow Inline Entity Form Widgets to be used with Field Collection fields.
 */
function field_collection_field_widget_info_alter(array &$info) {
  if (isset($info['inline_entity_form_simple'])) {
    $info['inline_entity_form_simple']['field_types'][] = 'field_collection';
  }
}

/**
 * Implements hook_field_formatter_info_alter().
 *
 * Allow any formatter that works with Entity References to be used with Field Collection.
 */
function field_collection_field_formatter_info_alter(&$info){
  foreach ($info as &$formatter) {
    if (isset($formatter['field_types'])) {
      if (in_array('entity_reference',$formatter['field_types'])
        && !in_array('field_collection',$formatter['field_types'])
        ) {
        $formatter['field_types'][] = 'field_collection';
      }
    }
  }
}
