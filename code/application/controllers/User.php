<?php
/**
 * 用户模块
 * @author	huhong
 * @date	2016-08-24 16:07
 */
class User extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('user_lib');
    }
    
    /**
     * 快速登入
     */
    public function login()
    {
        $content    = file_get_contents("php://input");
        if ($content) {
            $params  = json_decode($content,true);
        } else {
            $params['channel']      = $this->request_params('channel');
            $params['user_id']      = $this->request_params('user_id');
            $params['passport_id']  = $this->request_params('passport_id');
            $params['sn']           = $this->request_params('sn');
            $params['enter_type']   = $this->request_params('enter_type');// 0APP签到登录入口1进入游戏中心2签到游戏登录（可选，默认0）
            $params['sign']         = $this->request_params('sign');
        }
        
        // 校验参数
        if ($params['channel'] == "" || $params['user_id'] == "" || $params['passport_id'] == '' ||  $params['sn'] == ''  || $params['sign'] == "") {
            log_message('error', "login:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        
        // 校验签名
        $this->utility->check_sign($params, $params['sign']);
        $params['enter_type']   = (int)$params['enter_type'];
        if (!$params['enter_type']) {
            $params['enter_type']   = 0;
        }
        $data   = $this->user_lib->do_login($params);
        $this->output_json_return($data);
    }
    
    /**
     * 通过百联账号登录
     */
    public function login_by_loginid()
    {
        log_message('info', 'login:'.$this->user_lib->ip.'  params：'.http_build_query($_REQUEST));
        $params['login_id']     = $this->request_params('login_id');
        $params['password']     = $this->request_params('password');
        $params['channel']      = $this->request_params('channel');
        $params['sn']           = $this->request_params('sn');
        $params['sign']         = $this->request_params('sign');
        if ($params['login_id'] == "" || $params['password'] == "" || $params['channel'] == '' || $params['sn'] == '' || $params['sign'] == "") {
            log_message('error', "login_by_loginid:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        // 校验签名
        $this->utility->check_sign($params, $params['sign']);
        $data   = $this->user_lib->do_login_by_loginid($params);
        $this->output_json_return($data);
    }
    
    /**
     * 注销账户
     */
    public function logout()
    {
        $params = $this->public_params();
        $this->utility->check_sign($params, $params['sign']);
        $this->user_lib->do_logout($params);
        $this->output_json_return();
    }
    
    /**
     * 获取用户信息接口
     */
    public function user_info()
    {
        $params = $this->public_params();
        $this->utility->check_sign($params, $params['sign']);
        $data   = $this->user_lib->get_userinfo($params);
        if (base64_encode(base64_decode($data['name'])) == $data['name']) {
            $data['name']   = base64_decode($data['name']);
        }
        $this->output_json_return($data);
    }
    
    /**
     * 用户反馈
     */
    public function feedback()
    {
        $params             = $this->public_params();
        $params['content']  = urldecode($this->request_params('content'));
        $params['contact']  = urldecode($this->request_params('contact'));
        if ($params['content'] == '' || $params['contact'] == '') {
            log_message('error', "feedback:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        if (mb_strlen($params['contact'],'utf8') > 50) {
            log_message('error', "feedback:输入contact内容超过50;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_CONTACT_TOO_LONG);
            $this->output_json_return();
        }
        if (mb_strlen($params['content'],'utf8') > 500) {
            log_message('error', "feedback:输入content内容超过500;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_FEEDBACK_TOO_MUCH_CONTENT);
            $this->output_json_return();
        }
        $this->user_lib->do_feedback($params);
        $this->output_json_return();
    }
    
    /**
     * 消息列表
     */
    public function message_list()
    {
        $params             = $this->public_params();
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
            log_message('error', "message_list:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->user_lib->get_message_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 删除消息
     */
    public function message_del()
    {
        $params         = $this->public_params();
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            log_message('error', "message_del:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->user_lib->do_message_del($params);
        $this->output_json_return();
    }
    
    public function add_mess()
    {
        $this->user_lib->add_messge();
    }
    
    /**
     * 获取签到列表（7天）
     */
    public function signin_list()
    {
        $params = $this->public_params();
        log_message('info', 'params:'.  http_build_query($params));
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->user_lib->get_signin_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 获取签到列表 （一个月）
     */
    public function signin_for_month()
    {
        $params = $this->public_params();
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->user_lib->get_signin_for_month($params);
        $this->output_json_return($data);
    }
    
    /**
     * 签到接口
     */
    public function signin()
    {
        $params         = $this->public_params();
        if ($params['sign_recive_type'] != 1) {
            log_message('error', "signin:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $params['date'] = $this->request_params('date');
            $params['score']= $this->request_params('score');
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->user_lib->do_signin($params);
        $this->output_json_return();
    }
    
    /**
     * 查询积分接口
     */
    public function query_points()
    {
        $params = $this->public_params();
        $this->utility->check_sign($params,$params['sign']);
        $points_info    = $this->user_lib->query_bl_point($params['uuid']);
        $data['points'] = $points_info['points'];
        $this->output_json_return($data);
    }
    
    /** 
     * 查询积分接口
     */
    public function qpoints()
    {
        // $params = $this->public_params();
        $params['uuid'] = $this->request_params('uuid');
        // $this->utility->check_sign($params,$params['sign']);
        $points_info    = $this->user_lib->query_bl_point($params['uuid']);
        $data['points'] = $points_info['points'];
        $this->output_json_return($data);
    }
    
}
