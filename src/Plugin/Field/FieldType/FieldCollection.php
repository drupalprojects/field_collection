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
                      array('field_name' => $this->getFieldDefinition()->field_name));

      /*
      $field_collection_item->setHostEntity(
        $this->getEntity()->getEntityTypeId(), $this->getEntity(), FALSE);
      */

      return $field_collection_item;
    }
    return FALSE;
  }

  public function delete() {
    $this->getFieldCollectionItem()->delete();
    parent::delete();
  }

  // TODO
  public function insert() {
    /*
      if ($entity = field_collection_field_get_entity($item)) {
        if (!empty($host_entity->is_new) && empty($entity->is_new)) {
          // If the host entity is new but we have a field_collection that is not
          // new, it means that its host is being cloned. Thus we need to clone
          // the field collection entity as well.
          $new_entity = clone $entity;
          $new_entity->item_id = NULL;
          $new_entity->revision_id = NULL;
          $new_entity->is_new = TRUE;
          $entity = $new_entity;
        }
      }
    */

    if (isset($this->field_collection_item) ||
        $this->getEntity()->isNewRevision())
    {
      if ($field_collection_item = $this->getFieldCollectionItem()) {
        if ($field_collection_item->isNew()) {
          $field_collection_item->setHostEntity(
            $this->getEntity()->getEntityTypeId(), $this->getEntity(), FALSE);
        }

        // TODO: Don't save empty field collection item.
        $field_collection_item->save(TRUE);
        $this->value = $field_collection_item->id();
        $this->revision_id = $field_collection_item->getRevisionId();
      }
    }
  }

  /**
   * TODO
   * Implements hook_field_update().
   *
   * Care about removed field collection items.
   * Support saving field collection items in @code $item['entity'] @endcode. This
   * may be used to seamlessly create field collection items during host-entity
   * creation or to save changes to the host entity and its collections at once.
   */
  public function update() {
    if (isset($this->field_collection_item) ||
        $this->getEntity()->isNewRevision())
    {
      if ($field_collection_item = $this->getFieldCollectionItem()) {
        if ($field_collection_item->isNew()) {
          $field_collection_item->setHostEntity(
            $this->getEntity()->getEntityTypeId(), $this->getEntity(), FALSE);
          $field_collection_item->save();
        }

        $this->value = $field_collection_item->id();
        $this->revision_id = $field_collection_item->getRevisionId();
      }
    }

    /*
    $items_original = !empty($host_entity->original->{$field['field_name']}[$langcode]) ? $host_entity->original->{$field['field_name']}[$langcode] : array();
    $original_by_id = array_flip(field_collection_field_item_to_ids($items_original));

    foreach ($items as &$item) {
      // In case the entity has been changed / created, save it and set the id.
      // If the host entity creates a new revision, save new item-revisions as
      // well.
      if (isset($item['entity']) || !empty($host_entity->revision)) {

        if ($entity = field_collection_field_get_entity($item)) {

          if (!empty($entity->is_new)) {
            $entity->setHostEntity($host_entity_type, $host_entity, LANGUAGE_NONE, FALSE);
          }

          // If the host entity is saved as new revision, do the same for the item.
          if (!empty($host_entity->revision)) {
            $entity->revision = TRUE;
            // Without this cache clear entity_revision_is_default will
            // incorrectly return false here when creating a new published revision
            if (!isset($cleared_host_entity_cache)) {
              list($entity_id) = entity_extract_ids($host_entity_type, $host_entity);
              entity_get_controller($host_entity_type)->resetCache(array($entity_id));
              $cleared_host_entity_cache = true;
            }
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
      unset($original_by_id[$item['value']]);
    }

    // If there are removed items, care about deleting the item entities.
    if ($original_by_id) {
      $ids = array_flip($original_by_id);

      // If we are creating a new revision, the old-items should be kept but get
      // marked as archived now.
      if (!empty($host_entity->revision)) {
        db_update('field_collection_item')
          ->fields(array('archived' => 1))
          ->condition('item_id', $ids, 'IN')
          ->execute();
      }
      else {
        // Delete unused field collection items now.
        foreach (field_collection_item_load_multiple($ids) as $un_item) {
          $un_item->updateHostEntity($host_entity);
          $un_item->deleteRevision(TRUE);
        }
      }
    }
    */
  }

  /**
   * {@inheritdoc}
   */
  function isEmpty() {
    if ($this->value) {
      return FALSE;
    }
    /*
    else if (isset($this->getFieldCollectionItem()) {
      return $this->getFieldCollectionItem()->isEmpty();
    }
    */
    return TRUE;
  }
}

