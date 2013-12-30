<?php

/**
 * @file
 * Contains \Drupal\field_collection\Form\FieldCollectionItemDeleteForm.
 */

namespace Drupal\field_collection\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a form for deleting a field collection item.
 */
class FieldCollectionItemDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete this field collection item?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $this->entity->delete();
    $host_entity = $this->entity->getHost();

    foreach ($this->entity->getHost()->{$this->entity->bundle()} as $key => $value) {
      if ($value->value == $this->entity->id()) {
        unset($this->entity->getHost()->{$this->entity->bundle()}[$key]);
      }
    }
    $this->entity->getHost()->save();

    watchdog('content', '@type: deleted %id.', array('@type' => $this->entity->bundle(), '%id' => $this->entity->id()));
    $node_type_storage = $this->entityManager->getStorageController('field_collection');
    $node_type = $node_type_storage->load($this->entity->bundle())->label();
    drupal_set_message(t('@type %id has been deleted.', array('@type' => $node_type, '%id' => $this->entity->id())));
    Cache::invalidateTags(array('content' => TRUE));
    $form_state['redirect_route']['route_name'] = '<front>';
  }

}
