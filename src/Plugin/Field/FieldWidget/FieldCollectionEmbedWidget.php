<?php

/**
 * @file
 * Contains \Drupal\field_collection\Plugin\Field\FieldWidget\FieldCollectionEmbedWidget.
 */

namespace Drupal\field_collection\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field_collection\Entity\FieldCollectionItem;

/**
 * Plugin implementation of the 'field_collection_embed' widget.
 *
 * @FieldWidget(
 *   id = "field_collection_embed",
 *   label = @Translation("Embedded"),
 *   field_types = {
 *     "field_collection"
 *   },
 *   settings = {
 *   }
 * )
 */
class FieldCollectionEmbedWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_collection_item = $items[$delta]->getFieldCollectionItem(TRUE);

    $field_collection_item_form = \Drupal::service('entity.form_builder')->getForm($field_collection_item);
    foreach ($field_collection_item_form as $key => $value) {
      if (substr($key, 0, 6) == 'field_') {
        $element[$key] = $value;
      }
    }

    if ($this->fieldDefinition->getFieldStorageDefinition()->cardinality == 1) {
      $element['#prefix'] = '<fieldset><legend>' . $this->fieldDefinition->label . '</legend>';
      $element['#suffix'] = '</fieldset>';
    }

    return $element;
  }
}
