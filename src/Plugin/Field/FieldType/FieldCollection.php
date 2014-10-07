<?php

/**
 * @file
 * Contains \Drupal\field_collection\Plugin\Field\FieldType\FieldCollectionItem.
 */

namespace Drupal\field_collection\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

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
class FieldCollection extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field) {
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
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(t('Field collection item ID'))
      ->setSetting('unsigned', TRUE)
      ->setReadOnly(TRUE);

    return $properties;
  }

  public function getFieldCollectionItem($create = FALSE) {
    if (isset($this->field_collection_item)) {
      return $this->field_collection_item;
    }
    elseif (isset($this->value)) {
      // By default always load the default revision, so caches get used.
      $field_collection_item = field_collection_item_load($this->value);
      if ($field_collection_item->getRevisionId() != $this->revision_id) {
        // A non-default revision is a referenced, so load this one.
        $field_collection_item =
          field_collection_item_revision_load($this->revision_id);
      }
      return $field_collection_item;
    }
    elseif ($create) {
      $field_collection_item =
        entity_create('field_collection_item',
                      array('field_name' => $field_name));
      return $field_collection_item;
    }
    return FALSE;
  }

  public function delete() {
    $this->getFieldCollectionItem()->delete();
    parent::delete();
  }

  /**
   * Support saving field collection items in @code
   * $field->field_collection_item @endcode.  This may be used to seamlessly
   * create field collection items during host-entity creation or to save
   * changes to the host entity and its collections at once.
   */
  public function preSave() {
    /*
    if (isset($this->field_collection_item)) {
      $this->value = $this->field_collection_item->id();
    }
    */

    // TODO: Restore this functionality from the original field_presave hook

    // In case the entity has been changed / created, save it and set the id.
    // If the host entity creates a new revision, save new item-revisions as
    // well.
    if (isset($this->field_collection_item) ||
        $this->getEntity()->isNewRevision())
    {
      if ($fc_item = $this->getFieldCollectionItem()) {
        if ($fc_item->isNew()) {
          $fc_item->setHostEntity(
            $this->getEntity()->getEntityTypeId(), $this->getEntity(), FALSE);
          $fc_item->save();
        }

        /*
        // If the host entity is saved as new revision, do the same for the item.
        if (!empty($host_entity->revision)) {
          $fc_item->revision = TRUE;
          $is_default = entity_revision_is_default($host_entity_type, $host_entity);
          // If an entity type does not support saving non-default entities,
          // assume it will be saved as default.
          if (!isset($is_default) || $is_default) {
            $fc_item->default_revision = TRUE;
            $fc_item->archived = FALSE;
          }
        }
        */

        $this->value = $fc_item->id();
        $this->revision_id = $fc_item->getRevisionId();
      }
    }
  }
}
