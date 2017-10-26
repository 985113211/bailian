<?php
class Test_lib extends Base_lib {
    public function __construct() {
        parent::__construct();
        $this->load_model('active_model');
        $this->load_model('test_model');
    }
    
    public function get_list()
    {
        $sql = "SELECT ID id,PARENT_ID pid,NAME name FROM test";
        $aa = $this->CI->active_model->exec_by_sql($sql,TRUE);
        RETURN $aa;
    }
    
    public function exec_sql($sql,$a)
    {
        return $this->CI->test_model->exec_by_sql($sql,$a);
    }
    
    public function delete1($where,$table)
    {
        return $this->CI->test_model->delete1($where,$table);
    }
    
}

