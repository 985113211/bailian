<?php
class Game extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('game_lib');
    }
    
    /**
     * 单机游戏列表
     */
    public function normal_list()
    {
        $params             = $this->public_params(0);
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
            log_message('error', "normal_list:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->game_lib->get_normal_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 网游列表
     */
    public function online_list()
    {
        $params             = $this->public_params(0);
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
            log_message('error', "online_list:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->game_lib->get_online_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 下载游戏列表
     */
    public function download_list()
    {
        $params             = $this->public_params(0);
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
            log_message('error', "download_list:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->game_lib->get_download_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 排行榜列表
     */
    public function ranking_list()
    {
        $params             = $this->public_params(0);
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
            log_message('error', "ranking_list:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->game_lib->get_ranking_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 游戏详情
     */
    public function detail()
    {
        $params         = $this->public_params(0);
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            log_message('error', "detail:游戏详情参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->game_lib->game_detail($params);
        $this->output_json_return($data);
    }
    
    /**
     * 收藏游戏
     */
    public function favorite()
    {
        $params         = $this->public_params();
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            log_message('error', "favorite:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->game_lib->do_favorite($params);
        $this->output_json_return();
    }
    
    /**
     * 取消收藏游戏
     */
    public function cancel_favorite()
    {
        $params         = $this->public_params();
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            log_message('error', "cancel_favorite:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $this->game_lib->do_cancel_favorite($params);
        $this->output_json_return();
    }
    
    /**
     * 收藏列表
     */
    public function favorites_list()
    {
        $params             = $this->public_params();
        $params['offset']   = (int)$this->request_params('offset');
        $params['pagesize'] = $this->request_params('pagesize');
        if ($params['offset'] < 0) {
            log_message('error', "favorites_list:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        if (!$params['pagesize']) {
            $params['pagesize'] = self::PAGESIZE;
        }
        $data   = $this->game_lib->get_favorites_list($params);
        $this->output_json_return($data);
    }
    
    /**
     * 前10名排行版
     */
    public function score_ranking()
    {
        $params = $this->public_params();
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->game_lib->get_score_ranking($params);
        $this->output_json_return($data);
    }
    
    /**
     * 游戏分享
     */
    public function share()
    {
        $params         = $this->public_params(0);
        $params['id']   = (int)$this->request_params('id');
        if ($params['id'] == '') {
            log_message('error', "share:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params,$params['sign']);
        $data   = $this->game_lib->do_share($params);
        $this->output_json_return($data);
    }
    
    /**
     * 统计游戏被玩次数、分享被玩次数
     */
    public function pv_statistics()
    {
        $params['id']   = (int)$this->request_params('id');
        $params['type'] = $this->request_params('type');
        if ($params['id'] == '') {
            log_message('error', "pv_statistics:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->game_lib->do_pv_statistics($params);
        $this->output_json_return();
    }
    
    /*
     * 接收白鹭服务器的支付请求（唤起前端支付界面）
     */
    public function wakeup()
    {
        $params['uuid']         = $this->request_params('userId');
        $params['nickname']     = $this->request_params('userName');
        $params['game_id']      = $this->request_params('gameId');
        $params['goods_id']     = $this->request_params('goodsId');
        $params['goods_name']   = $this->request_params('goodsName');
        $params['money']        = $this->request_params('money'); // 元
        $params['order_id']     = $this->request_params('egretOrderId');
        $params['ext']          = $this->request_params('ext'); // 此参数为透传参数，通知支付结果接口调用的时候原样返回
        $params['game_url']     = $this->request_params('gameUrl');
        $params['time']         = $this->request_params('time');
        $params['sign']         = $this->request_params('sign');
        // 校验参数
        if ($params['uuid'] == '' || $params['game_id'] == '' || $params['goods_id'] == '' || 
                $params['goods_name'] === '' || $params['money'] == '' || $params['order_id'] == '' || $params['ext'] == '' || $params['game_url'] == '' || $params['time'] == '' || $params['sign'] == '') {
            $this->error_->set_error(Err_Code::ERR_PARA);
            log_message('error', "wakeup:网游支付异常参数异常;".$this->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $url = $this->passport->get('bailu_callback_url')."?type=error&msg=支付异常";
            echo "<script>window.location.href='".$url."'</script>";exit;
        }
        // 依据白鹭签名规则，校验签名
        $key_arr = array(
            'appId'         => $this->passport->get('bailu_channel_id'),
            'egretOrderId'  => $params['order_id'],
            'gameId'        => $params['game_id'],
            'goodsId'       => $params['goods_id'],
            'money'         => $params['money'],
            'time'          => $params['time'],
            'userId'        => $params['uuid'],
        );
        $sign   = $this->game_lib->sign_for_bailu($key_arr);
        if ($sign != $params['sign']) {
            log_message('error', "wakeup:网游支付异常-参数校验错误;".$this->input->ip_address().";请求参数:".  http_build_query($params).";白鹭签名参数：".  json_encode($key_arr).",白鹭sign：".$sign.";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARAM_SIGN);
            $url = $this->passport->get('bailu_callback_url')."?type=error&msg=支付签名异常";
            echo "<script>window.location.href='".$url."'</script>";exit;
        }
        $url    = $this->game_lib->do_wakeup_php($params);
        if (!$url) {
            log_message('error', "wakeup:网游支付异常错;".$this->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $url = $this->passport->get('bailu_callback_url')."?type=error&msg=支付操作异常";
            echo "<script>window.location.href='".$url."'</script>";exit;
        }
        log_message('info', "wakeup:网游支付-唤起前端支付页面成功;".$this->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
        echo "<script>window.location.href='".$url."'</script>";
    }
    
    /*
     * 前端发起购买请求（前端确认支付请求）
     */
    public function buy_prop() 
    {
        $params             = $this->public_params();
        $params['id']       = $this->request_params('id');
        // 校验参数
        if ($params['id'] == '') {
            log_message('error', "normal_list:参数错误;请求参数params:".http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->error_->set_error(Err_Code::ERR_PARA);
            $this->output_json_return();
        }
        $this->utility->check_sign($params, $params['sign']);
        $this->game_lib->do_buy_prop($params);
        $this->output_json_return();
    }
    
    /**
     * 获取签到游戏URL
     */
    public function sign_game()
    {
        $params = $this->public_params();
        $this->utility->check_sign($params, $params['sign']);
        $data   = $this->game_lib->get_sign_game($params);
        $this->output_json_return($data);
    }
    
    /**
     * 购买收费游戏
     */
    public function buy()
    {
        $params         = $this->public_params();
        $params['id']   = $this->request_params('id');
        $this->utility->check_sign($params, $params['sign']);
        $this->game_lib->do_buy($params);
        $this->output_json_return();
    }
    
}
