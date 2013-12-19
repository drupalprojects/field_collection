<?php

/**
 * @file
 * Contains \Drupal\field_collection\Plugin\Core\Entity\FieldCollection.php
 */

namespace Drupal\field_collection\Plugin\Core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityStorageControllerInterface;

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

}
