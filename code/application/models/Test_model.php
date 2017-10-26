<?php
class Test_model extends MY_Model {
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 插入表数据
     * @param array $data
     * @param string $table
     * @return int 返回插入id
     */
    public function insert_data($data, $table)
    {
        $data['time']           = time();
        $data['update_time']    = time();
        return parent::insert_data($data, $table);
    }
    
    /**
     * 更新表数据
     * @param array $data
     * @param array $where
     * @param string $table
     * @return bool 返回真|假
     */
    public function update_data($data, $where, $table)
    {
        $data['update_time']    = time();
        return parent::update_data($data, $where, $table);
    }
    
    /**
     * 删除数据
     * @param type $where
     * @param type $table
     * @return type
     */
    public function delete_data($where, $table)
    {
        return parent::delete_data($where, $table);
    }
    
    /**
     * 获取列表数据
     * @param array $options = array('order' => ,'where'=> ,'fields'=> ......)
     * @param string $table
     * @return array 返回列表数据
     */
    public function list_data($options, $table)
    {
        return parent::get_list_term($options, $table);
    }
    
    /**
     * 获取单条数据
     * @param array $where 查询条件
     * @param string  $fields 查询字段
     * @param string $table 表
     * @return array 返回单条数据
     */
    public function get_one($where, $table, $fields = "*")
    {
        return parent::get_one($where, $fields, $table);
    }
    
    /**
     * 获取数据总条数
     * @param string $count_key count($count_key)
     * @param array $where 查询条件
     * @param string $table 表
     * @return int 返回数据总条数
     */
    public function total_count($count_key, $where, $table)
    {
        return parent::count($count_key, $where, $table);
    }
    
    /**
     * 执行sql语句
     * @param type $sql
     * @param type $type
     * @return type
     */
    public function fetch($sql, $type)
    {
        return parent::fetch($sql, $type);
    }
    
    /**
     * 执行LEFT JOIN操作
     * @param type $condition
     * @param type $join_condition
     * @param type $select
     * @param type $tb_a
     * @param type $tb_b
     * @param type $batch
     * @return boolean
     */
    public function left_join($condition, $join_condition,$select,$tb_a,$tb_b,$batch = FALSE)
    {
        if ($condition == '' || $select=='' || $join_condition == '' || $tb_a == '' || $tb_b == '') {
            $this->CI->output_json_return("params_err");
            return false;
        }
        $_limit = '';
        if($batch === false) {
            $_limit = " LIMIT 1";
        }
        $sql = "SELECT ".$select." FROM ".$tb_a." LEFT JOIN ".$tb_b." ON ".$join_condition." WHERE ".$condition.$_limit;
        // var_dump($sql);exit;
        $query = $this->db->query($sql);
        // 记录数据库错误日志
        if ($query === false) {
            return false;
        }
        $ret = array();
        if ($query->num_rows() > 0) {
            $ret = $query->result_array();
            if($batch === false) {
                $ret = $ret[0];
            }
        }
        return $ret;
    }
    
    /**
     * 查询数据总条数
     */
    public function exec_by_sql($sql,$batch = false)
    {
        return parent::exec_by_sql($sql,$batch);
    }
    
    /**
     * 查询数据总条数
     */
    public function delete1($where,$table)
    {
        return parent::delete_data($where,$table);
    }
}
