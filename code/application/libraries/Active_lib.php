<?php
/**
 * 活动/专题操作
 * @author	huhong
 * @date	2016-08-24 16:14
 */
class Active_lib extends Base_lib {
    public function __construct() {
        parent::__construct();
        $this->load_model('active_model');
    }
    
    /**
     * 获取专题|活动列表
     */
    public function _get_list($params)
    {
        $table  = "bl_active";
        $options['where']   = array('A_TYPE'=>$params['type'],'STATUS'=>0);
        $options['fields']  = "IDX AS id,A_NAME AS name,A_TOPIMG AS topimg,A_ACTIVETYPE type,A_GAMEID game_id";
        $data['list']       = $this->CI->active_model->list_data($options,$table);
        if (!$data['list']) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $path   = $this->CI->passport->get('game_url');
        foreach ($data['list'] as &$v) {
            $v['topimg']    = $path.$v['topimg'];
        }
        return $data;
    }
    
    /**
     * 获取活动|专题详情
     */
    public function _get_detail($params)
    {
        $table  = "bl_active";
        $where  = array('IDX'=>$params['id'],'STATUS'=>0);
        $fields ="IDX id,A_ACTIVETYPE type,A_NAME name,A_TOPIMG topimg,A_IMG img,A_INFO info,A_GAMEID game_id,A_STARTDATE start_time,A_ENDDATETIME end_time";
        $data   = $this->CI->active_model->get_one($where,$table,$fields);
        if (!$data) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $data['status'] = 2;
        $time           = time();
        if ($data['start_time'] > $time) {
            $data['status'] = 1;
        } else if($time > $data['end_time']){
            $data['status'] = 3;
        }
        $path           = $this->CI->passport->get('game_url');
        $data['img']    = $path.$data['img'];
        $data['topimg'] = $path.$data['topimg'];
        return $data;
    }
    
    
    
}

