<?php
/**
 * @file
 * Contains \Drupal\field_collection\Form\FieldCollectionInlineForm.
 */


namespace Drupal\field_collection\Form;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\inline_entity_form\Form\EntityInlineForm;

/**
 * Field Collection inline form handler.
 */
class FieldCollectionInlineForm extends EntityInlineForm {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   * @todo Current save() is not in InlineFormInterface
   *  Uncomment once https://www.drupal.org/node/2680681 is committed for IEF
  * public function save(EntityInterface $entity) {
    * return $entity->save(TRUE);
  * }*/

  /**
   * {@inheritdoc}
   */
  protected function buildEntity(array $entity_form, ContentEntityInterface $entity, FormStateInterface $form_state) {
    parent::buildEntity($entity_form, $entity, $form_state);
    $ief_state = $form_state->get(['inline_entity_form', $entity_form['#ief_id']]);
    /** @var \Drupal\field\FieldConfigInterface $field */
    $field = $ief_state['instance'];
    $entity->host_type = $field->getTargetEntityTypeId();
    $entity->skip_host_save = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTableFields($bundles) {
    $table_fields = parent::getTableFields($bundles);
    $table_fields['label']['label'] = $this->t('Item');
    return $table_fields;
  }

}
