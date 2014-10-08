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
    return $element;
  }
}
