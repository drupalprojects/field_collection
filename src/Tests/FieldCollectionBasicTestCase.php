<?php

/**
 * @file
 * Definition of Drupal\field_collection\Tests\FieldCollectionBasicTestCase.
 */

namespace Drupal\field_collection\Tests;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field_collection\Entity\FieldCollection;
use Drupal\field_collection\Entity\FieldCollectionItem;
use Drupal\node\Entity\Node;
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
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['field_collection', 'node', 'field', 'field_ui'];

  /**
   * Name of the field collection bundle ie. the field in the host entity.
   *
   * @var string
   */
  protected $field_collection_name;

  /**
   * Field storage config for the field collection bundle.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $field_collection_field_storage;

  /**
   * Field config for the field collection bundle.
   *
   * @var \Drupal\Core\Field\FieldConfigInterface
   */
  protected $field_collection_field;

  /**
   * Name of the field inside the field collection being used for testing.
   *
   * @var string
   */
  protected $inner_field_name;

  /**
   * Definition of the inner field that can be passed to FieldConfig::create().
   *
   * @var array
   */
  protected $inner_field_definition;

  /**
   * EntityStorageInterface for nodes.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  public function setUp() {
    parent::setUp();
    $this->nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

    // Create Article node type.
    if ($this->profile != 'standard') {
      $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    }

    // Create a field_collection field to use for the tests.
    $this->field_collection_name = 'field_test_collection';

    $this->field_collection_field_storage = FieldStorageConfig::create([
      'field_name' => $this->field_collection_name,
      'entity_type' => 'node',
      'type' => 'field_collection',
      'cardinality' => 4,
    ]);

    $this->field_collection_field_storage->save();

    $this->field_collection_field = $this->addFieldCollectionFieldToContentType('article');

    // Create an integer field inside the field_collection.
    $this->inner_field_name = 'field_inner';

    $inner_field_storage = FieldStorageConfig::create([
      'field_name' => $this->inner_field_name,
      'entity_type' => 'field_collection_item',
      'type' => 'integer',
    ]);

    $inner_field_storage->save();

    $this->inner_field_definition = [
      'field_name' => $this->inner_field_name,
      'entity_type' => 'field_collection_item',
      'bundle' => $this->field_collection_name,
      'field_storage' => $inner_field_storage,
      'label' => $this->randomMachineName() . '_label',
      'description' => $this->randomMachineName() . '_description',
      'settings' => [],
    ];

    $inner_field = FieldConfig::create($this->inner_field_definition);

    $inner_field->save();

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
    $field_collection_definition = [
      'field_name' => $this->field_collection_name,
      'entity_type' => 'node',
      'bundle' => $content_type,
      'field_storage' => $this->field_collection_field_storage,
      'label' => $this->randomMachineName() . '_label',
      'description' => $this->randomMachineName() . '_description',
      'settings' => [],
    ];

    $field_config = FieldConfig::create($field_collection_definition);

    $field_config->save();

    \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load("node.$content_type.default")
      ->setComponent($this->field_collection_name, array('type' => 'field_collection_editable'))
      ->save();


    \Drupal::entityTypeManager()
      ->getStorage('entity_form_display')
      ->load("node.$content_type.default")
      ->setComponent($this->field_collection_name, array('type' => 'field_collection_embed'))
      ->save();

    return $field_config;
  }

  /**
   * Helper for creating a new node with a field collection item.
   */
  protected function createNodeWithFieldCollection($content_type) {
    $node = $this->drupalCreateNode(array('type' => $content_type));

    // Manually create a field_collection.
    $entity = FieldCollectionItem::create(['field_name' => $this->field_collection_name]);

    $entity->{$this->inner_field_name}->setValue(1);
    $entity->setHostEntity($node);
    $entity->save();

    return array($node, $entity);
  }

  /**
   * Tests CRUD.
   */
  public function testCRUD() {
    /** @var \Drupal\node\NodeInterface $node */
    /** @var \Drupal\field_collection\FieldCollectionItemInterface $field_collection_item */
    list ($node, $field_collection_item) = $this->createNodeWithFieldCollection('article');

    $this->assertEqual($field_collection_item->id(), $node->{$this->field_collection_name}->value, 'A field_collection_item has been successfully created and referenced.');

    $this->assertEqual($field_collection_item->revision_id->value, $node->{$this->field_collection_name}->revision_id, 'The new field_collection_item has the correct revision.');

    // Test adding an additional field_collection_item.
    $field_collection_item_2 = FieldCollectionItem::create(['field_name' => $this->field_collection_name]);

    $field_collection_item_2->{$this->inner_field_name}->setValue(2);

    $node->{$this->field_collection_name}[1] = array('field_collection_item' => $field_collection_item_2);

    $node->save();
    $this->nodeStorage->resetCache([$node->id()]);
    $node = Node::load($node->id());

    $this->assertTrue(!empty($field_collection_item_2->id()) && !empty($field_collection_item_2->getRevisionId()), 'Another field_collection_item has been saved.');

    $this->assertEqual(count(FieldCollectionItem::loadMultiple()), 2, 'Field_collection_items have been stored.');

    $this->assertEqual($field_collection_item->id(), $node->{$this->field_collection_name}->value, 'Existing reference has been kept during update.');

    $this->assertEqual($field_collection_item->getRevisionId(), $node->{$this->field_collection_name}[0]->revision_id, 'Revision: Existing reference has been kept during update.');

    $this->assertEqual($field_collection_item_2->id(), $node->{$this->field_collection_name}[1]->value, 'New field_collection_item has been properly referenced.');

    $this->assertEqual($field_collection_item_2->getRevisionId(), $node->{$this->field_collection_name}[1]->revision_id, 'Revision: New field_collection_item has been properly referenced.');

    // Make sure deleting the field collection item removes the reference.
    $field_collection_item_2->delete();
    $this->nodeStorage->resetCache([$node->id()]);
    $node = Node::load($node->id());

    $this->assertTrue(!isset($node->{$this->field_collection_name}[1]), 'Reference correctly deleted.');

    // Make sure field_collections are removed during deletion of the host.
    $node->delete();

    $this->assertIdentical(FieldCollectionItem::loadMultiple(), array(), 'field_collection_item deleted when the host is deleted.');

    // Try deleting nodes with collections without any values.
    $node = $this->drupalCreateNode(array('type' => 'article'));
    $node->delete();

    $this->nodeStorage->resetCache([$node->id()]);
    $node = Node::load($node->id());
    $this->assertFalse($node);

    // Test creating a field collection entity with a not-yet saved host entity.
    $node = $this->drupalCreateNode(array('type' => 'article'));

    $field_collection_item = FieldCollectionItem::create(['field_name' => $this->field_collection_name]);

    $field_collection_item->{$this->inner_field_name}->setValue(3);
    $field_collection_item->setHostEntity($node);
    $field_collection_item->save();

    // Now the node should have been saved with the collection and the link
    // should have been established.
    $this->assertTrue(!empty($node->id()), 'Node has been saved with the collection.');

    $this->assertTrue(count($node->{$this->field_collection_name}) == 1 && !empty($node->{$this->field_collection_name}[0]->value) && !empty($node->{$this->field_collection_name}[0]->revision_id), 'Link has been established.');

    // Again, test creating a field collection with a not-yet saved host entity,
    // but this time save both entities via the host.
    $node = $this->drupalCreateNode(array('type' => 'article'));

    $field_collection_item = FieldCollectionItem::create(array('field_name' => $this->field_collection_name));

    $field_collection_item->{$this->inner_field_name}->setValue(4);
    $field_collection_item->setHostEntity($node);
    $node->save();

    $this->assertTrue(!empty($field_collection_item->id()) && !empty($field_collection_item->getRevisionId()), 'Removed field collection item still exists.');

    $this->assertTrue(count($node->{$this->field_collection_name}) == 1 && !empty($node->{$this->field_collection_name}[0]->value) && !empty($node->{$this->field_collection_name}[0]->revision_id), 'Removed field collection item is archived.');

  }

  /**
   * Test deleting the field corresponding to a field collection.
   */
  public function testFieldDeletion() {
    // Create a separate content type with the field collection field.
    $this->drupalCreateContentType(array('type' => 'test_content_type', 'name' => 'Test content type'));

    $field_collection_field_1 = $this->field_collection_field;

    $field_collection_field_2 = $this->addFieldCollectionFieldToContentType('test_content_type');

    list(, $field_collection_item_1) = $this->createNodeWithFieldCollection('article');

    list(, $field_collection_item_2) = $this->createNodeWithFieldCollection('test_content_type');

    /** @var \Drupal\field_collection\FieldCollectionItemInterface $field_collection_item_1 */
    $field_collection_item_id_1 = $field_collection_item_1->id();
    /** @var \Drupal\field_collection\FieldCollectionItemInterface $field_collection_item_2 */
    $field_collection_item_id_2 = $field_collection_item_2->id();

    $field_collection_field_1->delete();

    $this->assertNull(FieldCollectionItem::load($field_collection_item_id_1), 'field_collection_item deleted with the field_collection field.');

    $this->assertNotNull(FieldCollectionItem::load($field_collection_item_id_2), 'Other field_collection_item still exists.');

    $this->assertNotNull(FieldCollection::load($this->field_collection_name), 'field_collection config entity still exists.');

    $field_collection_field_2->delete();

    $this->assertNull(FieldCollectionItem::load($field_collection_item_id_2), 'Other field_collection_item deleted with it\'s field.');

    $this->assertNull(FieldCollection::load($this->field_collection_name), 'field_collection config entity deleted.');
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

    $this->assertText(t($this->inner_field_definition['label']), 'Add form is shown.');

    $edit = array("$this->inner_field_name[0][value]" => rand());
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->assertText(t('Successfully added a @field.', array('@field' => $this->field_collection_name)), 'Field collection saved.');

    $this->assertText($edit["$this->inner_field_name[0][value]"], 'Added field value is shown.');

    $field_collection_item = FieldCollectionItem::load(1);

    // Test field collection item edit form.
    $edit["$this->inner_field_name[0][value]"] = rand();
    $this->drupalPostForm('field_collection_item/1/edit', $edit, t('Save'));

    $this->assertText(t('Successfully edited @field.', array('@field' => $field_collection_item->label())), 'Field collection saved.');

    $this->assertText($edit["$this->inner_field_name[0][value]"], 'Field collection has been edited.');

    $this->drupalGet('field_collection_item/1');

    $this->assertText($edit["$this->inner_field_name[0][value]"], 'Field collection can be viewed.');
  }

  /**
   * Tests how Field Collections manage empty fields.
   *
   * @see \Drupal\field_collection\Plugin\Field\FieldWidget\FieldCollectionEmbedWidget::formMultipleElements()
   */
  public function testEmptyFields() {
    $user_privileged = $this->drupalCreateUser([
      'access content',
      'edit any article content',
      'create article content',
    ]);
    $this->drupalLogin($user_privileged);

    // First, set the field collection cardinality to unlimited.
    $field_config = FieldStorageConfig::loadByName('node', $this->field_collection_name);
    $field_config->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    $field_config->save();

    // Check that we can see field collection fields when creating content.
    $this->drupalGet('node/add/article');
    $this->assertFieldById('edit-field-test-collection-0-field-inner-0-value');

    // Check that the "Add another item" button works as expected.
    $this->drupalPostAjaxForm('node/add/article', array(), array('field_test_collection_add_more' => t('Add another item')));
    // The AJAX request changes field identifiers, so we need to find them by name.
    $this->assertFieldByName('field_test_collection[0][field_inner][0][value]');
    $this->assertFieldByName('field_test_collection[1][field_inner][0][value]');

    // Check that we can see an empty field collection when editing content
    // that did not have values for it.
    $node = $this->drupalCreateNode(array('type' => 'article'));
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertFieldById('edit-field-test-collection-0-field-inner-0-value');
  }

}
