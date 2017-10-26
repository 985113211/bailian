<?php
class Cron extends MY_Controller{
    
    /**
     * 白鹭网游同步
     */
    public function index()
    {
        $this->load->library('game_lib');
        $res    = $this->game_lib->bailu_game_sync();
        echo 'SUCCESS';exit;
    }
    
    /**
     * 百联币充值---根据订单的失效，处理订单状态（当未支付订单超过30分钟，执行订单退款、关闭操作）
     */
    public function order_handle()
    {
        $this->load->library('pay_lib');
        $res    = $this->pay_lib->order_expire_handle();
        if ($res) {
            echo 'SUCCESS';EXIT;
        }
        echo 'ERROR';EXIT;
    }
    
    /**
     * 数据统计
     */
    public function data_statistics()
    {
        $this->load->library('statistics_lib');
        // 签到数据统计
        $data['sign_user_day']                  = (int)$this->statistics_lib->sign_user_day_num();// 每日签到人数
        $data['appendsign_user_day']            = (int)$this->statistics_lib->appendsign_user_day_num();// 当日补签人数
        $data['sign_get_points_day']            = $this->statistics_lib->sign_get_points_day();// 每日签到发放积分（单位：元）
        $data['appendsign_reduce_points_day']   = $this->statistics_lib->appendsign_reduce_points_day();//  每日补签回收积分（单位：元）
        $data['sign_pool']                      = $data['sign_get_points_day']-$data['appendsign_reduce_points_day'];
        // 将每日签到数据写入数据库
        $this->statistics_lib->insert_sign_data_day($data);
        
        /* （已弃用）该功能已移植到管理后台
        // 游戏数据统计
        $data['user_add_day']           = (int)$this->statistics_lib->user_everyday_add();// 统计每日新增用户
        $data['user_total']             = (int)$this->statistics_lib->user_total();// 统计用户总数
        $data['DAU']                    = (int)$this->statistics_lib->DAU_day();// 日活跃用户
        $data['payfee_user_total']      = (int)$this->statistics_lib->payfee_user_total();// 统计付费用户总数（包含积分、人民币）
        $data['payfee_user_day']        = (int)$this->statistics_lib->payfee_user_day();// 统计每日付费用户
        $data['payfee_rate_day']        = 0;// 日人民币付费率
        $data['payfee_user_newadd']     = (int)$this->statistics_lib->payfee_user_newadd();; // 每日新增付费用户数
        $data['payrmb_user_total']      = (int)$this->statistics_lib->payrmb_user_total();// 人民币付费 总用户
        $data['payrmb_user_day']        = (int)$this->statistics_lib->payrmb_user_day();// 人民币付费 -日用户
        $data['blcoin_income_total']    = (int)$this->statistics_lib->blcoin_income_total();// 百联币收入 -总额
        $data['blcoin_income_day']      = (int)$this->statistics_lib->blcoin_income_day();//百联币收入 -日收入 --- 
        $data['rmb_pay_total']          = (int)$this->statistics_lib->rmb_pay_total();// 人民币充值金额  -总额
        $data['rmb_pay_day']            = (int)$this->statistics_lib->rmb_pay_day();// 人民币充值金额 -日充值  ---
        $data['point_pay_total']        = (int)$this->statistics_lib->point_pay_total(); // 百联积分充值金额  -总额
        $data['point_pay_day']          = (int)$this->statistics_lib->point_pay_day(); // 百联积分充值金额  -日充值 ---
                
        // 将统计数据，写入txt
        $data_str  = $data['user_add_day']."_".$data['user_total']."_".$data['DAU']."_".$data['payfee_user_total']."_".
                $data['payfee_user_day']."_".$data['payrmb_user_total']."_".
                $data['payrmb_user_day']."_".$data['payfee_rate_day']."_".$data['payfee_user_newadd']."_".$data['blcoin_income_total']."_".$data['blcoin_income_day']."_".$data['rmb_pay_total']."_".
                $data['rmb_pay_day']."_".$data['point_pay_total']."_".$data['point_pay_day']."_".$data['sign_user_day']."_".$data['appendsign_user_day']."_".$data['sign_get_points_day']."_".$data['appendsign_reduce_points_day']."_".$data['sign_pool'];
                
        file_put_contents('/log/statistics/statistict_'.date('Ymd',strtotime("-1 day")).".txt", $data_str);
         */
        ECHO 'OK';
    }
    
    /**
     * 月记录签到统计（每月1号 03点执行）
     */
    public function statistics_month_sign()
    {
        set_time_limit(0); 
        $this->load->library('statistics_lib');
        // 数据统计  appendsign_num
        $appendsign_num             = $this->statistics_lib->one_appendsign_num();
        $data['one_appendsign']     = (int)$appendsign_num['one_appendsign_num'];// 补签一次总人数
        $data['two_appendsign']     = (int)$appendsign_num['two_appendsign_num'];// 补签二次总人数
        $data['three_appendsign']   = (int)$appendsign_num['three_appendsign_num'];// 补签三次总人数
        $data['four_appendsign']    = (int)$appendsign_num['four_appendsign_num'];// 补签四次总人数
        $data['five_appendsign']    = (int)$appendsign_num['five_appendsign_num'];// 补签五次总人数
        
        $data['appendsign_reduce']  = $this->statistics_lib->appendsign_reduce_points_month();// 补签回收积分
        $data['sign_get']           = $this->statistics_lib->sign_get_points_month();// 签到发放积分
        $data['sign_pool']          = $data['sign_get']-$data['appendsign_reduce'];;// 签到积分池
        
        $data['weekly_continue']        = (int)$this->statistics_lib->weekly_continue_sign();// 一周连续签到人数
        $data['two_weekly_continue']    = (int)$this->statistics_lib->two_weekly_continue_sign();// 两周连续签到人数
        
        $data['three_weekly_continue']  = (int)$this->statistics_lib->three_weekly_continue_sign();// 三周连续签到人数
        $data['month_continue_sign']    = (int)$this->statistics_lib->month_continue_sign();// 一个月连续签到人数
        $data['total_appendsign']       = (int)$appendsign_num['total_appendsign_num'];// 补签会员（总人数）
        $data['total_sign']             = (int)$this->statistics_lib->total_sign_num();// 总签到人数（总人数）
        
        // 将每月签到数据记录到数据库
        $this->statistics_lib->insert_sign_data_month($data);
        
        // (已弃用:月签到数据改为插入数据库)将月签到统计数据，写入txt
        /*
        $data_str  = $data['one_appendsign']."_".$data['two_appendsign']."_".$data['three_appendsign']."_".$data['four_appendsign']."_".
                $data['five_appendsign']."_".$data['appendsign_reduce']."_".
                $data['sign_get']."_".$data['sign_pool']."_".$data['weekly_continue']."_".$data['two_weekly_continue']."_".$data['three_weekly_continue']."_".$data['month_continue_sign']."_".
                $data['total_appendsign']."_".$data['total_sign'];    
        file_put_contents('/log/statistics/sign_month_'.date('Ymd',strtotime("-1 day")).".txt", $data_str);
        */
        // 每月统计一次--满月签到人数的百联会员ID
        $uuid_arr  = $this->statistics_lib->month_continue_sign(2);
        $this->load->library('user_lib');
        $member_ids= '';
        if (count($uuid_arr)) {
            foreach ($uuid_arr as $k=>$v) {
                $u_info = $this->user_lib->get_register_info($v['uuid']);
                $member_ids .=$this->user_lib->get_register_info($v['uuid'])['user_id'].",";
            }
        }
        file_put_contents('/log/statistics/month_sign_user_ids'.date('Ymd',strtotime("-1 day")).".txt", $member_ids);
        ECHO 'OK';
    }
    
    /**
     * 兑换置灰自动检测
     */
    public function exchange_handle()
    {
        $this->load->library('exchange_lib');
        $this->exchange_lib->exchange_query_handle();
        echo 'OK';
    }
    
    /**
     * ERP百联币统计
     * 每日早上3点同步一次ERP系统
     */
    public function erp_statist()
    {
        set_time_limit(180);
        $this->load->library('statistics_lib');
        if ($_GET['date']) {
            $date['start_time'] = strtotime('2016-12-01');
            $date['end_time']   = strtotime('2017-01-01');
        }
        $res = $this->statistics_lib->erp_statist_of_blcoin($date);// 每日执行一次
        echo $res;
    }
    
    /**
     * 定期执行开奖操作
     */
    public function exec_dbopen()
    {
        $this->load->library('duobao_lib');
        $res = $this->duobao_lib->dbopen();
        echo $res;
    }
    
    /**
     * (管理后台数据统计)定时统计--日活跃、次日留存、3日留存、7日留存
     * 每日02:20（凌晨2点20分执行）
     */
    public function backdata_statist()
    {
        $this->load->library('statistics_lib');
        $res = $this->statistics_lib->do_backdata_statist();// 每日执行一次
        echo $res;
    }
        
}

