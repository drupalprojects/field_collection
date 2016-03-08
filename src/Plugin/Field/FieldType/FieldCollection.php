<?php

/**
 * @file
 * Contains \Drupal\field_collection\Plugin\Field\FieldType\FieldCollection.
 */

namespace Drupal\field_collection\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\field_collection\Entity\FieldCollectionItem;

/**
 * Plugin implementation of the 'field_collection' field type.
 *
 * @FieldType(
 *   id = "field_collection",
 *   label = @Translation("Field collection"),
 *   description = @Translation(
 *     "This field stores references to embedded entities, which itself may
 *     contain any number of fields."
 *   ),
 *   settings = {
 *     "path" = "",
 *     "hide_blank_items" = TRUE,
 *   },
 *   instance_settings = {
 *   },
 *   default_widget = "field_collection_complex",
 *   default_formatter = "entity_reference_entity_view",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList"
 * )
 */
class FieldCollection extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return array(
      'target_type' => 'field_collection_item',
    ) + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   *
   * Override to take away option to choose target type.
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $settings_form['handler'] = [
      '#type' => 'value',
      '#value' => 'default:field_collection_item',
    ];
    $settings_form['handler_settings'] = [
      '#type' => 'value',
      '#value' => [

            'target_bundles' => [$this->getFieldDefinition()->getName()],
      ],
    ];
    return $settings_form;
  }

  /**
   * {@inheritdoc}
   *
   * There are no user input options in fieldSettingsForm for this class.
   * So no validation needed.
   */
  public static function fieldSettingsFormValidate(array $form, FormStateInterface $form_state) {
    return;
  }


}

