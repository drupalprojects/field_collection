<?php

/**
 * @file
 * Contains \Drupal\field_collection\Plugin\Field\FieldWidget\FieldCollectionComplex.
 */

namespace Drupal\field_collection\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;

/**
 * Plugin implementation of the 'field_collection_complex' widget.
 *
 * @FieldWidget(
 *   id = "field_collection_complex",
 *   label = @Translation("Field Collection"),
 *   field_types = {
 *     "field_collection"
 *   },
 *   multiple_values = true
 * )
 */
class FieldCollectionComplex extends InlineEntityFormComplex {
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $defaults = [
      'allow_new' => TRUE,
      'allow_existing' => FALSE,
      'match_operator' => 'CONTAINS',
    ] + parent::defaultSettings();
    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    // These settings should not be options.
    unset($elements['allow_existing']);
    unset($elements['match_operator']);
    unset($elements['allow_new']);


    return $elements;
  }


  /**
   * {@inheritdoc}
   *
   * This function is copied from InlineEntityFormBase::settingsForm.
   * We do not want the logic in InlineEntityFormComplex::settingsSummary.
   */
  public function settingsSummary() {
    $summary = [];
    if ($entity_form_mode = $this->getEntityFormMode()) {
      $form_mode_label = $entity_form_mode->label();
    }
    else {
      $form_mode_label = $this->t('Default');
    }
    $summary[] = t('Form mode: @mode', ['@mode' => $form_mode_label]);
    if ($this->getSetting('override_labels')) {
      $summary[] = $this->t(
        'Overriden labels are used: %singular and %plural',
        ['%singular' => $this->getSetting('label_singular'), '%plural' => $this->getSetting('label_plural')]
      );
    }
    else {
      $summary[] = $this->t('Default labels are used.');
    }

    return $summary;
  }

}
