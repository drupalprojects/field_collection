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
    $item = entity_create('field_collection_item',
                          array('field_name' => $this->fieldDefinition->field_name));

    $element = \Drupal::service('entity.form_builder')->getForm($item);
    unset($element['actions']);

    if ($this->fieldDefinition->getFieldStorageDefinition()->cardinality == 1) {
      $element = array(
        '#type' => 'fieldset',
        '#title' => $this->fieldDefinition->label,
        '#collapsible' => FALSE,
        'items' => $element,
      );
    }

    return $element;
  }
}
