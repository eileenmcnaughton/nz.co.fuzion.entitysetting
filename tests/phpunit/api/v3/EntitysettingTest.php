<?php

use CRM_Entitysetting_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class api_v3_EntitySettingTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  use \Civi\Test\Api3DocTrait;

  protected $_apiversion = 3;
  protected $_params = [];
  protected $ids = [];
  protected $_entity = 'entity_setting';
  public $_eNoticeCompliant = TRUE;

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp(): void {
    $this->_params = [
      'entity_id' => 1,
      'entity_type' => 'Relationship',
      'key' => 'test_key',
      'settings' => ['test_setting' => [1, 2, 3, 4], 'another_setting' => 'Monster'],
    ];
    //$this->quickCleanup(array('civicrm_entity_setting'));
    parent::setUp();
  }

  public function tearDown(): void {
    parent::tearDown();
  }

  public function testCreate(): void {
    $result = $this->callAPISuccess($this->_entity, 'create', $this->_params);
    $this->assertEquals(1, $result['count'], 'In line ' . __LINE__);
    $this->assertNotNull($result['values'][$result['id']]['id'], 'In line ' . __LINE__);
  }

  /**
   * Test that we can get a settings
   */
  public function testGet(): void {
    $this->callAPISuccess($this->_entity, 'create', $this->_params);
    $result = $this->callAPISuccess($this->_entity, 'get', [
      'entity_type' => 'relationship',
      'entity_id' => 1,
      'key' => 'test_key',
    ]);
    foreach ($this->_params['settings'] as $key => $setting) {
      $this->assertEquals($setting, $result['values'][1][$key]);
    }
  }

  /**
   * Test that we can delete a settings
   */
  public function testDelete(): void {
    $this->callAPISuccess($this->_entity, 'create', $this->_params);
    $secondParams = $this->_params;
    $secondParams['key'] = 'second_key';
    $secondParams['settings']['another_setting'] = 'little angel';
    $this->callAPISuccess($this->_entity, 'create', $secondParams);
    unset($this->_params['settings'], $secondParams['settings']);
    $result = $this->callAPISuccess($this->_entity, 'get', $this->_params);
    $this->assertEquals('Monster', $result['values'][1]['another_setting']);
    $result = $this->callAPISuccess($this->_entity, 'get', $secondParams);
    $this->assertEquals('little angel', $result['values'][1]['another_setting']);
    $this->callAPISuccess($this->_entity, 'delete', $this->_params);
    $result = $this->callAPISuccess($this->_entity, 'get', $this->_params);
    $this->assertArrayNotHasKey('another_setting', $result['values'][1]);
    $this->callAPISuccess($this->_entity, 'delete', $secondParams);
    $result = $this->callAPISuccess($this->_entity, 'get', $secondParams);
    $this->assertArrayNotHasKey('another_setting', $result['values'][1]);
  }

}
