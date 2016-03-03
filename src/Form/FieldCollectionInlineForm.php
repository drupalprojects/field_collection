<?php
/**
 * @file
 * Contains \Drupal\field_collection\Form\FieldCollectionInlineForm.
 */


namespace Drupal\field_collection\Form;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\inline_entity_form\Form\EntityInlineForm;

/**
 * Field Collection inline form handler.
 */
class FieldCollectionInlineForm extends EntityInlineForm {

  /**
   * {@inheritdoc}
   */
  public function saveEntity(ContentEntityInterface $entity, array $context) {

    if (isset($context['instance'])) {
      /** @var \Drupal\field\Entity\FieldConfig $field */
      $field = $context['instance'];
      $entity->host_type = $field->getTargetEntityTypeId();
    }
    $entity->save(TRUE);
  }


}
