<?php

/**
 * @file
 * Contains \Drupal\field_collection\Plugin\Field\FieldFormatter\FieldCollectionLinksFormatter.
 */

namespace Drupal\field_collection\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Entity\ContentEntityInterface;

abstract class FieldCollectionLinksFormatter extends FormatterBase {

  /**
   * Helper function to get Edit and Delete links for an item.
   */
  protected function getEditLinks(FieldItemInterface $item) {
    $links = '(' . \Drupal::l(t('Edit'),
      Url::FromRoute('entity.field_collection_item.edit_form',
                     array('field_collection_item' => $item->value)));

    $links .= '|' . \Drupal::l(t('Delete'),
      Url::FromRoute('entity.field_collection_item.delete_form',
                     array('field_collection_item' => $item->value)));

    $links .= ')';

    return $links;
  }

  protected function getAddLink(ContentEntityInterface $host) {
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
    $link = '<ul class="action-links action-links-field-collection-add"><li>';

    $link .= _l(t('Add'),
      Url::FromRoute('field_collection_item.add_page', array(
        'field_collection' => $this->fieldDefinition->getName(),
        'host_type' => $host->getEntityTypeId(),
        'host_id' => $host->id(),))
      ->toString());

    $link .= '</li></ul>';

    return($link);
  }

}
