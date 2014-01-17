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

  /**
   * Support saving field collection items in @code $item['entity'] @endcode.
   * This may be used to seamlessly create field collection items during
   * host-entity creation or to save changes to the host entity and its
   * collections at once.
   */
  public function preSave() {
    if (isset($this->entity)) {
      $this->value = $this->entity->id();
    }

    // TODO: Restore this functionality from the original field_presave hook

    // In case the entity has been changed / created, save it and set the id.
    // If the host entity creates a new revision, save new item-revisions as
    // well.
    /*
    if (isset($item['entity']) || !empty($host_entity->revision)) {

      if ($entity = field_collection_field_get_entity($item)) {

        if (!empty($entity->is_new)) {
          $entity->setHostEntity($host_entity_type, $host_entity, LANGUAGE_NONE, FALSE);
        }

        // If the host entity is saved as new revision, do the same for the item.
        if (!empty($host_entity->revision)) {
          $entity->revision = TRUE;
          $is_default = entity_revision_is_default($host_entity_type, $host_entity);
          // If an entity type does not support saving non-default entities,
          // assume it will be saved as default.
          if (!isset($is_default) || $is_default) {
            $entity->default_revision = TRUE;
            $entity->archived = FALSE;
          }
        }
        $entity->save(TRUE);

        $item = array(
          'value' => $entity->item_id,
          'revision_id' => $entity->revision_id,
        );
      }
    }
    */
  }
}
