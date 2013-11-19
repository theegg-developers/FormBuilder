<?php namespace TheEgg\FormBuilder;

class Builder{
  static function form_for($object, $callback){
    $form = new Form(array($object));
    $callback($form);
    echo $form->getBuffer();
  }
}