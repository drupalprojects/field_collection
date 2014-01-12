<?php

/**
 * @file
 * Contains \Drupal\field_collection\Plugin\Field\FieldType\FieldCollectionItem.
 */

namespace Drupal\field_collection\Plugin\Field\FieldType;

use Drupal\Core\Field\ConfigFieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the 'field_collection' field type.
 *
 * @FieldType(
 *   id = "field_collection",
 *   label = @Translation("Field collection"),
 *   description = @Translation("This field stores references to embedded entities, which itself may contain any number of fields."),
 *   settings = {
 *     "path" = "",
 *     "hide_blank_items" = TRUE,
 *   },
 *   instance_settings = {
 *   },
 *   default_widget = "field_collection_embed",
 *   default_formatter = "field_collection_list"
 * )
 */
class FieldCollection extends ConfigFieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldDefinitionInterface $field) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'int',
          'not null' => TRUE
        ),
        'revision_id' => array(
          'type' => 'int',
          'not null' => FALSE
        ),
      ),
    );
  }
}
