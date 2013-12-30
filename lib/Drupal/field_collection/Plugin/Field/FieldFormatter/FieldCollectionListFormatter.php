<?php

/**
 * @file
 * Contains \Drupal\field_collection\Plugin\Field\FieldFormatter\FieldCollectionListFormatter.
 */

namespace Drupal\field_collection\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_collection_list' formatter.
 *
 * @FieldFormatter(
 *   id = "field_collection_list",
 *   label = @Translation("List"),
 *   field_types = {
 *     "field_collection"
 *   },
 *   settings = {
 *   }
 * )
 */
class FieldCollectionListFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $element = array();
    $settings = $this->getFieldSettings();

    foreach ($items as $delta => $item) {
      $element[$delta] = array('#markup' =>
        l($this->fieldDefinition->getName() . " $delta",
          "field-collection/" . $item->value));
    }

    /* The following is the original code from
     * field_collection_field_formatter_links pasted for reference
     * TODO: Replace this
    $settings = $display['settings'];
    if ($settings['add'] && ($field['cardinality'] == FIELD_CARDINALITY_UNLIMITED || count($items) < $field['cardinality'])) {
      // Check whether the current is allowed to create a new item.
      $field_collection_item = entity_create('field_collection_item', array('field_name' => $field['field_name']));
      $field_collection_item->setHostEntity($entity_type, $entity, LANGUAGE_NONE, FALSE);

      if (field_collection_item_access('create', $field_collection_item)) {
        $path = field_collection_field_get_path($field);
        list($id) = entity_extract_ids($entity_type, $entity);
        $element['#suffix'] = '';
        if (!empty($settings['description'])) {
          $element['#suffix'] .= '<div class="description field-collection-description">' . field_filter_xss($instance['description']) . '</div>';
        }
        $title = entity_i18n_string("field:{$field['field_name']}:{$instance['bundle']}:setting_add", $settings['add']);
        $add_path = $path . '/add/' . $entity_type . '/' . $id;
        $element['#suffix'] .= '<ul class="action-links action-links-field-collection-add"><li>';
        $element['#suffix'] .= l($title, $add_path, array('query' => drupal_get_destination()));
        $element['#suffix'] .= '</li></ul>';
      }
    }

    // If there is no add link, add a special class to the last item.
    if (empty($element['#suffix'])) {
      $index = count(element_children($element)) - 1;
      $element[$index]['#attributes']['class'][] = 'field-collection-view-final';
    }

    $element += array('#prefix' => '', '#suffix' => '');
    $element['#prefix'] .= '<div class="field-collection-container clearfix">';
    $element['#suffix'] .= '</div>';
    */

    // TODO: This will be replaced by the above
    $e = $items->getEntity();
    $element['#suffix'] = '';
    $element['#suffix'] .= '<ul class="action-links action-links-field-collection-add"><li>';
    $element['#suffix'] .= l(
      t('Add'), "field-collection/add/" .
      $items->getFieldDefinition()->getName() . "/" . $e->entityType() . "/" .
      $e->id());
    $element['#suffix'] .= '</li></ul>';

    return $element;
  }

}
