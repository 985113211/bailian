<?php
/**
 * 充值模块
 * @author	huhong
 * @date	2016-08-24 16:24
 */
class Pay extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('pay_lib');
    }
    
    /**
     * 百联币充值接口
     */
    public function recharge()
    {
        $params             = $this->public_params();
        $params['blcoin']   = (int)$this->request_params('blcoin');
        $params['points']   = $this->request_params('points');
        if ($params['blcoin'] == '') {
            log_message('error', "recharge:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data = $this->pay_lib->do_recharge($params);
        $this->output_json_return($data);
    }

    /**
     * 订单支付接口---只负责支付积分（多次支付）（弃用）
     */
    public function pay_order()
    {
        $params             = $this->public_params();
        $params['id']       = (int)$this->request_params('id');
        $params['points']   = (int)$this->request_params('points');
        if ($params['id'] == '' || $params['points'] == '') {
            log_message('error', "pay_order:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data = $this->pay_lib->do_pay_order($params);
        $this->output_json_return($data);
    }
    
    /**
     * 获取充值订单列表
     */
    public function order_list()
    {
        $params             = $this->public_params();
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
            log_message('error', "order_list:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->pay_lib->get_order_list($params);
        $this->output_json_return($data);
    }
        
    /**
     * 百联币支付异步通知
     */
    public function order_callback()
    {
        // 异步通知，更改订单O_CALLBACK状态 是否回调通知1是0未
        $content        = file_get_contents("php://input");
        $para_          = json_decode($content,true);
        $para_['id']    = $para_['merOrderNo'];
        log_message("info", "order_callback：百联支付回调记录".json_encode($para_).time());
        $result = $this->pay_lib->do_order_callback($para_);
        if ($result) {
            echo '{"resCode":"00100000"}';exit;
        }
        log_message("error", "order_callback：游戏中心回调处理失败;result:".$result.";".json_encode($para_).";执行时间：".date('Y-m-d H:i:s',time()));
        echo 'ERROR';exit;
    }
    
    /**
     * 百联币充值历史记录
     */
    public function recharge_his()
    {
        $params             = $this->public_params();
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
            log_message('error', "recharge_his:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->pay_lib->get_recharge_his($params);
        $this->output_json_return($data);
    }
    
    /**
     * 取消订单
     */
    public function order_cancel()
    {
        $params         = $this->public_params();
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            log_message('error', "order_cancel:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->pay_lib->do_order_cancel($params);
        $this->output_json_return();
    }
    
    /**
     * 删除订单
     */
    public function order_del()
    {
        $params         = $this->public_params();
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            log_message('error', "order_del:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->pay_lib->do_order_del($params);
        $this->output_json_return();
    }
    
    /**
     * 获取充值百联币，所需的积分+人民币值
     */
    public function recharge_info()
    {
        $params = $this->public_params(0);
        $params['blcoin']   = (int)$this->request_params('blcoin');
        $params['points']   = (int)$this->request_params('points');
        if ($params['blcoin'] <= 0 || $params['points'] < 0) {
            log_message('error', "recharge_info:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data = $this->pay_lib->get_recharge_info($params);
        $this->output_json_return($data);
    }
    
    /**
     * 获取货币汇率
     */
    public function currency_rate()
    {
        $params = $this->public_params(0);
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->pay_lib->get_currency_rate($params);
        $this->output_json_return($data);
    }
    
    
    
    /**
     * 支付订单接口（丢弃）
     */
    public function order_pay()
    {
        $params         = $this->public_params();
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            log_message('error', "order_pay:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->pay_lib->do_order_pay($params);
        $this->output_json_return();
    }
    
}
