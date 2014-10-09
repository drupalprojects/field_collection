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
    $count = 0; // TODO: Is there a better way to get an accurate count of the
                // items from the FileItemList that doesn't count blank items?

    foreach ($items as $delta => $item) {
      if ($item->value !== NULL) {
        // TODO: There is probably a better way to generate the URLs...
        // Entity::uri() ?
        $count++;
        $element[$delta] = array('#markup' =>
          _l($this->fieldDefinition->getName() . " $delta",
            "field-collection-item/" . $item->value)
          . " (" . _l(t('Edit'), "field-collection-item/" . $item->value . "/edit",
                     array('query' => array("destination" => current_path())))
          . "|" . _l(t('Delete'), "field-collection-item/" . $item->value
          . "/delete", array('query' =>
                             array("destination" => current_path()))) . ")");
      }
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
        $element['#suffix'] .= _l($title, $add_path, array('query' => drupal_get_destination()));
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
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->cardinality;
    if ($cardinality == -1 || $count < $cardinality) {
      $e = $items->getEntity();
      $element['#suffix'] = '';
      $element['#suffix'] .= '<ul class="action-links action-links-field-collection-add"><li>';
      $element['#suffix'] .= _l(
        t('Add'), "field-collection-item/add/" .
        $this->fieldDefinition->getName() . "/" . $e->getEntityTypeId() . "/" .
        $e->id(), array('query' => array("destination" => current_path())));
      $element['#suffix'] .= '</li></ul>';
    }

    return $element;
  }

}
