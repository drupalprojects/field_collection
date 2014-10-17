<?php

/**
 * @file
 * Contains \Drupal\field_collection\FieldCollectionWidgetAjaxController.
 */

namespace Drupal\field_collection\Controller;

use Drupal\system\Controller\FormAjaxController;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Component\Utility\NestedArray;

class FieldCollectionWidgetAjaxController extends FormAjaxController {

  /**
   * Processes AJAX field_collection deletions.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AjaxResponse object.
   */
  public function remove(Request $request) {
    $form_parents = explode('/', $request->query->get('element_parents'));

    //$form_build_id = $request->query->get('form_build_id');
    $form_build_id = $request->request->get('form_build_id');

    $request_form_build_id = $request->request->get('form_build_id');

    if (empty($request_form_build_id) || $form_build_id !== $request_form_build_id) {
      // Invalid request.
      drupal_set_message(t('An unrecoverable error occurred.'), 'error');
      $response = new AjaxResponse();
      $status_messages = array('#theme' => 'status_messages');
      return $response->addCommand(new ReplaceCommand(NULL, drupal_render($status_messages)));
    }

    try {
      $ajaxForm = $this->getForm($request);
      $form = $ajaxForm->getForm();
      $form_state = $ajaxForm->getFormState();
      $commands = $ajaxForm->getCommands();
    }
    catch (HttpExceptionInterface $e) {
      // Invalid form_build_id.
      drupal_set_message(t('An unrecoverable error occurred. Use of this form has expired. Try reloading the page and submitting again.'), 'error');
      $response = new AjaxResponse();
      $status_messages = array('#theme' => 'status_messages');
      return $response->addCommand(new ReplaceCommand(NULL, drupal_render($status_messages)));
    }

    // Process user input. $form and $form_state are modified in the process.
    drupal_process_form($form['#form_id'], $form, $form_state);

    // Retrieve the element to be rendered.
    $address = array_slice($form_parents, 0, -1);
    $form = NestedArray::getValue($form, $address);

    $status_messages = array('#theme' => 'status_messages');
    $form['#prefix'] .= drupal_render($status_messages);
    $output = drupal_render($form);
    drupal_process_attached($form);
    $js = _drupal_add_js();
    $settings = drupal_merge_js_settings($js['settings']['data']);

    $response = new AjaxResponse();
    foreach ($commands as $command) {
      $response->addCommand($command, TRUE);
    }
    return $response->addCommand(new ReplaceCommand(NULL, $output, $settings));
  }

}
