<?php

/**
 * @file
 * Contains \Drupal\field_collection\Entity\FieldCollection.
 */

namespace Drupal\field_collection\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Defines the Field collection configuration entity.
 *
 * @EntityType(
 *   id = "field_collection",
 *   label = @Translation("Field collection"),
 *   controllers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigStorageController",
 *     "access" = "Drupal\field_collection\FieldCollectionAccessController",
 *     "form" = {
 *       "add" = "Drupal\field_collection\FieldCollectionFormController",
 *       "edit" = "Drupal\field_collection\FieldCollectionFormController",
 *       "delete" = "Drupal\field_collection\Form\FieldCollectionDeleteConfirm"
 *     },
 *     "list" = "Drupal\field_collection\FieldCollectionListController",
 *   },
 *   admin_permission = "administer content types",
 *   config_prefix = "field_collection",
 *   bundle_of = "field_collection_item",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "field_collection.edit"
 *   }
 * )
 */
class FieldCollection extends ConfigEntityBase implements ConfigEntityInterface {

  /**
   * The machine name of this field collection.
   *
   * @var string
   *
   * @todo Rename to $id.
   */
  public $type;

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
   *
   * @todo Rename to $label.
   */
  public $name;

  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    $this->entityType = "field_collection";
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->type;
  }

    /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageControllerInterface $storage_controller, $update = TRUE) {
    parent::postSave($storage_controller, $update);

    if (!$update) {
      entity_invoke_bundle_hook('create', 'field_collection_item', $this->id());
    }
  }
}
