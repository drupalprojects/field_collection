<?php

/**
 * @file
 * Contains \Drupal\field_collection\Controller\FieldCollectionItemController.
 */

namespace Drupal\field_collection\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\field_collection\Entity\FieldCollection;
use Drupal\field_collection\Entity\FieldCollectionItem;

/**
 * Returns responses for Field collection item routes.
 */
class FieldCollectionItemController extends ControllerBase {

  /**
   * Provides the node submission form.
   *
   * @param \Drupal\field_collection\Entity\FieldCollection $field_collection
   *   The field_collection entity for the node.
   *
   * @param $host_type
   *   The type of the entity hosting the field collection item.
   *
   * @param $host_id
   *   The id of the entity hosting the field collection item.
   *
   * @return array
   *   A field collection item submission form.
   *
   * TODO: additional fields
   */
  public function add(FieldCollection $field_collection, $host_type, $host_id) {
    $field_collection_item = $this->entityManager()->getStorageController('field_collection_item')->create(array(
      'field_name' => $field_collection->type,
      'host_type' => $host_type,
      'revision_id' => 0, // TODO: set this correctly
    ));

    $form = $this->entityManager()->getForm($field_collection_item);
    return $form;
  }

  /**
   * Displays a field collection item.
   *
   * @param \Drupal\field_collection\Entity\FieldCollectionItem $field_collection_item
   *   The field collection item we are displaying.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function page(FieldCollectionItem $field_collection_item) {
    $build = $this->buildPage($field_collection_item);
    return $build;
  }

  /**
   * Builds a field collection item page render array.
   *
   * @param \Drupal\field_collection\Entity\FieldCollectionItem $field_collection_item
   *   The field collection item we are displaying.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  protected function buildPage(FieldCollectionItem $field_collection_item) {
    $ret = array('field_collection_items' => $this->entityManager()->getViewBuilder('field_collection_item')->view($field_collection_item));
    return $ret;
  }

}
