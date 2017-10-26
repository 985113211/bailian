<?php
/**
 * 活动/专题模块
 * @author	huhong
 * @date	2016-08-24 16:13
 */
class Active extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('active_lib');
    }
    
    /**
     * 活动列表
     */
    public function active_list()
    {
        $params = $this->public_params(0);
        $this->utility->check_sign($params,$params['sign']);
        $params['type'] = 1;
        $data   = $this->active_lib->_get_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 专题列表
     */
    public function subject_list()
    {
        $params = $this->public_params(0);
        $this->utility->check_sign($params,$params['sign']);
        $params['type'] = 2;
        $data   = $this->active_lib->_get_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 活动|专题详情
     */
    public function detail()
    {
        $params         = $this->public_params(0);
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            log_message('error', "detail:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->active_lib->_get_detail($params);
        $this->output_json_return($data);
    }
    
    
}
