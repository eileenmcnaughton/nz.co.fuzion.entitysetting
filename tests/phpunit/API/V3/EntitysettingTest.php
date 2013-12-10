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
    $this->_params = array('entity_id' => 1, 'entity_type' => 'Relationship');
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
    $this->getAndCheck($this->params, $result['id'], $this->_entity);
  }

  /**
   * Test that 8^2 == 64
   */
  function testSquareOfEight() {
    $this->assertEquals(64, 8*8);
  }
}