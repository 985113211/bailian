<?php
/**
 * 兑换模块
 * @author	huhong
 * @date	2016-08-24 16:19
 */
class Exchange extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('exchange_lib');
    }
    
    /**
     * 直充列表
     */
    public function direct_recharge_list()
    {
        $params             = $this->public_params(0);
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
            log_message('error', "direct_recharge_list:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->exchange_lib->get_direct_recharge_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 直充详情
     */
    public function direct_recharge_info()
    {
        $params         = $this->public_params(0);
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            log_message('error', "direct_recharge_info:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->exchange_lib->get_direct_recharge_info($params);
        $this->output_json_return($data);
    }
    
    /**
     * 直充|优惠券兑换接口
     */
    public function exchange()
    {
        $params             = $this->public_params();
        $params['id']       = (int)$this->request_params('id');
        $params['mobile']   = $this->request_params('mobile');
        if ($params['id'] == '' || $params['mobile'] == '') {
            log_message('error', "exchange:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        // 校验手机号
        if (!preg_match('/^(13|14|15|17|18)\d{9}$/', $params['mobile'])) {
            log_message('error', "exchange:手机号格式不正确;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_MOBILE_FOMAT_FAIL);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->exchange_lib->do_exchange($params);
        $this->output_json_return();
    }
    
    /**
     * 兑换历史记录
     */
    public function exchange_his()
    {
        $params             = $this->public_params();
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
            log_message('error', "exchange_his:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->exchange_lib->get_exchange_his($params);
        $this->output_json_return($data);
    }
    
    /**
     * 删除兑换记录（单条删除）
     */
    public function del_exchange()
    {
        $params         = $this->public_params();
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            log_message('error', "del_exchange:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->exchange_lib->do_del_exchange_his($params);
        $this->output_json_return();
    }
    
    /**
     * 获取消息快报
     */
    public function exchange_mess()
    {
        $params = $this->public_params(0);
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->exchange_lib->get_exchange_mess($params);
        $this->output_json_return($data);
    }
    
    /**
     * 热门兑换列表
     */
    public function hot_list()
    {
        $params             = $this->public_params(0);
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
            log_message('error', "hot_list:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        
        $data   = $this->exchange_lib->get_exchange_hot_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 查询兑换结果
     */
    public function query_exchange()
    {
        $params = $this->public_params();
        $data   = $this->exchange_lib->get_mobile_fare_exchange(72);
        $this->output_json_return($data);
    }
    
}
