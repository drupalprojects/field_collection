<?php

/**
 * @file
 * Field module functionality for the Field Collection module.
 */

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
