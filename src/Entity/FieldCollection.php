<?php

/**
 * @file
 * Contains \Drupal\field_collection\Entity\FieldCollection.
 */

namespace Drupal\field_collection\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the Field collection configuration entity.
 *
 * @ConfigEntityType(
 *   id = "field_collection",
 *   label = @Translation("Field collection"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigEntityStorage",
 *     "access" = "Drupal\field_collection\FieldCollectionAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\field_collection\FieldCollectionFormController",
 *       "edit" = "Drupal\field_collection\FieldCollectionFormController",
 *       "delete" = "Drupal\field_collection\Form\FieldCollectionDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\field_collection\FieldCollectionListBuilder",
 *   },
 *   admin_permission = "administer content types",
 *   config_prefix = "field_collection",
 *   bundle_of = "field_collection_item",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "field_collection.edit"
 *   }
 * )
 */
class FieldCollection extends ConfigEntityBundleBase implements ConfigEntityInterface, EntityInterface {

  /**
   * The machine name of this field collection.
   *
   * @var string
   */
  public $id;

  /**
   * The UUID of the node type.
   *
   * @var string
   */
  public $uuid;

  /**
   * The human-readable name of the field collection.
   *
   * @var string
   */
  public $label;

  public function __construct(array $values = array(),
                              $entity_type = 'field_collection')
  {
    parent::__construct($values, $entity_type);
    $this->entityType = "field_collection";
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage_controller, $update = TRUE) {
    parent::postSave($storage_controller, $update);

    if (!$update) {
      entity_invoke_bundle_hook('create', 'field_collection_item', $this->id());
    }
  }
}
