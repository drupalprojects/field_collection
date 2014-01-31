<?php

/**
 * @file
 * Definition of \Drupal\field_collection\Entity\FieldCollectionItem.
 */

namespace Drupal\field_collection\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Language\Language;

/**
 * Defines the field collection item entity class.
 *
 * @EntityType(
 *   id = "field_collection_item",
 *   label = @Translation("Field Collection Item"),
 *   bundle_label = @Translation("Field Name"),
 *   controllers = {
 *     "storage" = "Drupal\field_collection\FieldCollectionItemStorageController",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "access" = "Drupal\field_collection\FieldCollectionItemAccessController",
 *     "form" = {
 *       "default" = "Drupal\field_collection\FieldCollectionItemFormController",
 *       "edit" = "Drupal\field_collection\FieldCollectionItemFormController",
 *       "delete" = "Drupal\field_collection\Form\FieldCollectionItemDeleteForm"
 *     }
 *   },
 *   base_table = "field_collection_item",
 *   revision_table = "field_collection_item_revision",
 *   fieldable = TRUE,
 *   translatable = FALSE,
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "item_id",
 *     "revision" = "revision_id",
 *     "bundle" = "field_name",
 *     "label" = "field_name",
 *     "uuid" = "uuid"
 *   },
 *   bundle_keys = {
 *     "bundle" = "field_name"
 *   },
 *   bundle_entity_type = "field_collection",
 *   permission_granularity = "bundle",
 *   links = {
 *     "admin-form" = "field_collection.edit",
 *     "canonical" = "field_collection_item.view"
 *   }
 * )
 */
class FieldCollectionItem extends ContentEntityBase {

 /**
  * @todo uuid upgrade path
  * @todo rename item_id => id upgrade path
  * @todo add langcode column to database
  * @todo form controller
  * @todo render controller
  * @todo storage controller
  * @todo access controller
  * @todo translation controller
  */  

  /**
   * The id of the host entity.
   *
   * TODO: Possibly convert it to a FieldInterface.
   */
  protected $host_id;

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->item_id->value;
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    if (!isset($this->revision_id->value)) {
      $this->revision_id->value = 0;
    }

    return parent::save();
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    if ($this->getHost()) {
      $this->deleteHostEntityReference();
    }
    parent::delete();
  }
  
  /**
   * Deletes the host entity's reference of the field collection item.
   */
  protected function deleteHostEntityReference() {
    $delta = $this->getDelta();
    if ($this->id() && isset($delta) &&
        NULL !== $this->getHost(TRUE) &&
        isset($this->getHost()->{$this->field_name->value}[$delta]))
    {
      unset($this->getHost()->{$this->field_name->value}[$delta]);
      $this->getHost()->save();
    }
  }

  /**
   * Overrides \Drupal\Core\Entity\Entity::createDuplicate().
   */
  public function createDuplicate() {
    $duplicate = parent::createDuplicate();
    $duplicate->revision_id->value = NULL;
    $duplicate->id->value = NULL;
    return $duplicate;
  }

  /**
   * Overrides \Drupal\Core\Entity\Entity::getRevisionId().
   */
  public function getRevisionId() {
    return $this->revision_id->value;
  }
  
  /**
   * Determines the $delta of the reference pointing to this field collection
   * item.
   */
  public function getDelta() {
    $host = $this->getHost();
    if (($host = $this->getHost()) && isset($host->{$this->field_name->value})) {
      foreach ($host->{$this->field_name->value} as $delta => $item) {
        if (isset($item->value) && $item->value == $this->id()) {
          return $delta;
        }
        elseif (isset($item->field_collection_item) &&
                $item->field_collection_item === $this)
        {
          return $delta;
        }
      }
    }
  }

  /**
   * Overrides \Drupal\Core\Entity\Entity::uri().
   */
  public function uri() {
    $ret = array(
      'path' => 'field-collection-item/' . $this->id(),
      'options' => array(
        'entity_type' => $this->entityType,
        'entity' => $this,
      )
    );

    return $ret;
  }

  /**
   * Returns the host entity of this field collection item.
   */
  public function getHost($reset = FALSE) {
    $entity_info = \Drupal::entityManager()
                   ->getDefinition($this->host_type->value, TRUE);
    if (NULL !== $entity_info->get('base_table')) {
      return entity_load($this->host_type->value, $this->getHostId(), $reset);
    } else {
      return NULL;
    }
  }

  /**
   * Returns the id of the host entity for this field collection item.
   */
  public function getHostId() {
    if (!isset($this->host_id)) {
      $entity_info = \Drupal::entityManager()
                     ->getDefinition($this->host_type->value, true);
      $host_id_results = db_query(
        "SELECT `entity_id` " .
        "FROM {" . $entity_info->get('base_table') .
               "__" . $this->bundle() . "} " .
        "WHERE `" . $this->bundle() . "_value` = " . $this->id())
          ->fetchCol();
      $this->host_id = reset($host_id_results);
    }

    return $this->host_id;
  }

  /**
   * Sets the host entity. Only possible during creation of a item.
   *
   * @param $create_link
   *   (optional) Whether a field-item linking the host entity to the field
   *   collection item should be created.
   */
  public function setHostEntity($entity_type, $entity, $create_link = TRUE) {
    if ($this->isNew()) {
      if ($entity->isNew()) {
        $entity->save();
      }

      $this->host_type = $entity_type;
      $this->host_id = $entity->id();
      //$this->save();

      /*
      // If the host entity is not saved yet, set the id to FALSE. So
      // fetchHostDetails() does not try to load the host entity details.
      if (!isset($this->hostEntityId)) {
        $this->hostEntityId = FALSE;
      }
      // We are create a new field collection for a non-default entity, thus
      // set archived to TRUE.
      if (!entity_revision_is_default($entity_type, $entity)) {
        $this->hostEntityId = FALSE;
        $this->archived = TRUE;
      }
      */

      // TODO: Generate a message if attempting to add a value to a full limited
      // field
      if ($create_link) {
        $entity->{$this->bundle()}[] = array('field_collection_item' => $this);
        $entity->save();
      }
    }
    else {
      throw new \Exception('The host entity may be set only during creation ' .
                           'of a field collection item.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $fields['item_id'] = FieldDefinition::create('integer')
      ->setLabel(t('Field collection item ID'))
      ->setDescription(t('The field collection item ID.'))
      ->setReadOnly(TRUE);

    $fields['host_type'] = FieldDefinition::create('string')
      ->setLabel(t("Host's entity type"))
      ->setDescription(t("Type of entity for the field collection item's host."))
      ->setReadOnly(TRUE);

    /* TODO
    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The field collection item UUID.'))
      ->setReadOnly(TRUE);
    */

    $fields['revision_id'] = FieldDefinition::create('integer')
      ->setLabel(t('Revision ID'))
      ->setDescription(t('The field collection item revision ID.'))
      ->setReadOnly(TRUE);

    $fields['field_name'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The field collection item field.'))
      ->setSetting('target_type', 'field_collection')
      ->setReadOnly(TRUE);

    return $fields;
  }
}
