<?php

/**
 * @file
 * Contains \Drupal\field_collection\Plugin\Field\FieldFormatter\FieldCollectionEditableFormatter
 */

namespace Drupal\field_collection\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_collection_editable' formatter.
 *
 * @FieldFormatter(
 *   id = "field_collection_editable",
 *   label = @Translation("Editable Field Collection Items"),
 *   field_types = {
 *     "field_collection"
 *   },
 *   settings = {
 *   }
 * )
 */
class FieldCollectionEditableFormatter extends FormatterBase {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $render_items = array();
    foreach ($items as $delta => $item) {
      if ($item->value !== NULL) {
        $to_render = \Drupal::entityManager()
                       ->getViewBuilder('field_collection_item')
                       ->view($item->getFieldCollectionItem());

        // TODO: Add edit links.

        $render_items[] = $to_render;
      }
    }
    // TODO: Add new field collection item link.
    return $render_items;
  }
}
