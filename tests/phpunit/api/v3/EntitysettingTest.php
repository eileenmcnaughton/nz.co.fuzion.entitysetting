<?php

require_once 'CiviTest/CiviUnitTestCase.php';

/**
 * FIXME
 */
class api_v3_EntitySettingTest extends CiviUnitTestCase {
  protected $_apiversion = 3;
  protected $_params = array();
  protected $ids = array();
  protected $_entity = 'entity_setting';
  public $_eNoticeCompliant = TRUE;

  function setUp() {
    $this->_params = array(
      'entity_id' => 1,
      'entity_type' => 'Relationship',
      'key' => 'test_key',
      'settings' => array('test_setting' => array(1,2,3,4), 'another_setting' => 'Monster'),
    );
    //$this->quickCleanup(array('civicrm_entity_setting'));
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
  }

  public function testCreate() {
    $result = $this->callAPIAndDocument($this->_entity, 'create', $this->_params, __FUNCTION__, __FILE__);
    $this->assertEquals(1, $result['count'], 'In line ' . __LINE__);
    $this->assertNotNull($result['values'][$result['id']]['id'], 'In line ' . __LINE__);
  }

  /**
   * Test that we can get a settings
   */
  function testGet() {
    $this->callAPISuccess($this->_entity, 'create', $this->_params);
    $result = $this->callAPIAndDocument($this->_entity, 'get', array(
      'entity_type' => 'relationship',
      'entity_id' => 1,
      'key' => 'test_key',
    ), __FUNCTION__, __FILE__);
    foreach($this->_params['settings'] as $key => $setting) {
      $this->assertEquals($setting, $result['values'][1][$key]);
    }
  }

  /**
   * Test that we can delete a settings
   */
  function testDelete() {
    $description = 'at this stage only deleting a whole key is supported';
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
    $this->callAPIAndDocument($this->_entity, 'delete', $this->_params, __FUNCTION__, __FILE__, $description);
    $result = $this->callAPISuccess($this->_entity, 'get', $this->_params);
    $this->assertArrayNotHasKey('another_setting', $result['values'][1]);
    $this->callAPISuccess($this->_entity, 'delete', $secondParams);
    $result = $this->callAPISuccess($this->_entity, 'get', $secondParams);
    $this->assertArrayNotHasKey('another_setting', $result['values'][1]);
  }
}
