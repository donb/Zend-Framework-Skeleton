<?php
/**
 * @group controllers
 */

require_once realpath(dirname(__FILE__) . '/../../TestHelper.php');

class IndexControllerTest extends BaseControllerTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testShouldGetIndexPage() {
        $this->dispatch('/');
        $this->assertAction('index');
    }
}
