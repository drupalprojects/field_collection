<?php

/**
 * @file
 * Contains \Drupal\field_collection\Plugin\Core\Entity\FieldCollectionItem.php
 */

namespace Drupal\field_collection\Plugin\Core\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityNG;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines the field collection item entity class.
 *
 * @Plugin(
 *   id = "field_collection_item",
 *   label = @Translation("Field Collection Item"),
 *   bundle_label = @Translation("Field Name"),
 *   module = "field_collection",
 *   controller_class = "Drupal\field_collection\FieldCollectionItemStorageController",
 *   access_controller_class = "Drupal\field_collection\FieldCollectionItemAccessController",
 *   render_controller_class = "Drupal\field_collection\FieldCollectionItemRenderController",
 *   form_controller_class = {
 *     "default" = "Drupal\field_collection\FieldCollectionItemFormController"
 *   },
 *   base_table = "field_collection_item",
 *   revision_table = "field_collection_item_revision",
 *   fieldable = TRUE,
 *   translatable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "bundle" = "field_name",
 *     "label" = "field_name",
 *     "uuid" = "uuid"
 *   },
 *   bundle_keys = {
 *     "bundle" = "type"
 *   }
 * )
 */
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

class FieldCollectionItem extends EntityNG implements ContentEntityInterface {

  /**
   * The field collection item ID.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $id;

  /**
   * The field collection item revision ID.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $revision_id;

  /**
   * Indicates whether this is the default revision.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $isDefaultRevision = TRUE;

  /**
   * The item UUID.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $uuid;

  /**
   * The field name (bundle).
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $field_name;

  /**
   * The block language code.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $langcode;

  /**
   * The block description.
   *
   * @var \Drupal\Core\Entity\Field\FieldInterface
   */
  public $info;

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->id->value;
  }

  /**
   * Implements Drupal\Core\Entity\EntityInterface::bundle().
   */
  public function bundle() {
    return $this->type->value;
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
   * Initialize the object. Invoked upon construction and wake up.
   */
  protected function init() {
    parent::init();
    // We unset all defined properties so magic getters apply.
    unset($this->id);
    unset($this->revision_id);
    unset($this->uuid);
    unset($this->field_name);
    unset($this->new);
  }

  /**
   * Overrides \Drupal\Core\Entity\Entity::uri().
   */
  public function uri() {
    return array(
      'path' => 'field-collection/' . $this->id(),
      'options' => array(
        'entity_type' => $this->entityType,
        'entity' => $this,
      )
    );
  }
}
