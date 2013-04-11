<?php

/**
 * @file
 * Contains \Drupal\field_collection\FieldCollectionItemFormController
 */

use Drupal\Core\Entity\EntityFormControllerNG;
use Drupal\Core\Entity\EntityInterface;

namespace Drupal\field_collection;

class FieldCollectionItemFormController extends EntityFormControllerNG {

  /**
   * Overrides \Drupal\Core\Entity\EntityFormControllerNG::form().
   */
  public function form(array $form, array &$form_state, EntityInterface $field_collection_item) {

    // Basic item information.
    foreach (array('revision_id', 'id', 'field_name') as $key) {
      $form[$key] = array(
        '#type' => 'value',
        '#value' => $field_collection_item->$key->value,
      );
    }

    $language_configuration = module_invoke('language', 'get_default_configuration', 'field_collection_item', $field_collection_item->field_name->value);

    // Set the correct default language.
    if ($field_collection_item->isNew() && !empty($language_configuration['langcode'])) {
      $language_default = language($language_configuration['langcode']);
      $field_collection_item->langcode->value = $language_default->langcode;
    }

    $form['langcode'] = array(
      '#title' => t('Language'),
      '#type' => 'language_select',
      '#default_value' => $field_collection_item->langcode->value,
      '#languages' => LANGUAGE_ALL,
      '#access' => isset($language_configuration['language_show']) && $language_configuration['language_show'],
    );

    return parent::form($form, $form_state, $block);
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormControllerNG::submit().
   */
  public function submit(array $form, array &$form_state) {
    // Build the block object from the submitted values.
    $field_collection_item = parent::submit($form, $form_state);
    $field_collection_item->setNewRevision();

    return $field_collection_item;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormControllerNG::save().
   */
  public function save(array $form, array &$form_state) {
    $field_collection_item = $this->getEntity($form_state);
    $insert = empty($field_collection_item->id->value);
    $field_collection_item->save();
    $watchdog_args = array('@type' => $field_collection_item->bundle(), '%info' => $field_collection_item->label());

    if ($insert) {
      watchdog('content', '@type: added %info.', $watchdog_args, WATCHDOG_NOTICE);
    }
    else {
      watchdog('content', '@type: updated %info.', $watchdog_args, WATCHDOG_NOTICE);
    }

    if ($field_collection_item->id->value) {
      $form_state['values']['id'] = $field_collection_item->id->value;
      $form_state['id'] = $field_collection_item->id->value;
    }
    else {
      // In the unlikely case something went wrong on save, the block will be
      // rebuilt and block form redisplayed.
      drupal_set_message(t('The field collection item could not be saved.'), 'error');
      $form_state['rebuild'] = TRUE;
    }

    $form_state['redirect'] = $field_collection_item->uri();

    // Clear the page and block caches.
    cache_invalidate_tags(array('content' => TRUE));
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormControllerNG::delete().
   */
  public function delete(array $form, array &$form_state) {
    $destination = array();
    if (isset($_GET['destination'])) {
      $destination = drupal_get_destination();
      unset($_GET['destination']);
    }
    $field_collection_item = $this->buildEntity($form, $form_state);
    $form_state['redirect'] = array('field-collection/' . $field_collection_item->id() . '/delete', array('query' => $destination));
  }
}
