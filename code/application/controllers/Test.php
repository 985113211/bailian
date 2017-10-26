<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('test_lib');
        $this->load->library('duobao_lib');
    }
    public function index()
    {
        $params['sn']   = 'A7CF1B51-0F93-411A-82DD-E37FCE2F6568';
        $params['sign'] = 'e2250319814d2c33f2c1850e1e500f72';
        $params['channel']  = "1";
        $params['user_id']  = '100000000031450';
        $params['passport_id']  = "82131cf73d_1450";
        var_dump(json_encode($params));exit;
        
        $list   = $this->test_lib->test_();
        var_dump($list);exit;
        phpinfo();
    }
    
    public function view()
    {
        $res    = $this->user_lib->add_bl_point();
        var_dump($res);
    }
    
    public function bailu_sync()
    {
        $this->load->library('game_lib');
        $this->game_lib->bailu_game_sync();
    }
    
	/**
     * 获取access_token
     */
    public function access_token()
    {
        $result = $this->user_lib->get_access_token(15509,1111,1111);
        var_dump($result);exit;
    }
    
    public function qry_point()
    {
        $params = array(
            'uuid'=>15509,
        );
        $passport_id    = '123456_9c6fdf51bec146a582e5fbd3d8a3977b_2465';
        $res    = $this->user_lib->query_bl_point(1111,1111,$passport_id);
        var_dump($res);
    }
    
    public function public_key()
    {
        $res    = $this->user_lib->public_key(15509);
        var_dump($res);
    }
    
    public function login_()
    {
        $passport_id    = '123456_9c6fdf51bec146a582e5fbd3d8a3977b_2465';
        $res    = $this->user_lib->login_for_passportid($passport_id,1111,1111);
        var_dump($res);exit;
    }
    
    public function login()
    {
        $passport_id    = '123456_9c6fdf51bec146a582e5fbd3d8a3977b_2465';
        $res    = $this->user_lib->do_login();
        var_dump($res);exit;
    }
    
    public function test_repeat_sign()
    {
        $this->load->library('user_lib');
        $params['login_id']     = '13122012580';
        $params['password']     = 'hp123456';
        $params['channel']      = 10;
        $params['sn']           = 'sntest1238713123';
        $params['sign']         = 'sign';
        $data   = $this->user_lib->do_login_by_loginid($params);// 获取用户信息
        
        $params_['channel']  = $params['channel'];
        $params_['uuid']     = $data['uuid'];
        $params_['token']    = $data['token'];
        $params_['sign']     = $params['sign'];
        $aa = $this->user_lib->do_signin($params_);
        $bb = $this->user_lib->do_signin($params_);
        $cc = $this->user_lib->do_signin($params_);
        $dd = $this->user_lib->do_signin($params_);
        $ee = $this->user_lib->do_signin($params_);
        var_dump($aa.$bb.$cc.$dd.$ee);
        
    }
    
    public function ssc()
    {
        $ssc_list   = $this->duobao_lib->sscopen_info(1);
        // $ssc_list   = $this->duobao_lib->ssc_luckno();
        // $ssc_info   = $ssc_list['data'][0];
        var_dump($ssc_list);exit;
    }
    
    public function json_()
    {
        $para_['merOrderNo']    = "BLO5923ac5dbd5a457";
        echo json_encode($para_);
    }
}
