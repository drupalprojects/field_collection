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
    if ($ief_state = $form_state->get(['inline_entity_form', $entity_form['#ief_id']])) {
      if (!empty($ief_state['instance'])) {
        /** @var \Drupal\field\FieldConfigInterface $field */
        $field = $ief_state['instance'];
        $entity->host_type = $field->getTargetEntityTypeId();
        // Not needed if this committed to IEF https://www.drupal.org/node/2680681
        $entity->skip_host_save = TRUE;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTableFields($bundles) {
    $table_fields = parent::getTableFields($bundles);
    $bundle = array_pop($bundles);
    $use_display_fields = FALSE;
    // If there is a entity view display 'table' use those fields.
    // @todo How to document to the end user this functionality?
    /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $entity_view_display */
    if ($entity_view_display = $this->entityTypeManager->getStorage('entity_view_display')->load("field_collection_item.$bundle.table")) {
      foreach ($entity_view_display->getComponents() as $field_name =>$component) {
        $use_display_fields = TRUE;
        $fields = $this->entityFieldManager->getFieldDefinitions('field_collection_item', $bundle);
        /** @var \Drupal\field\Entity\FieldConfig $field */
        $field = $fields[$field_name];
        $table_fields[$field_name] = [
          'type' => 'field',
          'label' => $field->getLabel(),
          'display_options' => ['settings' => $component['settings']],
        ];
      }
    }
    if ($use_display_fields) {
      unset($table_fields['label']);
    }
    else {
      $table_fields['label']['label'] = $this->t('Item');
    }
    return $table_fields;
  }

}
