<?php

/**
 * @file
 * Contains \Drupal\field_collection\Plugin\Field\FieldFormatter\FieldCollectionListFormatter.
 */

namespace Drupal\field_collection\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_collection_list' formatter.
 *
 * @FieldFormatter(
 *   id = "field_collection_list",
 *   label = @Translation("List"),
 *   field_types = {
 *     "field_collection"
 *   },
 *   settings = {
 *   }
 * )
 */
class FieldCollectionListFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $elements = array();
    $settings = $this->getFieldSettings();

    foreach ($items as $delta => $item) {
      $elements[$delta] = array('#markup' => $this->fieldDefinition->getName() .
                                " $delta");
    }

    return $elements;
  }
}
