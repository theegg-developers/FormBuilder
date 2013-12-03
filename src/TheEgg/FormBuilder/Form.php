<?php namespace TheEgg\FormBuilder;

class Form {
  public $object;
  protected $buffer;
  protected $parent_prefix;
  protected $item_number;
  protected $blueprints = array();
  
  function __construct($options = array()){
    $this->object = $options[0];
    $this->buffer ='';
    $this->relation = isset($options['relation'])? $options['relation'] : null;
    $this->parent_prefix = isset($options['parent_prefix']) ? $options['parent_prefix'] : null;
    $this->item_number = isset($options['item_number']) ? $options['item_number'] : null;
  }

  function fields_for($relation_name, $items, $callback){
    $this->saveBlueprint($relation_name, $callback);
    $blueprint = $this->getBlueprint($relation_name);
    $this->addMarkup('<div class="nested-fields">');
    $i = 0;
    foreach($items as $item){
      $nested_form = new Form(array($item, 'parent_prefix'=>$this->prefix(), 'item_number'=>$i, 'relation'=>$relation_name));
      $this->addMarkup($blueprint($nested_form));
      $i++;
    }
    $this->addMarkup('</div>');
  }

  private function saveBlueprint($relation_name, $callback){
    $this->blueprints[$relation_name] = 
      function($form) use($callback, $relation_name){
        $callback($form);
        if(isset($form->object->id)){
          $form->hidden_input('id',array('class'=>'js-nested-fields-id'));
          $form->hidden_input('_destroy', array('value'=>0, 'class'=> 'js-nested-fields-destroy'));
        }
        return '<div class="nested-fields-group '.$relation_name.'">' . $form->getBuffer() . '</div>';
      };
  }

  private function getBlueprint($relation_name){
    return $this->blueprints[$relation_name];
  }

  function link_to_add($relation_name, $title){
    $item = new \stdClass();
    $form = new Form(array($item, 'parent_prefix'=>$this->prefix(), 'item_number'=> 'placeholder-item-number'));
    $blueprint = $this->getBlueprint($relation_name);    
    $this->addMarkup('<a href="#" class="js-add-nested-fields btn" data-relation="'.$relation_name.'" data-blueprint="'.htmlspecialchars($blueprint($form)).'"><i class="fa fa-plus-circle"></i> '.$title.' </a>');
  }

  function link_to_remove($title){
    $this->addMarkup('<a href="#" class="js-remove-nested-fields pull-right"><i class="fa fa-trash-o"></i> '.$title.'</a>');
  }


  //@todo: markup in a different file (a view) and dependency injection of views
  function text_input($label, $field, $options = array()){
    $markup = \Form::text($this->prefixed_field($field), $this->field_value($field), $options);
    return $markup;
  }

  function file_input($label, $field, $options = array()){
    $markup = \Form::file($this->prefixed_field($field), $options);
    return $markup;
  }

  function textarea_input($label, $field, $options = array()){
    $markup = \Form::textarea($this->prefixed_field($field), $this->field_value($field), $options);
    return $markup;
  }

  function date_input($label, $field, $options= array()){
    $markup = \Form::input('date', $this->prefixed_field($field), $this->field_value($field), $options);
    return $markup;
  }

  function number_input($label, $field, $options= array()){
    $markup = \Form::input('number', $this->prefixed_field($field), $this->field_value($field), $options);
    return $markup;
  }

  function time_input($label, $field, $options=array()){
    $markup = \Form::input('time', $this->prefixed_field($field), $this->field_value($field), $options);
    return $markup;
  }

  function password_input($label, $field, $options = array()){
    $markup = \Form::password($this->prefixed_field($field), $options);
    return $markup;
  }

  function select_input($label, $field, $list, $options=array()){
    if(isset($options['allow_blank']))
      $list = array_merge(array(''), $list);
    $markup = \Form::select($this->prefixed_field($field), $list, $this->field_value($field), $options);
    return $markup;
  }

  function label($name, $field, $options = array()) {
    $markup = \Form::label($this->prefixed_field($field), $name, $options);
    return $markup;
  }

  function addMarkupWithLabel($label, $field, $input_string){
    $this->addMarkup(
    '<div class="control-group">'.
    \Form::label($this->prefixed_field($field), $label, array('class'=>'control-label')).
    '<div class="controls">'.$input_string.
    '</div></div>');
  }

  function hidden_input($field, $options=array()){
    $value = '';
    if(isset($options['value'])){
      $value = $options['value'];
      unset($options['value']);
    }
    else $value = $this->field_value($field);
    $markup = \Form::hidden($this->prefixed_field($field), $value, $options);
    return $markup;
  }

  function addMarkup($text){
    $this->buffer .= $text;
  }

  function getBuffer(){
    return $this->buffer;
  }

  protected function prefix(){
    if(! $this->parent_prefix)
      return $this->objectName();
    return "{$this->parent_prefix}[{$this->relation}_attributes][{$this->item_number}]";
  }

  protected function prefixed_field($field){
    return $this->prefix()."[{$field}]";
  }

  private function objectName(){
    return \Illuminate\Support\Str::snake(get_class($this->object));
  }

  protected function field_value($field){
    if(isset($this->object->$field))
      return $this->object->$field;
    return null;
  }
}