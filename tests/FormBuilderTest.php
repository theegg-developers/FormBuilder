<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use TheEgg\FormBuilder;

class FormBuilderTest extends extends PHPUnit_Framework_TestCase {

  public function testCreateFormBuilder(){
    $form = new Form(array(new stdClass()));
    $this->assertTrue(true);
  }
}