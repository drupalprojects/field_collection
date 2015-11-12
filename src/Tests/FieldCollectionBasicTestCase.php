<?php

/**
 * @file
 * Definition of Drupal\field_collection\Tests\FieldCollectionBasicTestCase.
 */

namespace Drupal\field_collection\Tests;
use Drupal\simpletest\WebTestBase;

// TODO: Test field collections with no fields or with no data in their fields
//       once it's determined what is a good behavior for that situation.
//       Unless something is changed the Entity and the field entry for it
//       won't get created unless some data exists in it.

/**
 * Test basics.
 *
 * @group field_collection
 */
class FieldCollectionBasicTestCase extends WebTestBase {

  /**
   * Field collection field.
   *
   * @var
   */
  protected $field;

  /**
   * Field collection field instance.
   *
   * @var
   */
  protected $instance;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['field_collection', 'node', 'field', 'field_ui'];

  protected $field_collection_name;

  protected $field_collection_field_storage;

  protected $field_collection_field;

  protected $inner_field_name;

  protected $inner_field_storage;

  protected $inner_field_definition;

  protected $inner_field;

  protected $field_collection_definition;

  public function setUp() {
    parent::setUp();

    // Create Article node type.
    if ($this->profile != 'standard') {
      $this->drupalCreateContentType(array('type' => 'article',
                                           'name' => 'Article'));
    }

    // Create a field_collection field to use for the tests.
    $this->field_collection_name = 'field_test_collection';

    $this->field_collection_field_storage = entity_create('field_storage_config', [
      'field_name' => $this->field_collection_name,
      'entity_type' => 'node',
      'type' => 'field_collection',
      'cardinality' => 4,
    ]);

    $this->field_collection_field_storage->save();

    $this->field_collection_field = $this->addFieldCollectionFieldToContentType('article');

    // Create an integer field inside the field_collection.
    $this->inner_field_name = 'field_inner';

    $this->inner_field_storage = entity_create('field_storage_config', [
      'field_name' => $this->inner_field_name,
      'entity_type' => 'field_collection_item',
      'type' => 'integer',
    ]);

    $this->inner_field_storage->save();

    $this->inner_field_definition = [
      'field_name' => $this->inner_field_name,
      'entity_type' => 'field_collection_item',
      'bundle' => $this->field_collection_name,
      'field_storage' => $this->inner_field_storage,
      'label' => $this->randomMachineName() . '_label',
      'description' => $this->randomMachineName() . '_description',
      'settings' => [],
    ];

    $this->inner_field = entity_create('field_config', $this->inner_field_definition);

    $this->inner_field->save();

    entity_get_form_display('field_collection_item', $this->field_collection_name, 'default')
      ->setComponent($this->inner_field_name, array('type' => 'number'))
      ->save();

    entity_get_display('field_collection_item', $this->field_collection_name, 'default')
      ->setComponent($this->inner_field_name, array('type' => 'number_decimal'))
      ->save();
  }

  /**
   * Helper function for adding the field collection field to a content type.
   */
  protected function addFieldCollectionFieldToContentType($content_type) {
    $this->field_collection_definition = [
      'field_name' => $this->field_collection_name,
      'entity_type' => 'node',
      'bundle' => $content_type,
      'field_storage' => $this->field_collection_field_storage,
      'label' => $this->randomMachineName() . '_label',
      'description' => $this->randomMachineName() . '_description',
      'settings' => [],
    ];

    $field_config = entity_create('field_config', $this->field_collection_definition);

    $field_config->save();

    \Drupal::entityManager()
      ->getStorage('entity_view_display')
      ->load("node.$content_type.default")
      ->setComponent($this->field_collection_name, array('type' => 'field_collection_editable'))
      ->save();

    return $field_config;
  }

  /**
   * Helper for creating a new node with a field collection item.
   */
  protected function createNodeWithFieldCollection($content_type) {
    $node = $this->drupalCreateNode(array('type' => $content_type));

    // Manually create a field_collection.
    $entity = entity_create('field_collection_item', ['field_name' => $this->field_collection_name]);

    $entity->{$this->inner_field_name}->setValue(1);
    $entity->setHostEntity($node);
    $entity->save();

    return array($node, $entity);
  }

  /**
   * Tests CRUD.
   */
  public function testCRUD() {
    list ($node, $field_collection_item) = $this->createNodeWithFieldCollection('article');

    $node = node_load($node->id(), TRUE);

    $this->assertEqual($field_collection_item->id(), $node->{$this->field_collection_name}->value);

    $this->assertEqual($field_collection_item->revision_id->value, $node->{$this->field_collection_name}->revision_id);

    // Test adding an additional field_collection_item.
    $field_collection_item_2 = entity_create('field_collection_item', array('field_name' => $this->field_collection_name));

    $field_collection_item_2->{$this->inner_field_name}->setValue(2);

    $node->{$this->field_collection_name}[1] = array('field_collection_item' => $field_collection_item_2);

    $node->save();
    $node = node_load($node->id(), TRUE);

    $this->assertTrue(!empty($field_collection_item_2->id()) && !empty($field_collection_item_2->getRevisionId()));

    $this->assertEqual(count(entity_load_multiple('field_collection_item', NULL, TRUE)), 2);

    $this->assertEqual($field_collection_item->id(), $node->{$this->field_collection_name}->value);

    $this->assertEqual($field_collection_item->getRevisionId(), $node->{$this->field_collection_name}[0]->revision_id);

    $this->assertEqual($field_collection_item_2->id(), $node->{$this->field_collection_name}[1]->value);

    $this->assertEqual($field_collection_item_2->getRevisionId(), $node->{$this->field_collection_name}[1]->revision_id);

    // Make sure deleting the field collection item removes the reference.
    $field_collection_item_2->delete();
    $node = node_load($node->id(), TRUE);

    $this->assertTrue(!isset($node->{$this->field_collection_name}[1]));

    // Make sure field_collections are removed during deletion of the host.
    $node->delete();

    $this->assertIdentical(entity_load_multiple('field_collection_item', NULL, TRUE), array());

    // Try deleting nodes with collections without any values.
    $node = $this->drupalCreateNode(array('type' => 'article'));
    $node->delete();

    $this->assertTrue(node_load($node->id(), NULL, TRUE) == FALSE);

    // Test creating a field collection entity with a not-yet saved host entity.
    $node = $this->drupalCreateNode(array('type' => 'article'));

    $field_collection_item = entity_create('field_collection_item', array('field_name' => $this->field_collection_name));

    $field_collection_item->{$this->inner_field_name}->setValue(3);
    $field_collection_item->setHostEntity($node);
    $field_collection_item->save();

    // Now the node should have been saved with the collection and the link
    // should have been established.
    $this->assertTrue(!empty($node->id()));

    $this->assertTrue(count($node->{$this->field_collection_name}) == 1 && !empty($node->{$this->field_collection_name}[0]->value) && !empty($node->{$this->field_collection_name}[0]->revision_id));

    // Again, test creating a field collection with a not-yet saved host entity,
    // but this time save both entities via the host.
    $node = $this->drupalCreateNode(array('type' => 'article'));

    $field_collection_item = entity_create('field_collection_item', array('field_name' => $this->field_collection_name));

    $field_collection_item->{$this->inner_field_name}->setValue(4);
    $field_collection_item->setHostEntity($node);
    $node->save();

    $this->assertTrue(!empty($field_collection_item->id()) && !empty($field_collection_item->getRevisionId()));

    $this->assertTrue(count($node->{$this->field_collection_name}) == 1 && !empty($node->{$this->field_collection_name}[0]->value) && !empty($node->{$this->field_collection_name}[0]->revision_id));

  }

  /**
   * Test deleting the field corrosponding to a field collection.
   */
  public function testFieldDeletion() {
    // Create a separate content type with the field collection field.
    $this->drupalCreateContentType(array('type' => 'test_content_type', 'name' => 'Test content type'));

    $field_collection_field_1 = $this->field_collection_field;

    $field_collection_field_2 = $this->addFieldCollectionFieldToContentType('test_content_type');

    list($node_1, $field_collection_item_1) = $this->createNodeWithFieldCollection('article');

    list($node_2, $field_collection_item_2) = $this->createNodeWithFieldCollection('test_content_type');

    $field_collection_item_id_1 = $field_collection_item_1->id();
    $field_collection_item_id_2 = $field_collection_item_2->id();
    $field_storage_config_id = $this->field_collection_field_storage->id();

    $field_collection_field_1->delete();

    $this->assertNull(entity_load('field_collection_item', $field_collection_item_id_1, TRUE), 'field_collection_item deleted with the field_collection field.');

    $this->assertNotNull(entity_load('field_collection_item', $field_collection_item_id_2, TRUE), 'Other field_collection_item still exists.');

    $this->assertNotNull(entity_load('field_collection', $this->field_collection_name, TRUE), 'field_collection config entity still exists.');

    $field_collection_field_2->delete();

    $this->assertNull(entity_load('field_collection_item', $field_collection_item_id_2, TRUE), 'Other field_collection_item deleted with it\'s field.');

    $this->assertNull(entity_load('field_collection', $this->field_collection_name, TRUE), 'field_collection config entity deleted.');
  }

  /**
   * Make sure the basic UI and access checks are working.
   */
  public function testBasicUI() {
    $node = $this->drupalCreateNode(array('type' => 'article'));

    // Login with new user that has no privileges.
    $user = $this->drupalCreateUser(array('access content'));
    $this->drupalLogin($user);

    // Make sure access is denied.
    $path = "field_collection_item/add/field_test_collection/node/{$node->id()}";

    $this->drupalGet($path);
    $this->assertText(t('Access denied'), 'Access has been denied.');

    // Login with new user that has basic edit rights.
    $user_privileged = $this->drupalCreateUser([
      'access content',
      'edit any article content',
    ]);

    $this->drupalLogin($user_privileged);

    // Test field collection item add form.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->drupalGet("node/{$node->id()}");
    $this->assertLinkByHref($path, 0, 'Add link is shown.');
    $this->drupalGet($path);

    $this->assertText(t($this->inner_field_definition['label']));

    $edit = array("$this->inner_field_name[0][value]" => rand());
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->assertText(t('Successfully added a @field.', array('@field' => $this->field_collection_name)));

    $this->assertText($edit["$this->inner_field_name[0][value]"]);

    $field_collection_item = field_collection_item_load(1);

    // Test field collection item edit form.
    $edit["$this->inner_field_name[0][value]"] = rand();
    $this->drupalPostForm('field_collection_item/1/edit', $edit, t('Save'));

    $this->assertText(t('Successfully edited @field.', array('@field' => $field_collection_item->label())));

    $this->assertText($edit["$this->inner_field_name[0][value]"]);

    $this->drupalGet('field_collection_item/1');

    $this->assertText($edit["$this->inner_field_name[0][value]"]);
  }

}
