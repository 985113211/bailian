<?php
/**
 * 用户操作
 * @author	huhong
 * @date	2016-08-24 16:11
 */
class User_lib extends Base_lib {
    public function __construct() {
        parent::__construct();
        $this->load_model('user_model');
    }
    
    /**
     * 登录操作
     * @param type $params
    */
    public function do_login($params)
    {
        $this->CI->user_model->start();
        $key        = $params['user_id'].$params['channel'];
        $bind_res   = $this->login_for_passportid($params['passport_id'],$params['sn'],$params['channel'],$key);
        if (!$bind_res) {
            return false;
        }
        $params['passport_id']  = (string)$bind_res['passportId'];
        $params['name']     = $bind_res['nickName']?(string)$bind_res['nickName']:(string)$bind_res['member_name'];
        $params['mobile']   = (string)$bind_res['mobile'];
        $params['image']    = (string)$bind_res['mediaCephUrl'];
        $data               = $this->register_user($params);
        return $data;
    }
    
    /**
     * 注册用户
     * @param type $params
     * @return boolean
     */
    public function register_user($params)
    {
        // 将用户名转成二进制
        if ($params['name']) {
            $params['name'] = base64_encode($params['name']);
        } else {
            $params['name'] = '';
        }
        $this->CI->user_model->start();
        //查询该用户是否已注册
        $_uuid = $this->chk_user_account($params['user_id']);
        if ($_uuid === false) {
            //新注册用户 插入用户表
            $data   = array(
                'U_NAME'            => $params['name'],
                'U_ICON'            => $params['image'],
                'U_SEX'             => 0,
                'U_BLCOIN'          => 0,
                'U_MOBILEPHONE'     => $params['mobile'],
                'U_LASTLOGINTIME'   => $this->zeit,
                'U_SN'              => $params['sn'],
                'U_CHANNEL'         => $params['channel'],
                'U_PASSPORTID'      => $params['passport_id'],
                'STATUS'            => 0,
            );
            $_uuid = $this->CI->user_model->insert_data($data,'bl_user');
            if (!$_uuid) {
                log_message('error', "register_user:新注册用户插入失败;".$this->CI->input->ip_address().";请求参数params:".  json_encode($params).";插入数据:".json_encode($data).";执行时间".date('Y-m-d H:i:s',time()));
                $this->CI->user_model->error();
                $this->CI->error_->set_error(Err_Code::ERR_INSERT_USER_INFO_FAIL);
                return false;
            }
            //插入用户登入表
            $data2  = array(
                'U_USERIDX'     => $_uuid,
                'U_ACCOUNTID'   => $params['user_id'],
                'U_ACCOUNTNAME' => $params['name'],
                'U_CHANNEL'     => $params['channel'],
                'STATUS'        => 0,
            );
            $rst = $this->CI->user_model->insert_data($data2,'bl_userlogin');
            if (!$rst) {
                log_message('error', "register_user:用户登录记录表插入失败;".$this->CI->input->ip_address().";请求参数params:".  json_encode($params).";插入数据:".json_encode($data2).";执行时间".date('Y-m-d H:i:s',time()));
                $this->CI->user_model->error();
                $this->CI->error_->set_error(Err_Code::ERR_INSERT_USERLOGIN_FAIL);
                return false;
            }
        } else {
            // 更新用户信息
            $where  = array('IDX'=>$_uuid,'status'=>0);
            $fields = array(
                'U_NAME'            => $params['name'],
                'U_ICON'            => $params['image'],
                'U_SEX'             => 0,
                'U_MOBILEPHONE'     => $params['mobile'],
                'U_LASTLOGINTIME'   => $this->zeit,
                'U_SN'              => $params['sn'],
                'U_CHANNEL'         => $params['channel'],
                'U_PASSPORTID'      => $params['passport_id'],
            );
            $rst = $this->CI->user_model->update_data($fields,$where,'bl_user');
            if (!$rst) {
                log_message('error', "register_user:更新用户信息表失败;".$this->CI->input->ip_address().";请求参数params:".  json_encode($params).";更新字段:".json_encode($fields).";执行时间".date('Y-m-d H:i:s',time()));
                $this->CI->user_model->error();
                $this->CI->error_->set_error(Err_Code::ERR_UPDATE_USERINFO_FAIL);
                return false;
            }
        }
        
        //记录用户登录历史记录
        $data3  = array(
            'L_USERIDX'     => $_uuid,
            'L_NAME'        => $params['name'],
            'L_CHANNEL'     => $params['channel'],
            'L_IP'          => $this->ip,
            'L_SN'          => $params['sn'],
            'L_PASSPORTID'  => $params['passport_id'],
            'L_ENTERTYPE'   => $params['enter_type'],
            'STATUS'        => 0,
        );
        $rst = $this->CI->user_model->insert_data($data3,'bl_loginlog');
        if (!$rst) {
            log_message('error', "register_user:记录用户登录历史失败;".$this->CI->input->ip_address().";请求参数params:".  json_encode($params).";插入字段:".json_encode($data3).";执行时间".date('Y-m-d H:i:s',time()));
            $this->CI->user_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_INSERT_USER_LOGINLOG_FAIL);
            return false;
        }
        $this->CI->user_model->success();
        //获取用户信息
        $data   = $this->get_userinfo(array('uuid'=>$_uuid));
        if (!$data) {
            log_message('error', "register_user:获取用户信息失败;".$this->CI->input->ip_address().";请求参数params:".  json_encode($params).";执行时间".date('Y-m-d H:i:s',time()));
            return false;
        }
        if (base64_encode(base64_decode($data['name'])) == $data['name']) {
            $data['name']   = base64_decode($data['name']);
        }
        $data['token']  = $this->CI->gen_login_token($_uuid, $params['channel']);
        log_message('info', "register_user:用户登录成功;".$this->CI->input->ip_address().";请求参数params:".  json_encode($params).";用户信息：".  json_encode($data).";执行时间".date('Y-m-d H:i:s',time()));
        return $data;
    }
    
    /**
     * 校验用户是否已注册
     */
    public function chk_user_account($user_id)
    {
        $where          = array('U_ACCOUNTID'=>$user_id,'status'=>0);
        $fields         = "U_USERIDX AS uuid";
        $register_info  = $this->CI->user_model->get_one($where, 'bl_userlogin', $fields);
        if ($register_info['uuid']) {
            return $register_info['uuid'];
        }
        return false;
    }
    
    /**
     * 获取用户注册信息
     */
    public function get_register_info($uuid)
    {
        $condition      = "A.IDX = ".$uuid." AND A.STATUS = 0 AND B.STATUS = 0";
        $join_condition = "A.IDX = B.U_USERIDX";
        $select         = "A.IDX uuid,A.U_NAME name,A.U_BLCOIN blcoin,A.U_MOBILEPHONE mobile,A.U_SN sn,A.U_CHANNEL channel,A.U_PASSPORTID passport_id,B.U_ACCOUNTID user_id";
        $info   = $this->CI->user_model->left_join($condition, $join_condition,$select,'bl_user A','bl_userlogin B');
        if (!$info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        return $info;
    }
    
    /**
     * 注销用户
     * @param type $params
     * @return boolean
     */
    public function do_logout($params)
    {
        $token_key  = $this->CI->passport->get('token_key');
        $key        = $params['uuid']."_".$params['channel']."_".$token_key;
        $token_info = $this->CI->cache->memcached->get($key);
        if (!$token_info) {
            return true;
        }
        $this->CI->cache->memcached->delete($key);
        return true;
    }
    
    /**
     * 获取用户基本信息
     */
    public function get_userinfo($params)
    {
        $where  = array('IDX'=>$params['uuid'],'status'=>0);
        $fields = "IDX AS uuid,U_NAME AS name ,U_ICON image,U_SEX sex,U_BLCOIN blcoin,U_MOBILEPHONE mobile,U_LASTLOGINTIME lastlogin_time,U_SN sn,U_CHANNEL channel,U_PASSPORTID passport_id,ROWTIME AS create_time";
        $info   = $this->CI->user_model->get_one($where,'bl_user',$fields);
        if (!$info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        return $info;
    }
    
    /**
     * 用户反馈接口
     * @param type $params
     */
    public function do_feedback($params)
    {
        $this->CI->user_model->start();
        $name   = $this->get_userinfo($params)['name'];
        $data   = array(
            'F_USERIDX'     => $params['uuid'],
            'F_NICKNAME'    => $name,
            'F_INFO'        => $params['content'],
            'F_CONTACT'     => $params['contact'],
            'F_IP'          => $this->ip,
            'STATUS'        => 1,
        );
        $ist_res    = $this->CI->user_model->insert_data($data,'bl_feedback');
        if (!$ist_res) {
            log_message('error', 'do_feedback：用户反馈信息插入失败'.$this->CI->input->ip_address().';请求参数params:'.  json_encode($params).';插入数据:'.  json_encode($data).';执行时间'.date('Y-m-d H:i:s',time()));
            $this->CI->user_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_FEEDBACK_INSERT_FAIL);
            $this->CI->output_json_return();
        }
        $this->CI->user_model->success();
        log_message('info', 'do_feedback：用户反馈信息插入成功'.$this->CI->input->ip_address().';请求参数params:'.  json_encode($params).';插入数据:'.  json_encode($data).';执行时间'.date('Y-m-d H:i:s',time()));
        return true;
    }
    
    /**
     * 获取消息列表
     * @param type $params
     */
    public function get_message_list($params)
    {
        $user_info      = $this->get_userinfo($params);
        $sql            = "SELECT COUNT(IDX) AS num FROM bl_mailbox  where  STATUS = 0 AND  UNIX_TIMESTAMP(ROWTIME) >= ".strtotime($user_info['create_time'])." AND IDX NOT IN (SELECT M_MAILIDX FROM bl_mailbox_status WHERE M_USERIDX = ".$params['uuid'].")";
        $total_count    = $this->CI->user_model->exec_by_sql($sql);
        if (!$total_count['num']) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            $this->CI->output_json_return();
        }
        $data['pagecount']  = ceil($total_count['num']/$params['pagesize']);
        $select             = "IDX AS id,M_NAME AS title,M_INFO AS content,M_SENDER AS sender,ROWTIME AS sender_time";
        $sql_2              = "SELECT ".$select." FROM bl_mailbox AS A where  STATUS = 0 AND  UNIX_TIMESTAMP(ROWTIME) >= ".strtotime($user_info['create_time'])." AND IDX NOT IN (SELECT M_MAILIDX FROM bl_mailbox_status WHERE M_USERIDX = ".$params['uuid'].") ORDER BY IDX DESC LIMIT ".$params['offset'].",".$params['pagesize'];
        $data['list']       = $this->CI->user_model->exec_by_sql($sql_2,true);
        return $data;
    }
    
    /**
     * 删除消息通知
     * @param type $params
     */
    public function do_message_del($params)
    {
        $table  = "bl_mailbox_status";
        $data   = array(
            'M_MAILIDX' => $params['id'],
            "M_USERIDX" => $params['uuid'],
            'M_STATUS'  => 1,
            'STATUS'    => 0,
        );
        $res    = $this->CI->user_model->insert_data($data,$table);
        if (!$res) {
            log_message('error', 'do_message_del：删除消息通知失败'.$this->CI->input->ip_address().';请求参数params:'.  json_encode($params).';插入数据:'.  json_encode($data).';执行时间'.date('Y-m-d H:i:s',time()));
            $this->CI->error_->set_error(Err_Code::ERR_MESSAGE_DEL_FAIL);
            $this->CI->output_json_return();
        }
        log_message('info', 'do_message_del：删除消息通知成功'.$this->CI->input->ip_address().';请求参数params:'.  json_encode($params).';插入数据:'.  json_encode($data).';执行时间'.date('Y-m-d H:i:s',time()));
        return true;
    }
    
    /**
     * 签到列表
     */
    public function get_signin_list($params)
    {
        // 获取签到配置文件
        $conf_info  = $this->signin_conf_info();
        if (!$conf_info) {
            $this->CI->output_json_return();
        }
        $begindate  =date('Ym01');// 当月第一天 20160830 00:00:00
        $enddate    = date('Ymd',strtotime("$begindate +1 month"));// 当月最后一天 20160901 00:00:00
        $days   = date('t');// 当月总天数
        $day    = date("d");// 当天是几号
        // 当前整个星期 开始--结束日期
        $mod    = $day%7;
        if($mod) {
            $mod=$mod-1;
            $start_time  = date('Ymd',strtotime("-".$mod." day"));
            if (($day-$mod+7) > $days) {
                $end_time   = date('Ymd',strtotime("$begindate +1 month"));
                $last_week  = 1;
            } else {
                $end_time   = date('Ymd',strtotime("+".(7-$mod)." day"));
            }
        } else {
            $start_time = date('Ymd',strtotime("-7 day"));
            $end_time   = date('Ymd');
        }
        $surplus_day    = $end_time-date('Ymd');
        // 查询用户当月签到记录
        $pass_append_num    = 0;    // 过去补签次数
        $curr_append_num    = 0;    // 本周补签次数
        $sign_list          = array();
        $sign_his       = $this->signin_his_info($params['uuid'], $begindate, $enddate);
        if ($sign_his) {
            foreach ($sign_his as $k=>$v) {
                if ($v['sign_type'] == 2) {
                    if ($v['date'] > $start_time && $v['date'] <= $end_time) {
                        $curr_append_num ++;// 本周补签次数
                    } else {
                        $pass_append_num ++;// 以往补签次数
                    }
                }
                if ($v['date'] > $start_time && $v['date'] <= $end_time) {
                    $sign_list[]    = $v['date'];// 签到记录
                } 
            }
        }
        
        // 获取补签消耗
        $signin_status  = 2;
        $max_append     = $this->CI->passport->get('max_append_signin');
        $append_total   = $pass_append_num+$curr_append_num;// 总补签次数
        if($append_total >= $max_append) {
            $append_expend  = 0;
            $signin_status  = 3;
        } else {
            switch ($append_total) {
                case 0:
                    $append_expend  = $conf_info['feplenish_first'];
                    break;
                 case 1:
                    $append_expend  = $conf_info['replenish_second'];
                    break;
                 case 2:
                    $append_expend  = $conf_info['replenish_third'];
                    break;
                 case 3:
                    $append_expend  = $conf_info['replenish_fourth'];
                    break;
                case 4:
                    $append_expend  = $conf_info['feplenish_fifth'];
                    break;
                default:
                    $append_expend  = 0;
                    break;
            }
        }
        
        for($i==0;$i<7;$i++) {
            $signin_status  = 3;
            $d_curr   = $i?date('d',strtotime("$start_time +$i day")):date('d',  strtotime($start_time));
            if (in_array(date('Ym').$d_curr,$sign_list)) {
                $signin_status  = 1;
            }else if (date('Ym').$d_curr >= $start_time && date('Ym').$d_curr < date('Ymd')){
                $signin_status  = 2;
            }
            $data['list'][] = array(
                'date'          => $i?date('m.d',strtotime("$start_time +$i day")):date('m.d',  strtotime($start_time)),
                'signin_status' => $signin_status,
                'is_weekly'     => $d_curr%7?0:1,
                'is_monthly'    => $d_curr==$days?1:0,
                'expend_point'  => $append_expend,
                'get_point'     => $conf_info['daily'],
            );
        }
        // 签到文案提示
        $signed_info    = $this->CI->passport->get('signed_info');
        if ($last_week) {
            if (!$pass_append_num && !$curr_append_num) {//好厉害，再坚持[XX]天就可以额外获得1000积分的月奖励啦！
                $info   = str_replace('[XX]', $surplus_day, $signed_info['last_insist']);
            }
            if (!$pass_append_num && $curr_append_num) {
                $info   = $signed_info['last_append'];
            }
            if ($pass_append_num) {
                $info   = $signed_info['encourage_signin'];
            }
        } else {
            if ($max_append <=$append_total) {
                $info   = str_replace('[XX]', $surplus_day, $signed_info['week_signin']);// 加油哦，再坚持[XX]天就可以额外获得50积分的周奖励啦！
            } else {
                $info   = $signed_info['week_append'];
            }
        }
        $data['info']   = $info;
        return $data;
    }
    
    /**
     * 获取签到列表（一个月）
     * @param type $params
     */
    public function get_signin_for_month($params)
    {
        // 获取签到配置文件
        $conf_info  = $this->signin_conf_info();
        if (!$conf_info) {
            $this->CI->output_json_return();
        }
        $begindate  =date('Ym01');// 当月第一天 20160830 00:00:00
        $enddate    = date('Ymd',strtotime("$begindate +1 month"));// 当月最后一天 20160901 00:00:00
        $days   = date('t');// 当月总天数
        $day    = date("d");// 当天是几号
        // 当前整个星期 开始--结束日期
        $mod    = $day%7;
        $_pass_date_    = $day-$mod;// 过去几周，总共天数
        if($mod) {
            $mod=$mod-1;
            $start_time  = date('Ymd',strtotime("-".$mod." day"));
            if (($day-$mod+7) > $days) {
                $end_time   = date('Ymd',strtotime("$begindate +1 month -1 day"));
                $last_week  = 1;
            } else {
                $end_time   = date('Ymd',strtotime("+".(6-$mod)." day"));
            }
        } else {
            if ($days == 28 && $day == '28') {
                $last_week  = 1;
            }
            $start_time = date('Ymd',strtotime("-6 day"));
            $end_time   = date('Ymd');
        }
        $surplus_day    = $end_time-date('Ymd');// 距离本周还有几天
        // 查询用户当月签到记录
        $pass_append_num    = 0;    // 过去补签次数
        $curr_append_num    = 0;    // 本周补签次数
        $curr_sign_num      = 0;    // 本周正常签到次数
        $pass_sign_num      = 0;    // 过去正常签到次数
        $append_num = 0;// 总补签次数
        $sign_num   = 0;//  总正常签到次数
        $sign_list  = array();
        $sign_his   = $this->signin_his_info($params['uuid'], $begindate, $enddate);
        if ($sign_his) {
            foreach ($sign_his as $k=>$v) {
                if ($v['date'] == date('Ymd', time())) {
                    $today_sign_  = 1;// 今天已签
                }
                if ($v['date'] >= $start_time && $v['date'] <= $end_time) {// 本周
                    if ($v['sign_type'] == 2) {// 补签
                        $curr_append_num ++;// 本周补签次数
                    }else{// 正常签到
                        $curr_sign_num ++;// 本周签到次数
                    }
                } else {
                    if ($v['sign_type'] == 2) {// 补签
                        $pass_append_num ++;// 过去时段补签次数
                    }else{// 正常签到
                        $pass_sign_num ++;// 过去时段签到次数
                    }
                }
                $sign_list[]            = $v['date'];
                $sign_type[$v['date']]  = $v['sign_type'];
            }
            $append_num = $curr_append_num+$pass_append_num; // 总补签次数
            $sign_num   = $curr_sign_num+$pass_sign_num;// 总正常签到次数
        }
        
        // 获取补签消耗
        $signin_status  = 2;
        $max_append = $this->CI->passport->get('max_append_signin');
        if($append_num >= $max_append) {
            $append_expend  = 0;
            $signin_status  = 3;
        } else {
            switch ($append_num) {
                case 0:
                    $append_expend  = $conf_info['feplenish_first'];
                    break;
                 case 1:
                    $append_expend  = $conf_info['replenish_second'];
                    break;
                 case 2:
                    $append_expend  = $conf_info['replenish_third'];
                    break;
                 case 3:
                    $append_expend  = $conf_info['replenish_fourth'];
                    break;
                case 4:
                    $append_expend  = $conf_info['replenish_fifth'];
                    break;
                default:
                    $append_expend  = 0;
                    break;
            }
        }
        
        for($i==0;$i<$days;$i++) {
            $signin_status  = 3;
            $d_curr   = $i?date('d',strtotime("$begindate +$i day")):date('d',  strtotime($begindate));
            if (in_array(date('Ym').$d_curr,$sign_list)) {
                $signin_status  = 1;
                if ($sign_type[date('Ym').$d_curr] == 2) {
                    $signin_status  = 4;
                }
            }else if (date('Ym').$d_curr >= $start_time && date('Ym').$d_curr < date('Ymd') && $append_num < 5){
                $signin_status  = 2;
            }
            $data['list'][] = array(
                'date'          => $i?date('m.d',strtotime("$begindate +$i day")):date('m.d',  strtotime($begindate)),
                'signin_status' => $signin_status,
                'is_weekly'     => $d_curr%7?0:1,
                'is_monthly'    => $d_curr==$days?1:0,
                'expend_point'  => $append_expend,
                'get_point'     => $conf_info['daily'],
                'sign_num'      => 5,
            );
        }
        // 签到文案提示
        $signed_info    = $this->CI->passport->get('signed_info');
        if ($last_week) {// 最后一周，月奖励文案
            if ($days == $sign_num + $append_num) {
                $info = "";
            } elseif ($_pass_date_ == ($pass_append_num+$pass_sign_num)) {
                if (!($days%7)) {// 本月只有28天
                    if ($today_sign_ && (($curr_append_num + $curr_sign_num) == 7 - $surplus_day)) {
                        $info   = str_replace('[XX]', $surplus_day, $signed_info['last_insist']);
                    } else if (!$today_sign_ && (($curr_append_num + $curr_sign_num) == 6 - $surplus_day)){
                        $info   = str_replace('[XX]', $surplus_day, $signed_info['last_insist']);
                    } else {
                        $info   = $signed_info['encourage_signin'];
                    }
                } else if (($curr_append_num + $curr_sign_num) == (($days%7) - $surplus_day-1) && !$today_sign_) {
                    if (!$today_sign_) {
                        $surplus_day +=1;
                    }
                    $info   = str_replace('[XX]', $surplus_day, $signed_info['last_insist']);
                } elseif (($curr_append_num + $curr_sign_num) >= (($days%7) - $surplus_day) && $today_sign_) {
                    $info   = str_replace('[XX]', $surplus_day, $signed_info['last_insist']);
                } elseif($max_append-$append_num  >= ($days%7) - $surplus_day-$curr_append_num-$curr_sign_num-1) {
                    $info   = $signed_info['last_append'];
                } else {
                    $info   = $signed_info['encourage_signin'];
                }
            } else {
                $info   = $signed_info['encourage_signin'];
            }
        } else {// 周奖励文案
            if ($curr_append_num+$curr_sign_num == 7) {
                $info   = "";
            }elseif ($curr_append_num+$curr_sign_num == 7-$surplus_day-1 && !$today_sign_) {// 本周是否有“需要补签”的次数
                if (!$today_sign_) {
                    $surplus_day +=1;
                }
                $info   = str_replace('[XX]', $surplus_day, $signed_info['week_signin']);// 加油哦，再坚持[XX]天就可以额外获得50积分的周奖励啦！
            }elseif ($curr_append_num+$curr_sign_num == 7-$surplus_day && $today_sign_) {// 本周是否有“需要补签”的次数
                $info   = str_replace('[XX]', $surplus_day, $signed_info['week_signin']);// 加油哦，再坚持[XX]天就可以额外获得50积分的周奖励啦！
            }else if ($max_append-$append_num  >= 7-$surplus_day-$curr_append_num-$curr_sign_num && $today_sign_) {
                $info   = $signed_info['week_append'];
            }else if ($max_append-$append_num  >= 7-$surplus_day-$curr_append_num-$curr_sign_num -1 && !$today_sign_) {
                $info   = $signed_info['week_append'];
            } else {
                $info   = "";
            }
        }
        $data['info']   = $info;
        $data['title']  = date('Y.m')."月签到";
        return $data;
    }
    
    /**
     * 获取签到配置表信息
     * @return boolean
     */
    public function signin_conf_info()
    {
        $where  = array('STATUS'=>0);
        $fields = "IDX AS id,S_DAIKY AS daily,S_WEEKLY AS weekly,S_MONTHLY AS monthly,S_REPLENISHFIRST AS feplenish_first,S_REPLENISHSECOND AS replenish_second,S_REPLENISHTHIRD AS replenish_third,S_REPLENISHFOURTH AS replenish_fourth,S_REPLENISHFIFTH AS replenish_fifth";
        $conf   = $this->CI->user_model->get_one($where,'bl_signed',$fields);
        if (!$conf) {
            $this->CI->error_->set_error(Err_Code::ERR_SIGNIN_CONF_EMPTY);
            return false;
        }
        return $conf;
    }
    
    
    /**
     * 获取用户在某时间段，签到历史记录
     * @param int $uuid
     * @param int $start_time  20160828
     * @param int $end_time    20160830
     */
    public function signin_his_info($uuid,$start_time,$end_time)
    {
        $options['fields']  = "S_USERID AS uuid,S_NAME AS name,S_SIGNTYPE AS sign_type,S_EXPENDPOINT AS expend_point,S_DATE AS date,S_DAILYPOINT AS daily_point,S_WEEKLYYPOINT AS weekly_point,S_MONTHLYPOINT AS monthly_point,ROWTIME AS sign_time";
        $options['where']   = array('S_USERID'=>$uuid,'S_DATE>='=>  $start_time,'S_DATE<'=>  $end_time,'STATUS'=>0);
        $sign_list          = $this->CI->user_model->list_data($options,'bl_signed_his');
        return $sign_list;
    }
    
    /**
     * 执行签到|补签操作
     */
    public function do_signin($params)
    {
        // 记录签到游戏最高得分
        if ($params['score']) {
            $params['score']    = (float)$params['score'];
            $this->CI->load->library('game_lib');
            $res = $this->CI->game_lib->do_upload_score($params['uuid'],$params['score']);
            if (!$res) {
                log_message('error', "do_signin:签到游戏最高得分记录失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
                $this->CI->output_json_return();
            }
        }
        
        $this->CI->user_model->start();
        // 签到操作
        $curr_date  = date('Ymd');// 当前日期
        $begindate  = date('Ym01');// 当月第一天 20160830 00:00:00
        $enddate    = date('Ymd',strtotime("$begindate +1 month"));// 当月最后一天 20160901 00:00:00
        $days       = date('t');// 当月总天数
        $d          = date('d',strtotime($curr_date));// 当日是几号   
        // 签到获得积分奖励
        $sign_conf  = $this->signin_conf_info();
        if (!$sign_conf) {
            log_message('error', "do_signin:签到配置文件获取失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->output_json_return();
        }
        
        // 获取签到历史记录
        $mod    = $d%7;
        if($mod) {
            $start_time  = date('Ymd',strtotime("-".($mod-1)." day"));
            if (($d-$mod+7) >= $days) {
                $end_time   = date('Ymd',strtotime("$begindate +1 month -1 day"));
            } else {
                $end_time   = date('Ymd',strtotime("+".(7-$mod)." day"));
            }
        } else {// 获取前7天
            $start_time = date('Ymd',strtotime("-6 day"));
            $day        = date('Ym'.$d);
            $end_time   = $curr_date;
        }
        // 校验签到日期是否是本周可签到日期
        if ($params['date']) {
            if ($params['date'] < $start_time && $params['date'] > $end_time) {
                log_message('error', "do_signin:该日期不允许签到;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
                $this->CI->error_->set_error(Err_Code::ERR_DATE_NOT_ALLOW_SIGNIN_ENOUGHT);
                $this->CI->output_json_return();
            }
        }
        
        $his_list       = $this->signin_his_info($params['uuid'],$begindate,$enddate);
        $pointsign_num  = 0;// 积分补签次数
        $append_expend  = 0;// 积分补签消耗积分数
        $daily_point    = 0;
        $week_point     = 0;
        $month_point    = 0;
        if ($his_list) {
            foreach ($his_list as $v) {
                if ($v['sign_type'] == 2) {
                    $pointsign_num  ++;
                }
                if ($v['date'] >= $start_time && $v['date'] <= $end_time) {
                    $weekly[]   = $v['date'];
                    if ($v['date'] == $params['date'] || (!$params['date'] && $curr_date == $v['date'])) {
                        log_message('error', "do_signin:今天已经签过啦;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
                        $this->CI->error_->set_error(Err_Code::ERR_DO_SIGNIN_EXISTS_FAIL);
                        $this->CI->output_json_return();
                    }
                }
            }
            if (count($his_list) == $days-1) {
                $month_point    = $sign_conf['monthly'];
            }
        }
        // 判断是补签还是当日签到
        if (!$params['date'] || $curr_date == $params['date']) {
            $params['type'] = 1;// 当日签到
            $params['date'] = $curr_date;
            $daily_point    = $sign_conf['daily'];
        } else {
            $params['type'] = 2;// 补签
            $tt             = date('Ymd',  strtotime("$curr_date +1 day"));
            if ($params['date'] >= $tt || $params['date'] < $start_time) {
                log_message('error', "do_signin:改日期不允许补签;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
                $this->CI->error_->set_error(Err_Code::ERR_NOT_ALLOW_APPEND_SIGN);
                $this->CI->output_json_return();
            }
            $max_append_num = $this->CI->passport->get('max_append_signin');
            if ($pointsign_num >= $max_append_num) {
                log_message('error', "do_signin:改月补签次数已用完,不允许补签;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
                $this->CI->error_->set_error(Err_Code::ERR_OUTNUMBER_APPEND_NUM);
                $this->CI->output_json_return();
            }
            switch ($pointsign_num) {
                case 0:
                    $append_expend  = $sign_conf['feplenish_first'];
                    break;
                 case 1:
                    $append_expend  = $sign_conf['replenish_second'];
                    break;
                 case 2:
                    $append_expend  = $sign_conf['replenish_third'];
                    break;
                 case 3:
                    $append_expend  = $sign_conf['replenish_fourth'];
                    break;
                case 4:
                    $append_expend  = $sign_conf['replenish_fifth'];
                    break;
                default:
                    $append_expend  = 0;
                    break;
            }
        }
        
        // 签到积分奖励   
        if (count($weekly) == 6) {
            $week_point    = $sign_conf['weekly'];
        }
        
        // 查询积分是否足够
        if ($append_expend) {
            $points_info    = $this->query_bl_point($params['uuid']);
            if ($points_info['points'] < $append_expend) {
                log_message('error', "do_signin:用户积分不足;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";需消耗积分数:".$append_expend.";用户积分信息：".json_encode($points_info).";执行时间：".date('Y-m-d H:i:s',time()));
                $this->CI->error_->set_error(Err_Code::ERR_BLPOINT_NOT_ENOUGHT);
                return false;
            }
        }
        
        $user_info  = $this->get_userinfo($params);
        // 签到历史记录
        $data   = array(
            'S_USERID'          => $params['uuid'],
            'S_NAME'            => $user_info['name'],
            'S_SIGNTYPE'        => $params['type'],
            'S_EXPENDPOINT'     => $append_expend,
            'S_DATE'            => $params['date'],
            'S_DAILYPOINT'      => $daily_point,
            'S_WEEKLYYPOINT'    => $week_point,
            'S_MONTHLYPOINT'    => $month_point,
            'STATUS'            => 0,
        );
        $ist_res    = $this->CI->user_model->insert_data($data,'bl_signed_his');
        if (!$ist_res) {
            log_message('error', "do_signin:签到历史记录插入失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";插入数据:".  json_encode($data).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->error_->set_error(Err_Code::ERR_SIGNIN_INSERT_HIS_FAIL);
            $this->CI->user_model->error();
            $this->CI->output_json_return();
        }
        
        // 补签消耗积分
        if ($append_expend) {
            $point_data   = array(
                'G_USERIDX'     => $params['uuid'],
                'G_NICKNAME'    => $user_info['name'],
                'G_TYPE'        => 1,// 类型0:增加1:减少
                'G_SOURCE'      => 1,// 变更来源0:充值抵扣1补签消耗2:签到获得
                'G_POINT'       => $append_expend,
                'G_INFO'        => '补签'.$params['date'].'消耗'.$params['reduce_point']."积分",
                'STATUS'        => 0,
            );
            $point_his_id   = $this->CI->user_model->insert_data($point_data,'bl_point_his'); 
            if (!$point_his_id) {
                log_message('error', "do_signin:补签消耗积分变更记录插入失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";插入数据:".  json_encode($point_data).";执行时间：".date('Y-m-d H:i:s',time()));
                $this->CI->user_model->error();
                $this->CI->error_->set_error(Err_Code::ERR_INSERT_POINT_CHANGE_HIS_FAIL);
                $this->CI->output_json_return();
            }
            $result_2       = $this->update_bl_point($params['uuid'],$point_his_id,$append_expend,"031");
            if (!$result_2) {
                log_message('error', "do_signin:补签更新积分失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";记录id:". $point_his_id.",积分值：".$append_expend.";执行时间：".date('Y-m-d H:i:s',time()));
                $this->CI->user_model->error();
                $this->CI->error_->set_error(Err_Code::ERR_APPEND_SIGNIN_REDUCE_POINT_FAIL);
                $this->CI->output_json_return();
            }
        }
        // 签到获得积分
        if ($daily_point) {
            $point_data = array(
                'G_USERIDX'     => $params['uuid'],
                'G_NICKNAME'    => $user_info['name'],
                'G_TYPE'        => 0,
                'G_SOURCE'      => 2,
                'G_POINT'       => $daily_point,
                'G_INFO'        => '签到'.$params['date'].'获得'.$daily_point.'积分',
                'STATUS'        => 0,
            );
            $ist_id = $this->CI->user_model->insert_data($point_data,'bl_point_his'); 
            if (!$ist_id) {
                log_message('error', "do_signin:签到获得积分变更记录插入失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";插入数据:".  json_encode($point_data).";执行时间：".date('Y-m-d H:i:s',time()));
                 $this->CI->user_model->error();
                 $this->CI->error_->set_error(Err_Code::ERR_INSERT_POINT_CHANGE_HIS_FAIL);
                 $this->CI->output_json_return();
            }
            $result = $this->update_bl_point($params['uuid'],$ist_id,$daily_point,"020");
            if (!$result) {
                log_message('error', "do_signin:签到更新积分失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";记录id:". $ist_id.",积分值：".$daily_point.";执行时间：".date('Y-m-d H:i:s',time()));
                $this->CI->user_model->error();
                $this->CI->error_->set_error(Err_Code::ERR_SIGNIN_GET_POINT_FAIL);
                $this->CI->output_json_return();
            }   
        }
        
        // 插入获得额外奖励积分
        if ($week_point+$month_point) {
            $point_data = array(
                'G_USERIDX'     => $params['uuid'],
                'G_NICKNAME'    => $user_info['name'],
                'G_TYPE'        => 0,
                'G_SOURCE'      => 2,
                'G_POINT'       => $week_point+$month_point,
                'G_INFO'        => '签到'.$params['date'].'额外奖励获得'.($week_point+$month_point).'积分',
                'STATUS'        => 0,
            );
            $ist_id_ = $this->CI->user_model->insert_data($point_data,'bl_point_his'); 
            if (!$ist_id_) {
                log_message('error', "do_signin:获得额外奖励积分插入失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";插入数据:".  json_encode($point_data).";执行时间：".date('Y-m-d H:i:s',time()));
                $this->CI->user_model->error();
                $this->CI->error_->set_error(Err_Code::ERR_INSERT_POINT_CHANGE_HIS_FAIL);
                $this->CI->output_json_return();
            }
        }
        $this->CI->user_model->success();
        
        // 签到获取额外奖励
        if ($week_point+$month_point) {
            // 单独发放奖励积分
            $result = $this->update_bl_point($params['uuid'],$ist_id_,($week_point+$month_point),"030");
            if (!$result) {
                log_message('error', "do_signin:额外奖励积分更新失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";记录id:". $ist_id_.",积分值：".($week_point+$month_point).";执行时间：".date('Y-m-d H:i:s',time()));
                $this->CI->error_->set_error(Err_Code::ERR_UPDATE_REWARD_BLPOINT_FAIL);
                // 记录额外奖励-发放失败[奖励类型1周奖励2月奖励3周+月]
                if ($week_point && $month_point) {
                    $fail_type  = 3;
                } else if($week_point) {
                    $fail_type  = 1;
                } else {
                    $fail_type  = 2;
                }
                 $data   = array(
                    'S_USERID'          => $params['uuid'],
                    'S_NAME'            => $user_info['name'],
                    'S_SIGNTYPE'        => $params['type'],
                    'S_EXPENDPOINT'     => $append_expend,
                    'S_DATE'            => $params['date'],
                    'S_TYPE'            => $fail_type,
                    'S_POINT'           => $week_point+$month_point,
                    'STATUS'            => 0,
                );
                $ist_res    = $this->CI->user_model->insert_data($data,'bl_reward_fail');
                if (!$ist_res) {
                    log_message("error", "do_signin:bl_reward_fail表插入失败(签到额外奖励发送失败之历史记录失败);insert_data:".json_encode($data));
                    return true;
                }
            }
        }
        return true;
    }
    
    /**
     * 获取百联账户的user_id
     */
    public function get_bluser_id($uuid)
    {
        $where          = array('U_USERIDX'=>$uuid,'status'=>0);
        $fields         = 'U_ACCOUNTID AS user_id,U_ACCOUNTNAME AS name';
        $register_info  = $this->CI->user_model->get_one($where,'bl_userlogin',$fields);
        if (!$register_info) {
            $this->CI->error_->set_error(Err_Code::ERR_GET_REGISTER_INFO_FAIL);
            return false;
        }
        return $register_info;
    }
    
    /**
     * 获取证书
     */
    public function public_key($sn,$channel,$key)
    {
        $p_key                  = $this->CI->passport->get('public_key').$key;
        $params['sn']           = $sn;
        $params['timestamp']    = time();
        $params['channelId']    = $channel;
        $params['sign']         = md5($params['sn'].$params['timestamp'].$params['channelId']);
        $params['type']         = 'forcerefresh';
        if (ENVIRONMENT != 'production') {
            $url    = $this->openapi_url_test;
        } else {
            $url    = $this->openapi_url;
        }
        $url = $url."getCertificate.htm?openapi_params=".base64_encode(http_build_query($params));
        $public_key = $this->CI->utility->post($url);
        $key_info   = json_decode($public_key,true);
        if (!$key_info['publicKey']) {
            log_message('error', 'get_public_key:'.$this->CI->input->ip_address().',获取public_key失败');
            return false;
        }
        $this->CI->cache->memcached->save($p_key,$public_key,$key_info['expiresIn']);
        return $key_info;
    }
    
    /**
     * 获取access_token
     * Token有效期为6小时，每天允许获取token数量为10次
     * 时间到期内Token信息不变，Token到期后5分钟内可以继续访问。该5分钟新老token同时有效
     * @param string $sn 设备编号
     * @param string $channel渠道id
     * @param string $key  access_token 保存的KEY
     * @return array
     */
    public function get_access_token($sn,$channel,$key = 0)
    {
        $params['grant_type']   = 'client_credentials';
        $params['appid']        = $this->CI->passport->get('bl_appid');
        $params['secret']       = $this->CI->passport->get('bl_secret');
        $params['sn']           = $sn;
        $params['timestamp']    = time();
        $params['channelId']    = $channel;
        $salt                   = $this->CI->passport->get('bl_salt');
        $params['sign']         = sha1($params['grant_type'].$params['appid'].$params['secret'].$params['timestamp'].$salt.$params['sn'].$params['channelId']);
        if (ENVIRONMENT != 'production') {
            $url    = $this->openapi_url_test;
        } else {
            $url    = $this->openapi_url;
        }
        $url .= "getToken.htm?openapi_params=".base64_encode(http_build_query($params));
        $header = array(
            "Content-type: application/json", 
        );
        $access_token   = $this->CI->utility->post($url,array(),$header);
        $token_info     = json_decode($access_token,true);
        if (!$token_info['accessToken']) {
            $this->CI->error_->set_error(Err_Code::ERR_GET_ACCESS_TOKEN_FAIL);
            log_message('error', 'get_access_token:'.$this->CI->input->ip_address().',获取access_token失败;url:'.$url.";return_data:".$access_token);
            return false;
        }
        if ($key) {
            $this->CI->cache->memcached->save($key,$access_token,$token_info['expiresIn']);
        }
        return $access_token;
    }
    

    /**
     * 通过百联账号登录
     * @param array $para 参数
     * @param int   $int  控制递归调用次数
     * @return boolean
     */
    public function do_login_by_loginid($para,$int = 0)
    {
        $access_token = $this->get_access_token($para['sn'],$para['channel']);
        $token_info    = json_decode($access_token,true);
        
        $params['access_token'] = $token_info['accessToken'];
        $params['service_name'] = 'bl.app.member.login';
        $params['timestamp']    = time();
        $params['sn']           = $para['sn'];
        $params['channelId']    = $para['channel'];
        $params['sign']         = $this->CI->utility->sha1_sign($params,$token_info['tokenKey']);
        if (ENVIRONMENT != 'production') {
            $url    = $this->openapi_url_test;
        } else {
            $url    = $this->openapi_url;
        }
        $url .= "service.htm?openapi_params=".base64_encode(http_build_query($params));
        $header = array(
            "Content-type: application/json", 
        );
        $data['channelId']      = $para['channel'];
        $data['loginId']        = $para['login_id'];
        $data['password']       = md5($para['password']);
        $content                = $this->CI->utility->post($url,json_encode($data),$header);
        $content_arr            = json_decode($content,true);
        if ($content_arr['resCode'] != '00100000') {
            if ($content_arr['errorCode'] == 'BL10012') {// ACCESS_TOKEN失效
                log_message('error', 'do_login_by_loginid_BL10012：百联三合一登录失败ACCESS_TOKEN失效'.$this->CI->input->ip_address().'递归调用次数i:'.$int.';url:'.$url.';requir_data:'.json_encode($data).';return_data:'.$content.';执行时间'.date('Y-m-d H:i:s',time()));
                if ($int < 2) {
                    $access_token   = $this->get_access_token($para['sn'],$para['channel']);
                    $data           = $this->do_login_by_loginid($para,$int+1);
                    return $data;
                }
                $this->CI->error_->set_error(Err_Code::ERR_LOGIN_BY_LOINGID_FAIL);
                 return false;
            } else {
                $this->CI->error_->set_error(Err_Code::ERR_LOGIN_BY_LOINGID_FAIL);
                log_message('error', 'do_login_by_loginid：百联三合一登录失败'.$this->CI->input->ip_address().'递归调用次数i:'.$int.';url:'.$url.';requir_data:'.json_encode($data).';return_data:'.$content.';执行时间'.date('Y-m-d H:i:s',time()));
                return false;
            }
        }
        
        // 执行游戏中心登录操作
        $user_params['channel']     = $params['channelId'];
        $user_params['user_id']     = $content_arr['obj']['member_id'];
        $user_params['passport_id'] = (string)$content_arr['obj']['passportId'];
        $user_params['sn']          = $params['sn'];
        $user_params['name']        = $content_arr['obj']['nickName']?(string)$content_arr['obj']['nickName']:(string)$content_arr['obj']['member_name'];
        $user_params['sex']         = 0;
        $user_params['mobile']      = (string)$content_arr['obj']['mobile'];
        $user_params['image']       = (string)$content_arr['obj']['mediaCephUrl'];
        $user_params['enter_type']  = 1;// 系统登入方式0APP签到登录入口1进入游戏中心2签到游戏登录（可选，默认0）
        
        $a_key  = $this->CI->passport->get('access_token').$user_params['user_id'].$user_params['channel'];
        $this->CI->cache->memcached->save($a_key,$access_token,$token_info['expiresIn']);
        
        $data   = $this->register_user($user_params);
        return $data;
    }
    
    /**
     * 隐形登录（OpenApi的access_token和会员登录信息绑定功能）
     * @param string $passport_id  passport_id
     * @param string $access_token  access_token
     * @param string $sn  设备编号
     * @param string $channel 渠道
     * @param string $key  保存public_key的KEY
     * @return array
     */
    public function login_for_passportid($passport_id,$sn,$channel,$access_key)
    {
        $a_key          = $this->CI->passport->get('access_token').$access_key;
        $access_token   = $this->CI->cache->memcached->get($a_key);
        if (!$access_token) {
            $access_token = $this->get_access_token($sn,$channel,$a_key);
        }
        $token_info             = json_decode($access_token,true);
        $params['access_token'] = $token_info['accessToken'];
        $params['service_name'] = 'bl.app.member.loginByPassport';
        $params['timestamp']    = time();
        $params['sn']           = $sn;
        $params['channelId']    = $channel;
        $params['sign']         = $this->CI->utility->sha1_sign($params,$token_info['tokenKey']);
        $params['passport_id']  = $passport_id;
        if (ENVIRONMENT != 'production') {
            $url    = $this->openapi_url_test;
        } else {
            $url    = $this->openapi_url;
        }
        $url .= "service.htm?openapi_params=".base64_encode(http_build_query($params));
        $header = array(
            "Content-type: application/json", 
        );
        $member_info    = $this->CI->utility->post($url,array(),$header);
        $member_info    = json_decode($member_info,true);
        if ($member_info['resCode'] != '00100000') {
            if ($member_info['errorCode'] == 'BL10012') {// ACCESS_TOKEN失效
                log_message('error', "login_for_passportid_BL10012:ACCESS_TOKEN失效;url:".$url.";return_data:".json_encode($member_info).";执行时间".date('Y-m-d H:i:s',time()));
                $access_token   = $this->get_access_token($sn,$channel,$a_key);
                return $data;
            } else {
                $this->CI->error_->set_error(Err_Code::ERR_TOKEN_EXPIRE);
                log_message('error', "login_for_passportid:登录失败;url:".$url.";return_data:".json_encode($member_info).";执行时间".date('Y-m-d H:i:s',time()));
                return false;
            }
        }
        log_message('info', "login_for_passportid:登录成功;url:".$url.";return_data:".json_encode($member_info).";执行时间".date('Y-m-d H:i:s',time()));
        return $member_info['obj'];
    }
    

    /**
     * 查看百联账户积分
     * @param int $uuid 用户id
     * @param int $int  用于控制递归调用次数
     * @return boolean
     */
    public function query_bl_point($uuid,$int = 0)
    {
        $u_info         = $this->get_register_info($uuid);
        $a_key          = $this->CI->passport->get('access_token').$u_info['user_id'].$u_info['channel'];;
        $access_token   = $this->CI->cache->memcached->get($a_key);
        if (!$access_token) {
            $access_token = $this->get_access_token($u_info['sn'],$u_info['channel'],$a_key);
        }
        $token_info             = json_decode($access_token,true);
        $para['access_token']   = $token_info['accessToken'];
        $para['service_name']   = 'bl.member.core.querymemberpoint';
        $para['timestamp']      = time();
        $para['sn']             = $u_info['sn'];
        $para['channelId']      = $u_info['channel'];
        $para['passport_id']    = $u_info['passport_id'];
        $para['sign']           = $this->CI->utility->sha1_sign($para,$token_info['tokenKey']);
        $data['sysid']          = '2103';
        $data['passport_id']    = $u_info['passport_id'];
        if (ENVIRONMENT != 'production') {
            $url    = $this->openapi_url_test;
        } else {
            $url    = $this->openapi_url;
        }
        $url    = $url."service.htm?openapi_params=".base64_encode(http_build_query($para));
        $header = array(
            "Content-type: application/json", 
        );
        $content                = $this->CI->utility->post($url,json_encode($data),$header);
        $content_arr            = json_decode($content,true);
        if ($content_arr['resCode'] != '00100000') {
            if ($content_arr['errorCode'] == 'BL10012') {// ACCESS_TOKEN失效
                log_message('error', 'query_bl_point_BL10012：积分查询失败ACCESS_TOKEN失效;'.$this->CI->input->ip_address().',递归执行次数i:'.$int.';用户信息：'.json_encode($u_info).';URL:'.$url.";return_data:".$content);
                if ($int < 2) {
                    $access_token = $this->get_access_token($u_info['sn'],$u_info['channel'],$a_key);
                    $data         = $this->query_bl_point($uuid,$int+1);
                    return $data;
                }
                $this->CI->error_->set_error(Err_Code::ERR_GET_BLPOINT_FAIL);
                return false;
            } else {
                $this->CI->error_->set_error(Err_Code::ERR_GET_BLPOINT_FAIL);
                log_message('error', 'query_bl_point:积分查询失败'.$this->CI->input->ip_address().';uuid:'.$uuid.';URL:'.$url.";return_data:".$content);
                return false;
            }
        }
        log_message('info', 'query_bl_point：积分查询成功;'.$this->CI->input->ip_address().',递归执行次数i:'.$int.';用户信息：'.json_encode($u_info).';URL:'.$url.";return_data:".$content);
        return $content_arr['obj'];
    }
    
    
    /**
     * 修改积分接口（百联补签送积分）
     * @param type $uuid
     * @param type $change_id 变更记录ID
     * @param type $change_value  变更积分值
     * @param type $type = 020（签到积分）030 （签到奖励积分）031（补签消耗积分）
     * @param int  $int 用户控制递归调用次数
     * @return boolean
     */
    public function update_bl_point($uuid,$change_id,$change_value,$type = '020',$int = 0)
    {
        $u_info         = $this->get_register_info($uuid);
        $a_key          = $this->CI->passport->get('access_token').$u_info['user_id'].$u_info['channel'];
        $access_token   = $this->CI->cache->memcached->get($a_key);
        if (!$access_token) {
            $access_token = $this->get_access_token($u_info['sn'],$u_info['channel'],$a_key);
        }
        $token_info    = json_decode($access_token,true);
        $para['access_token']   = $token_info['accessToken'];
        if ($type == '020') {
            $data['pointTime']      = time();
            $para['service_name']   = 'bl.member.core.addMemberPoints';
        } else if($type == '030'){
            $data['pointTime']      = time();
            $para['service_name']   = 'bl.member.core.addMemberPoints';
        } else {
            $para['service_name']   = 'bl.member.core.gift';
            $data['occurTime']      = date('YmdHis',time());
        }
        $para['timestamp']      = time();
        $para['sn']             = $u_info['sn'];
        $para['channelId']      = $u_info['channel'];
        $para['passport_id']    = $u_info['passport_id'];
        $para['sign']           = $this->CI->utility->sha1_sign($para,$token_info['tokenKey']);
        if (ENVIRONMENT != 'production') {
            $url    = $this->openapi_url_test;
        } else {
            $url    = $this->openapi_url;
        }
        $url                    = $url."service.htm?openapi_params=".base64_encode(http_build_query($para));
        $data['billId']         = $change_id;// 对账单号
        $data['pointType']      = $type;
        $data['points']         = $change_value;
        $data['sysid']          = '2104';
        $data['passport_id']    = $u_info['passport_id'];
        $data['channelId']      = 1;
        $data['buId']           = '3000';
        $header = array(
            "Content-type: application/json", 
        );
        $content                = $this->CI->utility->post($url,json_encode($data),$header);
        $content_arr            = json_decode($content,true);
        if ($content_arr['resCode'] != '00100000') {
            if ($content_arr['errorCode'] == 'BL10012') {// ACCESS_TOKEN失效
                log_message('error', 'update_bl_point_fail_for_token_expire:'.$this->CI->input->ip_address().';递归调用次数i:'.$int.';URL:'.$url.";return_data:".$content);
                if ($int < 2) {
                    $access_token   = $this->get_access_token($u_info['sn'],$u_info['channel'],$a_key);
                    $data           = $this->update_bl_point($uuid,$change_id,$change_value,$type,$int+1);
                    return $data;
                }
                $this->CI->error_->set_error(Err_Code::ERR_UPDATE_BLPOINT_FAIL);
                return false;
            } else {
                $this->CI->error_->set_error(Err_Code::ERR_UPDATE_BLPOINT_FAIL);
                log_message('error', 'update_bl_point:update_blpoint_fail'.$this->CI->input->ip_address().',百联积分更新失败;uuid:'.$uuid.';URL:'.$url.";return_data:".$content);
                return false;
            }
        }
        return $content_arr;
    }
    
    /**
     * 更新用户信息
     */
    public function update_user_info($uuid,$fields)
    {
        $table  = "bl_user";
        $where  = array('IDX'=>$uuid,'STATUS'=>0);
        $res    = $this->CI->user_model->update_data($fields,$where,$table);
        if (!$res) {
            $this->CI->error_->set_error(Err_Code::ERR_INSERT_BEST_SCORE_FAIL);
            return false;
        }
        return true;
    }
    
    /**
     * 测试接口（插入消息）
     */
    public function add_messge()
    {
        $fields = array(
            'M_NAME'    => 'test_'.time(),
            'M_ICON'    => '1.png',
            'M_INFO'    => 'infoinfoinfo',
            'M_SENDER'  => 'me',
            'STATUS'    => 0,
        );
        $res    = $this->CI->user_model->insert_data($fields,'bl_mailbox');
        if (!$res) {
            $this->CI->error_->set_error(Err_Code::ERR_DB);
            $this->CI->output_json_return();
        }
        $this->CI->output_json_return();
    }
    
    /**
     * 查看百联账户积分
     */
    public function q_points($uuid)
    {
        $u_info         = $this->get_register_info($uuid);
        $a_key          = $this->CI->passport->get('access_token').$u_info['user_id'].$u_info['channel'];;
        // $access_token   = $this->CI->cache->memcached->get($a_key);
        if (!$access_token) {
            $access_token = $this->get_access_token($u_info['sn'],$u_info['channel'],$a_key);
        }
        $token_info    = json_decode($access_token,true);

        $para['access_token']   = $token_info['accessToken'];
        $para['service_name']   = 'bl.member.core.querymemberpoint';
        $para['timestamp']      = time();
        $para['sn']             = $u_info['sn'];
        $para['channelId']      = $u_info['channel'];
        $para['passport_id']    = '1_c4d42d211072444c802808b86c279436_3106';
        $para['sign']           = $this->CI->utility->sha1_sign($para,$token_info['tokenKey']);
        $data['sysid']          = '2103';
        $data['passport_id']    = '1_c4d42d211072444c802808b86c279436_3106';
        if (ENVIRONMENT != 'production') {
            $url    = $this->openapi_url_test;
        } else {
            $url    = $this->openapi_url;
        }
        $url    = $url."service.htm?openapi_params=".base64_encode(http_build_query($para));
        $header = array(
            "Content-type: application/json",
        );
        $content                = $this->CI->utility->post($url,json_encode($data),$header);
        $content_arr            = json_decode($content,true);
        if ($content_arr['errorCode'] == '05111040') {
            $this->CI->error_->set_error(Err_Code::ERR_TOKEN_EXPIRE);
            log_message('error', 'query_bl_point:passport_id expired'.$this->CI->input->ip_address().',PASSPORT_ID失效');
            return false;
        }
        if ($content_arr['resCode'] != '00100000') {
            $this->CI->error_->set_error(Err_Code::ERR_GET_BLPOINT_FAIL);
            log_message('error', 'query_bl_point:get_blpoint_fail'.$this->CI->input->ip_address().',百联积分查询失败');
            return false;
        }
        return $content_arr['obj'];
    }
    
    
}

