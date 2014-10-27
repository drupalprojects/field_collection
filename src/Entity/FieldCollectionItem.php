<?php

/**
 * @file
 * Definition of \Drupal\field_collection\Entity\FieldCollectionItem.
 */

namespace Drupal\field_collection\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Language\Language;

/**
 * Defines the field collection item entity class.
 *
 * @ContentEntityType(
 *   id = "field_collection_item",
 *   label = @Translation("Field Collection Item"),
 *   bundle_label = @Translation("Field Name"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "access" = "Drupal\field_collection\FieldCollectionItemAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\field_collection\FieldCollectionItemForm",
 *       "edit" = "Drupal\field_collection\FieldCollectionItemForm",
 *       "delete" = "Drupal\field_collection\Form\FieldCollectionItemDeleteForm"
 *     },
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
 *   field_ui_base_route = "entity.field_collection.edit_form",
 *   permission_granularity = "bundle",
 *   links = {
 *     "admin-form" = "field_collection.edit",
 *     "canonical" = "field_collection_item.view"
 *   }
 * )
 */
class FieldCollectionItem extends ContentEntityBase {

  /**
   * The id of the host entity.
   *
   * TODO: Possibly convert it to a FieldInterface.
   */
  protected $host_id;

  /**

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->item_id->value;
  }

  /**
   * Save the field collection item.
   *
   * By default, always save the host entity, so modules are able to react
   * upon changes to the content of the host and any 'last updated' dates of
   * entities get updated.
   *
   * For creating an item a host entity has to be specified via setHostEntity()
   * before this function is invoked. For the link between the entities to be
   * fully established, the host entity object has to be updated to include a
   * reference on this field collection item during saving. So do not skip
   * saving the host for creating items.
   *
   * @param $skip_host_save
   *   (internal) If TRUE is passed, the host entity is not saved automatically
   *   and therefore no link is created between the host and the item or
   *   revision updates might be skipped. Use with care.
   */
  public function save($skip_host_save = FALSE) {
    /* TODO
    // Make sure we have a host entity during creation.
    if (!empty($this->is_new) && !(isset($this->hostEntityId) || isset($this->hostEntity) || isset($this->hostEntityRevisionId))) {
      throw new Exception("Unable to create a field collection item without a given host entity.");
    }
    */

    // Only save directly if we are told to skip saving the host entity. Else,
    // we always save via the host as saving the host might trigger saving
    // field collection items anyway (e.g. if a new revision is created).
    if ($skip_host_save) {
      return parent::save();
    }
    /* TODO: Need this.
    else {
      $host_entity = $this->hostEntity();
      if (!$host_entity) {
        throw new Exception("Unable to save a field collection item without a valid reference to a host entity.");
      }
      // If this is creating a new revision, also do so for the host entity.
      if (!empty($this->revision) || !empty($this->is_new_revision)) {
        $host_entity->revision = TRUE;
        if (!empty($this->default_revision)) {
          entity_revision_set_default($this->hostEntityType, $host_entity);
        }
      }
      // Set the host entity reference, so the item will be saved with the host.
      // @see field_collection_field_presave()
      $delta = $this->delta();
      if (isset($delta)) {
        $host_entity->{$this->field_name}[$this->langcode][$delta] = array('entity' => $this);
      }
      else {
        $host_entity->{$this->field_name}[$this->langcode][] =  array('entity' => $this);
      }
      return entity_save($this->hostEntityType, $host_entity);
    }
    */

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
  public function setHostEntity($entity, $create_link = TRUE) {
    if ($this->isNew()) {
      $this->host_type = $entity->getEntityTypeId();
      $this->host_id = $entity->id();
      $this->host_entity = $entity;

      // If the host entity is not saved yet, set the id to FALSE. So
      // fetchHostDetails() does not try to load the host entity details.
      if (!isset($this->host_id)) {
        $this->host_id = FALSE;
      }
      /*
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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['item_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Field collection item ID'))
      ->setDescription(t('The field collection item ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['host_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t("Host's entity type"))
      ->setDescription(t("Type of entity for the field collection item's host."))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The field collection item UUID.'))
      ->setReadOnly(TRUE);

    $fields['revision_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Revision ID'))
      ->setDescription(t('The field collection item revision ID.'))
      ->setReadOnly(TRUE);

    $fields['field_name'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The field collection item field.'))
      ->setSetting('target_type', 'field_collection')
      ->setReadOnly(TRUE);

    return $fields;
  }

  /**
   * Determines whether a field collection item entity is empty based on the
   * collection-fields.
   */
  function isEmpty() {
    $is_empty = TRUE;

    foreach ($this->getIterator() as $field) {
      // Only check configured fields, skip base fields like uuid.
      if (!$field->isEmpty() && 'Drupal\\field\\Entity\\FieldConfig' == get_class($field->getFieldDefinition())) {
        $is_empty = FALSE;
      }
    }

    // TODO: Allow other modules a chance to alter the value before returning?
    //drupal_alter('field_collection_is_empty', $is_empty, $this);

    return $is_empty;
  }

}
