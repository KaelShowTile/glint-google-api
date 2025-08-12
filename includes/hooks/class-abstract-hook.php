<?php
abstract class Abstract_Hook {
    protected $automation_id;
    
    public function __construct($automation_id) {
        $this->automation_id = $automation_id;
    }
    
    abstract public function handle($data);
    
    protected function process_automation($hook_data) {
        $handler = new Automation_Handler($this->automation_id);
        $handler->execute($hook_data);
    }
}