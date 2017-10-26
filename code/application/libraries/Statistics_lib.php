<?php
/**
 * 活动/专题操作
 * @author	huhong
 * @date	2016-08-24 16:14
 */
class Statistics_lib extends Base_lib {
    // public $time   = 1483210800;
    public $time;
    public function __construct() {
        parent::__construct();
        $this->load_model('active_model');
        $this->load_model('statistics_model');
        $this->time = time();
    }
    
    /**
     * 统计每日新增用户
     */
    public function user_everyday_add()
    {
        $table      = "bl_userlogin";
        $start_time = strtotime(date('Ymd',time()-86400));
        $end_time   = strtotime(date('Ymd',time()));
        $where      = array('unix_timestamp(ROWTIME)>='=>$start_time,"unix_timestamp(ROWTIME)<"=>$end_time);
        if ($date) {// TODO
            
        }
        $total_count    = $this->CI->active_model->total_count($where, $table);
        return $total_count;
    }
    
    /**
     * 统计用户总数
     */
    public function user_total()
    {
        $table      = "bl_userlogin";
        $end_time   = strtotime(date('Ymd',time()));
        $where      = array("unix_timestamp(ROWTIME)<"=>$end_time,'STATUS'=>0);
        if ($date) {// TODO
            
        }
        $total_count    = $this->CI->active_model->total_count($where, $table);
        return $total_count;
    }
    
    /**
     * 统计付费用户总数（包含积分、人民币）
     * 查询百联币充值订单用户
     */
    public function payfee_user_total($date = false)
    {
        $table      = "bl_recharge_his";
        $end_time   = strtotime(date('Ymd',time()));
        $where      = " WHERE R_ORDERSTATUS = 1 AND unix_timestamp(ROWTIME) <".$end_time." GROUP BY R_USERID";
        if ($date) {
            $where      = " WHERE R_ORDERSTATUS = 1 AND unix_timestamp(ROWTIME) <".$date['end_time']." GROUP BY R_USERID";
        }
        $sql    = "SELECT IDX FROM ".$table.$where;
        $res    = $this->CI->active_model->exec_by_sql($sql,true);
        return count($res);
    }
    
    /**
     * 统计每日付费用户
     */
    public function payfee_user_day()
    {
        $table      = "bl_recharge_his";
        $start_time = strtotime(date('Ymd',time()-86400));
        $end_time   = strtotime(date('Ymd',time()));
        $where      = " WHERE R_ORDERSTATUS = 1 AND UNIX_TIMESTAMP(ROWTIME)>=".$start_time." AND UNIX_TIMESTAMP(ROWTIME)<".$end_time."  GROUP BY R_USERID";
        if ($date) {// TODO
            $where = "WHERE R_ORDERSTATUS = 1 AND UNIX_TIMESTAMP(ROWTIME) >=".$time." GROUP BY R_USERID";
        }
        $sql    = "SELECT IDX FROM ".$table.$where;
        $res    = $this->CI->active_model->exec_by_sql($sql,true);
        return count($res);
    }
    
    /**
     * 每日新增充值用户数
     */
    public function payfee_user_newadd()
    {
        $date['end_time']   = strtotime(date('Ymd',time()-86400));;
        $total_1    = $this->payfee_user_total();// 当前付费用户总数
        $total_2    = $this->payfee_user_total($date);// 当天之前一天 付费用户总数
        return $total_1 - $total_2;
    }
    
    /**
     * 人民币付费 总用户
     */
    public function payrmb_user_total()
    {
        $table      = "bl_recharge_his";
        $end_time   = strtotime(date('Ymd',time()));
        $where      = " WHERE R_ORDERSTATUS = 1 AND R_EXPENDRMB !=0 AND UNIX_TIMESTAMP(ROWTIME)<".$end_time." GROUP BY R_USERID";
        if ($date) {// TODO
            $where = "WHERE R_ORDERSTATUS = 1 AND R_EXPENDRMB !=0 AND UNIX_TIMESTAMP(ROWTIME) >=".$time." GROUP BY R_USERID";
        }
        $sql    = "SELECT IDX FROM ".$table.$where;
        $res    = $this->CI->active_model->exec_by_sql($sql,true);
        return count($res);
    }
    
    /**
     * 人民币付费 -日用户
     */
    public function payrmb_user_day()
    {
        $table      = "bl_recharge_his";
        $start_time = strtotime(date('Ymd',time()-86400));
        $end_time   = strtotime(date('Ymd',time()));
        $where      = " WHERE R_ORDERSTATUS = 1 AND R_EXPENDRMB !=0 AND UNIX_TIMESTAMP(ROWTIME)>=".$start_time." AND UNIX_TIMESTAMP(ROWTIME)<".$end_time." GROUP BY R_USERID";
        if ($date) {// TODO
            $where = "WHERE R_ORDERSTATUS = 1  AND R_EXPENDRMB !=0  AND UNIX_TIMESTAMP(ROWTIME) >=".$time." GROUP BY R_USERID";
        }
        $sql    = "SELECT IDX FROM ".$table.$where;
        $res    = $this->CI->active_model->exec_by_sql($sql,true);
        return count($res);
    }
    
    /**
     * 百联币收入 -总额
     */
    public function blcoin_income_total()
    {
        $table      = "bl_recharge_his";
        $end_time   = strtotime(date('Ymd',time()));
        $where      = " WHERE R_ORDERSTATUS = 1 AND R_TYPE = 1 AND UNIX_TIMESTAMP(ROWTIME)<".$end_time." AND STATUS = 0";
        if ($date) {
            $where = "";
        }
        $sql    = "SELECT SUM(R_GETBLCOIN) count FROM ".$table.$where;
        $count  = $this->CI->active_model->exec_by_sql($sql);
        return (int)$count['count'];
    }
    
    /**
     * 百联币收入 -日收入
     */
    public function blcoin_income_day()
    {
        $table      = "bl_recharge_his";
        $start_time = strtotime(date('Ymd',time()-86400));
        $end_time   = strtotime(date('Ymd',time()));
        $where      = " WHERE R_ORDERSTATUS = 1 AND R_TYPE = 1 AND UNIX_TIMESTAMP(ROWTIME)>= ".$start_time." AND UNIX_TIMESTAMP(ROWTIME)<".$end_time." AND STATUS = 0";
        if ($date) {
            $where = "";
        }
        $sql    = "SELECT SUM(R_GETBLCOIN) count FROM ".$table.$where;
        $count  = $this->CI->active_model->exec_by_sql($sql);
        return (int)$count['count'];
    }
    
    /**
     * 人民币充值金额  -总额
     */
    public function rmb_pay_total()
    {
        $table  = "bl_recharge_his";
        $end_time   = strtotime(date('Ymd',time()));
        $where  = " WHERE R_ORDERSTATUS = 1 AND R_TYPE = 1 AND UNIX_TIMESTAMP(ROWTIME) <".$end_time;
        if ($date) {
            $where = "";
        }
        $sql    = "SELECT SUM(R_EXPENDRMB) rmb FROM ".$table.$where;
        $count  = $this->CI->active_model->exec_by_sql($sql);
        return $count['rmb'];
    }
    
    /**
     * 人民币充值金额 -日充值
     */
    public function rmb_pay_day()
    {
        $table      = "bl_recharge_his";
        $start_time = strtotime(date('Ymd',time()-86400));
        $end_time   = strtotime(date('Ymd',time()));
        $where      = " WHERE R_ORDERSTATUS = 1 AND R_TYPE = 1 AND UNIX_TIMESTAMP(ROWTIME)>= ".$start_time." AND UNIX_TIMESTAMP(ROWTIME) <".$end_time;
        if ($date) {
            $where = "";
        }
        $sql    = "SELECT SUM(R_EXPENDRMB) rmb FROM ".$table.$where;
        $count  = $this->CI->active_model->exec_by_sql($sql);
        return (int)$count['rmb'];
    }
    
    /**
     * 百联积分充值金额  -总额
     */
    public function point_pay_total()
    {
        $table      = "bl_recharge_his";
        $end_time   = strtotime(date('Ymd',time()));
        $where      = " WHERE R_ORDERSTATUS = 1 AND R_TYPE = 1 AND UNIX_TIMESTAMP(ROWTIME)<".$end_time;
        if ($date) {
            $where = "";
        }
        $sql    = "SELECT SUM(R_EXOENDPOINT) points FROM ".$table.$where;
        $count  = $this->CI->active_model->exec_by_sql($sql);
        return $count['points'];
    }
    
    /**
     * 百联积分充值金额  -日充值
     */
    public function point_pay_day()
    {
        $table      = "bl_recharge_his";
        $start_time = strtotime(date('Ymd',time()-86400));
        $end_time   = strtotime(date('Ymd',time()));
        $where      = " WHERE R_ORDERSTATUS = 1 AND R_TYPE = 1 AND UNIX_TIMESTAMP(ROWTIME)>= ".$start_time." AND UNIX_TIMESTAMP(ROWTIME)<".$end_time;
        if ($date) {
            $where = "";
        }
        $sql    = "SELECT SUM(R_EXOENDPOINT) points FROM ".$table.$where;
        $count  = $this->CI->active_model->exec_by_sql($sql);
        return (int)$count['points'];
    }
    
    /**
     * 日活跃用户，过去一天登录过游戏的用户
     */
    public function DAU_day()
    {
        $table      = "bl_loginlog";
        $start_time = strtotime(date('Ymd',time()-86400));
        $end_time   = strtotime(date('Ymd',time()));
        $where      = " WHERE unix_timestamp(ROWTIME)>=".$start_time." AND unix_timestamp(ROWTIME)<".$end_time." GROUP BY L_USERIDX ";
        if ($date) {// TODO
            $where      = " WHERE unix_timestamp(ROWTIME)>=".$start_time." AND unix_timestamp(ROWTIME)<".$end_time." GROUP BY L_USERIDX ";
        }
        $sql    = "SELECT IDX FROM ".$table.$where;
        $res    = $this->CI->active_model->exec_by_sql($sql,true);
        return count($res);
    }
    
    /**
     * 每日签到人数
     */
    public function sign_user_day_num($date = false)
    {
        $table      = "bl_signed_his";
        $start_time = strtotime(date('Ymd',time()-86400));
        $end_time   = strtotime(date('Ymd',time()));
        $where      = " WHERE S_SIGNTYPE = 1 AND unix_timestamp(ROWTIME)>=".$start_time." AND unix_timestamp(ROWTIME)<".$end_time;
        if ($date) {
            $where  = " WHERE unix_timestamp(S_DATE)>=".$date['start_time']." AND unix_timestamp(S_DATE)<".$date['end_time']." GROUP BY S_USERID";
        }
        $sql    = "SELECT COUNT(IDX) AS num FROM ".$table.$where;
        if ($date) {
            $count  = $this->CI->active_model->exec_by_sql($sql,true);
            return count($count);
        }
        $count  = $this->CI->active_model->exec_by_sql($sql);
        return $count['num'];
    }
    
    /**
     * 每日补签人数
     */
    public function appendsign_user_day_num()
    {
        $table      = "bl_signed_his";
        $start_time = strtotime(date('Ymd',time()-86400));
        $end_time   = strtotime(date('Ymd',time()));
        $where      = " WHERE S_SIGNTYPE = 2 AND unix_timestamp(ROWTIME)>=".$start_time." AND unix_timestamp(ROWTIME)<".$end_time." GROUP BY S_USERID";
        if ($date) {// TODO
            $where  = " WHERE S_SIGNTYPE = 2 AND unix_timestamp(ROWTIME)>=".$start_time." AND unix_timestamp(ROWTIME)<".$end_time." GROUP BY S_USERID";
        }
        $sql    = "SELECT IDX FROM ".$table.$where;
        $count  = $this->CI->active_model->exec_by_sql($sql,true);
        return count($count);
    }
    
    /**
     * 每日签到发放积分数（单位：元）|用户签到获得积分由百联发放
     */
    public function sign_get_points_day($date = false)
    {
        $time       = $this->time;
        $table      = "bl_signed_his";
        $start_time = strtotime(date('Ymd',$time-86400));
        $end_time   = strtotime(date('Ymd',$time));
        $where      = " WHERE unix_timestamp(ROWTIME)>=".$start_time." AND unix_timestamp(ROWTIME)<".$end_time;
        if ($date) {
            $where  = " WHERE unix_timestamp(ROWTIME)>=".$date['start_time']." AND unix_timestamp(ROWTIME)<".$date['end_time'];
        }
        $sql    = "SELECT SUM(S_DAILYPOINT+S_WEEKLYYPOINT+S_MONTHLYPOINT) AS sum FROM ".$table.$where;
        $count  = $this->CI->active_model->exec_by_sql($sql);
        if (!$count['sum']) {
            return 0;
        }
        $rmb    = $count['sum']/100;
        return $rmb;
    }
    
    /**
     * 每日补签消耗积分（用户补签消耗积分，百联账户回收积分）
     */
    public function appendsign_reduce_points_day($date = false)
    {
        $time       = $this->time;
        $table      = "bl_signed_his";
        $start_time = strtotime(date('Ymd',$time-86400));
        $end_time   = strtotime(date('Ymd',$time));
        $where      = " WHERE S_SIGNTYPE = 2 AND unix_timestamp(ROWTIME)>=".$start_time." AND unix_timestamp(ROWTIME)<".$end_time;
        if ($date) {
            $where  = " WHERE S_SIGNTYPE = 2 AND unix_timestamp(ROWTIME)>=".$date['start_time']." AND unix_timestamp(ROWTIME)<".$date['end_time'];
        }
        $sql    = "SELECT SUM(S_EXPENDPOINT) AS sum FROM ".$table.$where;
        $count  = $this->CI->active_model->exec_by_sql($sql);
        if (!$count['sum']) {
            return 0;
        }
        $rmb    = $count['sum']/100;
        return $rmb;
    }
    
    /**
     * 一周连续签到--签到人数
     */
    public function weekly_continue_sign()
    {
        $time       = $this->time;
        $table      = "bl_signed_his";
        $days       = date('t', $time-86400);
        $end_time   = date('Ymd',$time-10800);// 减去3小时  20160101 00:00:00
        $start_time = date("Ymd",strtotime("-".$days." day",  strtotime($end_time)));
        $where      = " WHERE S_WEEKLYYPOINT != 0 AND S_DATE>=".$start_time." AND S_DATE<".$end_time." GROUP BY S_USERID";
        $sql        = "SELECT COUNT(IDX) num FROM ".$table.$where;
        $result = $this->CI->active_model->exec_by_sql($sql,true);
        return count($result);
    }
    
    /**
     * 连续二周连续签到--签到人数（已弃用）
     */
    public function two_weekly_continue_sign()
    {
        $weekly_his    = $this->weekly_reward_his();
        if (!$weekly_his) {
            return 0;
        }
        $num    = 0;
        foreach ($weekly_his as $k=>$v) {
            $arr = explode(",", $v['date']);
            if (count($arr) < 2) {
                continue;
            }
            if (count($arr) == 2) {
                if ($arr[1] - $arr[0] <= 13) {
                    $num ++;
                }
            } elseif(count($arr) == 3) {
                if ($arr[1] - $arr[0] <= 13) {
                    $num ++;
                } elseif($arr[2] - $arr[1] <= 13) {
                    $num ++;
                }
            } elseif(count($arr) == 4) {
                if ($arr[1] - $arr[0] <= 13) {
                    $num ++;
                } elseif($arr[2] - $arr[1] <= 13) {
                    $num ++;
                }elseif($arr[3] - $arr[2] <= 13) {
                    $num ++;
                }
            }
        }
        return $num;
    }
    
    /**
     * 连续三周连续签到--签到人数(已弃用)
     */
    public function three_weekly_continue_sign()
    {
        $weekly_his    = $this->weekly_reward_his();
        if (!$weekly_his) {
            return 0;
        }
        $num    = 0;
        foreach ($weekly_his as $k=>$v) {
            $arr = explode(",", $v['date']);
            if (count($arr) < 3) {
                continue;
            }
            if (count($arr) === 3) {
                if ($arr[1] - $arr[0] <= 13 && $arr[2] - $arr[1] <=13) {
                    $num ++;
                }
            } elseif(count($arr) == 4) {
                if ($arr[1] - $arr[0] <= 13 && $arr[2] - $arr[1] <=13) {
                    $num ++;
                } elseif($arr[3] - $arr[2] <= 13 && $arr['2'] - $arr[1] <=13) {
                    $num ++;
                }
            }
        }
        return $num;
    }
    
    public function weeks_continue_sign()
    {
        $data['two_weekly_continue_sign']   = 0;
        $data['three_weekly_continue_sign'] = 0;
        $weekly_his    = $this->weekly_reward_his();
        if (!$weekly_his) {
            return $data;
        }
        // 获取连续2周的
        $num    = 0;
        foreach ($weekly_his as $k=>$v) {
            if (count($v) == 2) {
                if ($v[1] - $v[0] <= 13) {
                    $num ++;
                }
            } elseif(count($v) == 3) {
                if ($v[1] - $v[0] <= 13) {
                    $num ++;
                } elseif($v[2] - $v[1] <= 13) {
                    $num ++;
                }
            } elseif(count($v) == 4) {
                if ($v[1] - $v[0] <= 13) {
                    $num ++;
                } elseif($v[2] - $v[1] <= 13) {
                    $num ++;
                }elseif($v[3] - $v[2] <= 13) {
                    $num ++;
                }
            }
        }
        $data['two_weekly_continue_sign']   = $num;
        
        
        // 获取3周的
        $num    = 0;
        foreach ($weekly_his as $k=>$v) {
            if (count($v) === 3) {
                if ($v[1] - $v[0] <= 13 && $v[2] - $v[1] <=13) {
                    $num ++;
                }
            } elseif(count($v) == 4) {
                if ($v[1] - $v[0] <= 13 && $v[2] - $v[1] <=13) {
                    $num ++;
                } elseif($v[3] - $v[2] <= 13 && $v['2'] - $v[1] <=13) {
                    $num ++;
                }
            }
        }
        $data['three_weekly_continue_sign']   = $num;
        return $data;
    }
    
    /**
     * 统计周奖励记录
     */
    public function weekly_reward_his()
    {
        $time       = $this->time;
        $table      = "bl_signed_his";
        $days       = date('t', $time-86400);
        $end_time   = date('Ymd',$time-10800);// 减去3小时  20160101 00:00:00
        $start_time = date("Ymd",strtotime("-".$days." day",  strtotime($end_time)));
        // $where      = " WHERE S_WEEKLYYPOINT != 0 AND S_DATE>=".$start_time." AND S_DATE<".$end_time." ORDER BY S_DATE ASC";
        // $sql        = "SELECT IDX,S_USERID,S_DATE FROM ".$table.$where;
        $where      = " WHERE S_WEEKLYYPOINT != 0 AND S_DATE>=".$start_time." AND S_DATE<".$end_time." GROUP BY S_USERID ORDER BY S_DATE ASC";
        $sql        = "SELECT S_USERID,GROUP_CONCAT(S_DATE) date FROM ".$table.$where;
        $list       = $this->CI->active_model->exec_by_sql($sql,true);
        if (!$list) {
            return 0;
        } 
        return $list;
    }
    
    
    /**
     * 连续一个月签到--签到人数（获UUID）
     * @param type $type = 1 签到人数 2获取签到的uuid
     * @return type
     */
    public function month_continue_sign($type = 1)
    {
        $time       = $this->time;
        $table      = "bl_signed_his";
        $days       = date('t', $time-86400);
        $end_time   = date('Ymd',$time-10800);// 减去3小时  20160101 00:00:00
        $start_time = date("Ymd",strtotime("-".$days." day",  strtotime($end_time)));
        $where      = " WHERE S_DATE>=".$start_time." AND S_DATE<".$end_time." GROUP BY S_USERID";
        if ($date) {
            $where  = " WHERE S_DATE>=".$start_time." AND S_DATE<".$end_time." GROUP BY S_USERID";
        }
        $sql    = "SELECT COUNT(IDX) num,S_USERID uuid FROM ".$table.$where." HAVING num>=".$days;
        $count  = $this->CI->active_model->exec_by_sql($sql,true);
        if ($type == 1) {
            return count($count);
        }
        return $count;
    }
    
    
    /**
     * 获取补签人数 --- 1,2,3,4,5次  0:表示获取总补签人数
     * @param type $num
     * @return int
     */
    public function appendsign_num($num = 0)
    {
        $time       = $this->time;
        $table      = "bl_signed_his";
        $end_time   = date('Ymd',$time-10800);// 减去3小时  20160101 00:00:00
        $days       = date('t', $time-86400);
        $start_time = date("Ymd",strtotime("-".$days." day",  strtotime($end_time)));
        $where      = " WHERE S_SIGNTYPE = 2 AND S_DATE>=".$start_time." AND S_DATE<".$end_time." GROUP BY S_USERID";
        if ($date) {// TODO
            $where  = " WHERE S_SIGNTYPE = 2 AND S_DATE>=".$start_time." AND S_DATE<".$end_time." GROUP BY S_USERID";
        }
        $sql    = "SELECT COUNT(IDX) num FROM ".$table.$where;
        $list   = $this->CI->active_model->exec_by_sql($sql,true);
        
        $data['one_appendsign_num']     = 0;
        $data['two_appendsign_num']     = 0;
        $data['three_appendsign_num']   = 0;
        $data['four_appendsign_num']    = 0;
        $data['five_appendsign_num']    = 0;
        $data['total_appendsign_num']   = 0;
        
        if(!$list) {
            return $data;
        }
        
        $data['total_appendsign_num'] = count($list);
        foreach ($list as $k=>$v) {
            if ($v['num'] == 1) {
                $data['one_appendsign_num']++;
            } elseif($v['num'] == 2) {
                $data['two_appendsign_num']++;
            }  elseif($v['num'] == 3) {
                $data['three_appendsign_num']++;
            } elseif($v['num'] == 4) {
                $data['four_appendsign_num']++;
            }elseif($v['num'] == 5) {
                $data['five_appendsign_num']++;
            }
        }
        return $data;
    }
    
    /**
     * 获取总签到会员---月
     */
    public function total_sign_num()
    {
        $time               = $this->time;
        $date['end_time']   = strtotime(date('Ymd',$time));// 减去3小时  20160101 00:00:00
        $days               = date('t', $time-86400);
        $date['start_time'] = strtotime("-".$days." day",  $date['end_time']);
        $month_rmb          = $this->sign_user_day_num($date);
        return $month_rmb;
    }
    
    /**
     * 获取总补签人数
     */
    public function total_appendsign_num()
    {
        $num        = 0;
        $total_num  = $this->appendsign_num($num);
        return $total_num;
    }
    
    /**
     * 补签一次---总人数
     */
    public function one_appendsign_num()
    {
        $num        = 1;
        $total_num  = $this->appendsign_num($num);
        return $total_num;
    }
    
    /**
     * 补签二次---总人数
     */
    public function two_appendsign_num()
    {
        $num        = 2;
        $total_num  = $this->appendsign_num($num);
        return $total_num;
    }
    
    /**
     * 补签三次---总人数
     */
    public function three_appendsign_num()
    {
        $num        = 3;
        $total_num  = $this->appendsign_num($num);
        return $total_num;
    }
    
    /**
     * 补签四次---总次数
     */
    public function four_appendsign_num()
    {
        $num        = 4;
        $total_num  = $this->appendsign_num($num);
        return $total_num;
    }
    
    /**
     * 补签五次---总次数
     */
    public function five_appendsign_num()
    {
        $num        = 5;
        $total_num  = $this->appendsign_num($num);
        return $total_num;
    }
    
    /**
     * 每月签到发放积分数（单位：元）|用户签到获得积分 由百联发放
     */
    public function sign_get_points_month()
    {
        $time               = $this->time;
        $date['end_time']   = strtotime(date('Ymd',$time-10800));// 减去3小时  20160101 00:00:00
        $days               = date('t', $time-86400);
        $date['start_time'] = strtotime("-".$days." day",  $date['end_time']);
        $month_rmb          = $this->sign_get_points_day($date);
        return $month_rmb;
    }
    
    /**
     * 每月补签消耗积分（用户补签消耗积分，百联账户回收积分）
     * @return type
     */
    public function appendsign_reduce_points_month()
    {
        $time               = $this->time;
        $date['end_time']   = strtotime(date('Ymd',$time-10800));// 减去3小时  20160101 00:00:00
        $days               = date('t', $time-86400);
        $date['start_time'] = strtotime("-".$days." day",  $date['end_time']);
        $month_rmb          = $this->appendsign_reduce_points_day($date);
        return $month_rmb;
    }
    
    
    /**
     * ERP统计--百联币统计
     * 每日凌晨9点统计一次(统计昨天数据)
     */
    public function erp_statist_of_blcoin($date = array())
    {
        // 统计数据
        if (empty($date)) {
            $time               = time();
            $statist_date       = date('Ymd',$time - 86400);
            $date['start_time'] = strtotime($statist_date);// 开始时间 20161214 00:00:00 （统计14号数据）
            $date['end_time']   = strtotime(date('Y-m-d',$time));// 开始时间 20161215  00:00:00 
        } else {
            $statist_date       = date('Ymd',$date['end_time'] - 86400);
        }
        
        // 游戏币充值
        $blcoin     = $this->recharge_blcoin($date);
        // 兑换消耗百联币
        $exchange   = $this->exchange_statist_of_blcoin($date);
        // 购买单机收费游戏消耗百联币
        $gamebuy   = $this->gamebuy_statist_of_blcoin($date);
        // 网游充值-购买道具消耗百联币
        $propbuy   = $this->propbuy_statist_of_blcoin($date);
        // 获取当前需要同步的数据（同步前一天数据）
        $table  = "bl_blcoin_erp";
        $data   = array(array('E_STATISTNO'=> $this->getMillisecond().mt_rand(0, 9)."1",'E_BLCOIN'=>$blcoin,'E_GAMEBUY'=> $gamebuy,'E_PROPBUY'=> $propbuy,'E_EXCHANGE'=> $exchange,'E_DATE'=> $statist_date,'E_SYNCTYPE' => 1, 'E_SYNCSTATUS'  => 1,'STATUS'=>0),
                        array('E_STATISTNO'=> $this->getMillisecond().mt_rand(0, 9)."2",'E_BLCOIN'=>$blcoin,'E_GAMEBUY'=> $gamebuy,'E_PROPBUY'=> $propbuy,'E_EXCHANGE'=> $exchange,'E_DATE'=> $statist_date,'E_SYNCTYPE' => 2, 'E_SYNCSTATUS'  => 1,'STATUS'=>0),
                        array('E_STATISTNO'=> $this->getMillisecond().mt_rand(0, 9)."3",'E_BLCOIN'=>$blcoin,'E_GAMEBUY'=> $gamebuy,'E_PROPBUY'=> $propbuy,'E_EXCHANGE'=> $exchange,'E_DATE'=> $statist_date,'E_SYNCTYPE' => 3, 'E_SYNCSTATUS'  => 1,'STATUS'=>0),
                        array('E_STATISTNO'=> $this->getMillisecond().mt_rand(0, 9)."4",'E_BLCOIN'=>$blcoin,'E_GAMEBUY'=> $gamebuy,'E_PROPBUY'=> $propbuy,'E_EXCHANGE'=> $exchange,'E_DATE'=> $statist_date,'E_SYNCTYPE' => 4, 'E_SYNCSTATUS'  => 1,'STATUS'=>0),
                        array('E_STATISTNO'=> $this->getMillisecond().mt_rand(0, 9)."5",'E_BLCOIN'=>$blcoin,'E_GAMEBUY'=> $gamebuy,'E_PROPBUY'=> $propbuy,'E_EXCHANGE'=> $exchange,'E_DATE'=> $statist_date,'E_SYNCTYPE' => 5, 'E_SYNCSTATUS'  => 1,'STATUS'=>0),
                        array('E_STATISTNO'=> $this->getMillisecond().mt_rand(0, 9)."6",'E_BLCOIN'=>$blcoin,'E_GAMEBUY'=> $gamebuy,'E_PROPBUY'=> $propbuy,'E_EXCHANGE'=> $exchange,'E_DATE'=> $statist_date,'E_SYNCTYPE' => 6, 'E_SYNCSTATUS'  => 1,'STATUS'=>0)
                );
        
        // 插入数据同步表
        $res    = $this->CI->active_model->insert_batch($data,$table);
        if (!$res) {
            log_message('error', 'erp_statist_of_blcoin:当日'.$statist_date.'统计数据插入失败'.";插入数据:".  json_encode($data).";执行时间：".date('Y-m-d H:i:s',time()));
            return false;
        }
        
        // 获取以往同步失败的data
        $options['where']   = array('E_SYNCSTATUS !='=>2,'STATUS'=>0);
        $options['fields']  = "IDX,E_STATISTNO,E_BLCOIN,E_GAMEBUY,E_EXCHANGE,E_PROPBUY,E_DATE,E_SYNCTYPE,E_SYNCSTATUS";
        $list               = $this->CI->active_model->list_data($options,$table);
        
        // 执行数据同步操作
        foreach ($list as $k=>$v) {
            $type       = $v['E_SYNCTYPE'];
            $sync_res   = $this->erp_sync_send($v,$type);
            if (!$sync_res) {
                // 记录同步失败的idx
                $upt_data[]  = array(
                    'IDX'           => $v['IDX'],
                    'E_SYNCSTATUS'  => 3,
                );
                // 记录历史记录
                $data_his[]   = array(
                    'E_ERPID'       => $v['IDX'],
                    'E_STATISTNO'   => $v['E_STATISTNO'],
                    'E_DATE'        => $v['E_DATE'],
                    'E_SYNCSTATUS'  => 3,
                    'STATUS'        => 0,
                );
                log_message('error', 'erp_statist_of_blcoin:ERP同步失败;'.$statist_date.";同步数据:".  json_encode($v).";执行时间：".date('Y-m-d H:i:s',time()));
            } else {
                // 记录成功的idx
                $upt_data[]  = array(
                    'IDX'           => $v['IDX'],
                    'E_SYNCSTATUS'  => 2,
                );
                // 记录历史记录
                $data_his[]   = array(
                    'E_ERPID'       => $v['IDX'],
                    'E_STATISTNO'   => $v['E_STATISTNO'],
                    'E_DATE'        => $v['E_DATE'],
                    'E_SYNCSTATUS'  => 2,
                    'STATUS'        => 0,
                );
            }
        }
        
        $this->CI->active_model->start();
        // 修改同步数据
        $upt_res    = $this->CI->active_model->update_batch($upt_data,'IDX',$table);
        if (!$upt_res) {
            // 统计数据同步状态-数据更新失败
            $this->CI->active_model->error();
            log_message('error', 'erp_statist_of_blcoin:统计数据同步状态-数据更新失败;'.$statist_date.";更新数据:".  json_encode($upt_data).";执行时间：".date('Y-m-d H:i:s',time()));
            return false;
        }
        
        // 添加同步历史记录表
        $table_2    = "bl_erp_his";
        $ist_res    = $this->CI->active_model->insert_batch($data_his,$table_2);
        if (!$ist_res) {
            $this->CI->active_model->error();
            log_message('error', 'erp_statist_of_blcoin:数据统计同步数据-历史记录插入失败;'.$statist_date.";插入数据:".  json_encode($data_his).";执行时间：".date('Y-m-d H:i:s',time()));
            return false;
        }
        $this->CI->active_model->success();
        return 'SUCCESS';
    }
    
    /**
     * 获取毫秒时间戳
     * @return type
     */
    public function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
    
    
    /**
     * 游戏中心百联币向ERP同步
     * @param array $params 同步数据
     * @param int $type     同步类型[1单机收入2网游收入3网游代付白鹭4兑换20%议价5兑换代付6游戏币充值]
     * @return boolean
     */
    public function erp_sync_send($params,$type)
    {
        $tax_code   = $this->CI->passport->get('tax_code');
        $rate       = $this->CI->passport->get('blcoin_rate')*100;
        $propbuy    = $params['E_PROPBUY']/$rate;
        
        if ($type == 1) {// 1单机收入
            $total_rmb  = ($params['E_GAMEBUY'])/$rate;
            $data['HEADER']['SETT_CONTENT']     = 'S064';// 结算项  单机游戏币收入
            $data['HEADER']['SOURCE_CODE']      = 'A012';// 账务类型 收入结算
            $data['HEADER']['VALUE1']           = 'BQQD0';// 结算对象  
            $data['HEADER']['SETT_CODE']        = 'SP002';// 结算类型
        } elseif($type == 2) {// 2网游收入
            $total_rmb  =  ($propbuy - ($propbuy-$propbuy*0.05)*0.6);
            $data['HEADER']['SETT_CONTENT']     = 'S062';// 结算项   游戏币收入
            $data['HEADER']['SOURCE_CODE']      = 'A012';// 账务类型 收入结算
            $data['HEADER']['VALUE1']           = 'BQQD0';// 结算对象  
            $data['HEADER']['SETT_CODE']        = 'SP002';// 结算类型
        } elseif($type == 3) {// 3网游代付白鹭--支付给白鹭
            $total_rmb  = ($propbuy-$propbuy*0.05)*0.6;
            $data['HEADER']['SETT_CONTENT']     = 'S063';// 结算项  游戏币代付
            $data['HEADER']['SOURCE_CODE']      = 'A007';// 账务类型 代付结算
            $data['HEADER']['VALUE1']           = 'S:YXBL';// 结算对象  
            $data['HEADER']['SETT_CODE']        = 'SP002';// 结算类型
        } elseif($type == 4) {// 4兑换20%议价
            $total_rmb  = ($params['E_EXCHANGE']-($params['E_EXCHANGE']/1.2))/$rate;
            $data['HEADER']['SETT_CONTENT']     = 'S065';// 结算项    游戏币转移
            $data['HEADER']['SOURCE_CODE']      = 'A012';// 账务类型  收入结算
            $data['HEADER']['VALUE1']           = 'BQQD0';// 结算对象 
            $data['HEADER']['SETT_CODE']        = 'SP002';// 结算类型
        } elseif($type == 5) {// 5兑换代付
            $total_rmb  = ($params['E_EXCHANGE']/1.2)/$rate;
            $data['HEADER']['SETT_CONTENT']     = 'S065';// 结算项   游戏币转移
            $data['HEADER']['SOURCE_CODE']      = 'A007';// 账务类型 代付结算
            $data['HEADER']['VALUE1']           = 'BQQD0';// 结算对象  
            $data['HEADER']['SETT_CODE']        = 'SP002';// 结算类型
        } else {// 6游戏币充值
            $total_rmb  = ($params['E_BLCOIN'])/$rate;
            $data['HEADER']['SETT_CONTENT']     = 'S060';// 结算项    游戏币充值
            $data['HEADER']['SOURCE_CODE']      = 'A005';// 账务类型  代收结算
            $data['HEADER']['VALUE1']           = 'BQQD0';// 结算对象  
            $data['HEADER']['SETT_CODE']        = 'SP021';// 结算类型
        }
	if (!$total_rmb) {// 账目为0，不同步到ERP
            return true;
        }
        
        // 头数据
        $data['HEADER']['BATCH_NO']         = $params['E_STATISTNO'];// 批次ID
        $data['HEADER']['SOURCE_HEADER_ID'] = $params['E_STATISTNO'];// 头信息唯一标识ID
        $data['HEADER']['SYS_SOURCES']      = 'YXZX';// 来源系统
        $data['HEADER']['OU_CODE']          = 'BQQD0';// 务实体代码
        $data['HEADER']['AMOUNT']           = round($total_rmb,2);// 结算总额
        $data['HEADER']['VALUE10']          = date('Y-m-d',strtotime($params['E_DATE']));// 入账日期
        $data['HEADER']['TRANS_DATE']       = $this->zeit;// 传入日期
        $data['HEADER']['VALUE2']           = '';// 第三方客商编码
        $data['HEADER']['VALUE3']           = '';// 发票日期
        $data['HEADER']['VALUE4']           = '';// 付款条件
        $data['HEADER']['VALUE9']           = '';// 条件日期
        $data['HEADER']['VALUE5']           = '';// 付款方法
        $data['HEADER']['VALUE11']          = 'CNY';// 币种 默认：CNY
        $data['HEADER']['VALUE6']           = date('Y-m-d',strtotime($params['E_DATE']));// 汇率日期
        $data['HEADER']['VALUE7']           = 1;// 汇率
        $data['HEADER']['VALUE8']           = '';// 订单号
        
        // 行数据
        $data['HEADER']['LINE']['SYS_SOURCES']        = 'YXZX';// 来源系统
        $data['HEADER']['LINE']['SOURCE_HEADER_ID']   = $params['E_STATISTNO'];// 头信息唯一标识ID
        $data['HEADER']['LINE']['SOURCE_LINE_ID']     = $params['E_STATISTNO'];// 行信息唯一标识ID
        $data['HEADER']['LINE']['LINE_NUM']           = $params['IDX'];// 行号
        $data['HEADER']['LINE']['ITEM_CODE']          = '';// SKU编码
        if ($params['type'] == 1) {
            $data['HEADER']['LINE']['VALUE6']             = "VAT_".$tax_code;// 税码
            $data['HEADER']['LINE']['AMOUNT']             = round($total_rmb/(1+$tax_code/100),2);// 未含税金额（总额/1.06） TODO
            $data['HEADER']['LINE']['TAX_AMOUNT']         = round($total_rmb - $data['HEADER']['LINE']['AMOUNT'],2);// 税额  总额-未含税金额
        } else {
            $data['HEADER']['LINE']['AMOUNT']             = round($total_rmb,2);// 未含税金额（总额/1） TODO
            $data['HEADER']['LINE']['TAX_AMOUNT']         = '';// 税额  总额-未含税金额
            $data['HEADER']['LINE']['VALUE6']             = "";// 税码
        }
        
        $data['HEADER']['LINE']['TRANS_DATE']         = $this->zeit;// 传入日期
        $data['HEADER']['LINE']['VALUE2']             = '';// 商品大类
        $data['HEADER']['LINE']['VALUE3']             = '';// 产品线
        $data['HEADER']['LINE']['VALUE4']             = '100098';// 成本中心
        $data['HEADER']['LINE']['VALUE7']             = '';// 行类型
        $data['HEADER']['LINE']['VALUE8']             = '';// 付款银行账户
        $header['msg_type']                                 = 'B05FND0001';
        $header['branch_id']                                = 0;
        $header['operation_type']                           = 1;
        $new_data['Service']['Service_Header']              = $header;
        $new_data['Service']['Service_Body']['DATAINFO']    = $data;
        // 数组转成XML
        $xml    = '<?xml version="1.0" encoding="UTF-8"?>'; 
        $xml    .= $this->array_to_xml($new_data);
        if (ENVIRONMENT != 'production') {
            $url    = $this->erp_url_test;
        } else {
            $url    = $this->erp_url;
        }
        
        // 封装XML
        $message_type   = "ERPB05FND001";
        $xml_new        = $this->soapXml($message_type,$xml);
        // $a =file_put_contents('2_h.txt', $ret);
        // file_put_contents('e:\1_h.xml', $xml_new);
        
        // 执行soap
        try{
            $client = new  SoapClient($url."?wsdl",array('trace' => true,'exceptions'=>true,'cache_wsdl'=>WSDL_CACHE_NONE));
            $tuData = $client->__doRequest($xml_new,$url,'INVOKEFMSWS',1,0);
            $ret    = $this->xmlToInfo($tuData);
            if ($ret['x_return_code'] == 'S') {
                return true;
            }
        } catch (Exception $ex) {
            log_message('error', 'erp_statist_of_blcoin:连接webservice处理失败'.$ex->getMessage().";执行时间：".date('Y-m-d H:i:s',time()));
            return false;
        }
	return false;
    }
    
    private function xmlToInfo($base64_decode){
            $x_return_code = 'X_RETURN_CODE';
            $arr['x_return_code'] = $this->xmlsubstr($base64_decode,$x_return_code);
            $x_return_mesg = 'X_RETURN_MESG';
            $arr['x_return_mesg'] = $this->xmlsubstr($base64_decode,$x_return_mesg);
            $x_response_data = 'X_RESPONSE_DATA';
            $arr['x_response_data'] = $this->xmlsubstr($base64_decode,$x_response_data);
            if(!empty($arr['x_response_data'])){
                    $arr['x_response_data'] = base64_decode($arr['x_response_data']);
            }
            return $arr;
    }
    
    private function xmlsubstr($base64_decode,$string){
            $str = '';
            $findLen = strlen($string);
            $tmp = strpos($base64_decode, $string);
            $i = strrpos($base64_decode, $string,0);
            if($tmp != $i){
                    $str = substr($base64_decode,$tmp+$findLen+1,$i-$tmp-$findLen-3);
            }

            return $str;
    }
    
    private function soapXml($message_type,$request_data){
            $request_data = base64_encode($request_data);
            $soap = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cux="http://xmlns.oracle.com/apps/fnd/soaprovider/plsql/cux_3_ws_server_prg/" xmlns:inv="http://xmlns.oracle.com/apps/fnd/soaprovider/plsql/cux_3_ws_server_prg/invokefmsws/">
               <soapenv:Header>
                      <cux:SOAHeader>
                             <!--Optional:-->
                             <cux:Responsibility>INVENTORY</cux:Responsibility>
                             <!--Optional:-->
                             <cux:RespApplication>INV</cux:RespApplication>
                             <!--Optional:-->
                             <cux:SecurityGroup>STANDARD</cux:SecurityGroup>
                             <!--Optional:-->
                             <cux:NLSLanguage>SIMPLIFIED CHINESE</cux:NLSLanguage>
                             <!--Optional:-->
                             <cux:Org_Id></cux:Org_Id>
                      </cux:SOAHeader>
                      <wsse:Security env:mustUnderstand="1"
                            xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
                            xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                            <wsse:UsernameToken>
                              <wsse:Username>B05_WS_OIS</wsse:Username>
                              <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">abc123</wsse:Password>
                            </wsse:UsernameToken>
                      </wsse:Security>
               </soapenv:Header>
               <soapenv:Body>
                      <inv:InputParameters>
                             <!--Optional:-->
                             <inv:P_IFACE_CODE>'.$message_type.'</inv:P_IFACE_CODE>
                             <!--Optional:-->
                             <inv:P_BATCH_NUMBER>1</inv:P_BATCH_NUMBER>
                             <!--Optional:-->
                             <inv:P_REQUEST_DATA>'.$request_data.'</inv:P_REQUEST_DATA>
                      </inv:InputParameters>
               </soapenv:Body>
            </soapenv:Envelope>';
            $dom = new DomDocument('1.0', 'UTF-8');
            $dom->loadXML($soap);
            $xml = $dom->saveXML();
            if (ob_get_length()){
                    ob_clean();
            }
            return $xml;
    }
    
    /**
     * 将数组转为XML格式
     */
    function array_to_xml($arr){ 
        foreach ($arr as $key=>$val){ 
            if(is_array($val)){ 
            $xml.="<".$key.">".$this->array_to_xml($val)."</".$key.">"; 
            }else{ 
            $xml.="<".$key.">".$val."</".$key.">"; 
            } 
        } 
        return $xml; 
    }
    
    
    /**
     * 兑换商品（话费流量）---消耗百联币
     * @param type $date
     * @return type
     */
    public function exchange_statist_of_blcoin($date)
    {
        $table  = "bl_exchange_his";
        $where  = array('UNIX_TIMESTAMP(ROWTIME)>='=>$date['start_time'],'UNIX_TIMESTAMP(ROWTIME)<'=>$date['end_time']);
        $fields = "sum(E_EXPENDBLCION) sum";
        $count  = $this->CI->active_model->get_one($where,$table,$fields);
        return (int)$count['sum'];
    }
    
    /**
     * 购买单机收费游戏--消耗百联币
     * @param type $date
     * @return type
     */
    public function gamebuy_statist_of_blcoin($date)
    {
        $table  = "bl_gamebuy";
        $where  = array('UNIX_TIMESTAMP(ROWTIME)>='=>$date['start_time'],'UNIX_TIMESTAMP(ROWTIME)<'=>$date['end_time']);
        $fields = "sum(B_BLCOINCURRENT) sum";
        $count  = $this->CI->active_model->get_one($where,$table,$fields);
        return (int)$count['sum'];
    }
    
    /**
     * 购买网游道具|网游充值---消耗百联币
     * @param type $date
     * @return type
     */
    public function propbuy_statist_of_blcoin($date)
    {
        $table  = "bl_propbuy";
        $where  = array('P_BUYSTATUS'=>0,'UNIX_TIMESTAMP(ROWTIME)>='=>$date['start_time'],'UNIX_TIMESTAMP(ROWTIME)<'=>$date['end_time']);
        $fields = "sum(P_TOTALBLCOIN) sum";
        $count  = $this->CI->active_model->get_one($where,$table,$fields);
        return (int)$count['sum'];
    }
    
    /**
     * 百联币充值---统计用户充值获得百联币总数
     * @param type $date
     * @return type
     */
    public function recharge_blcoin($date)
    {
        $table  = "bl_recharge_his";
        $where  = array('R_TYPE'=>1,'UNIX_TIMESTAMP(ROWTIME)>='=>$date['start_time'],'UNIX_TIMESTAMP(ROWTIME)<'=>$date['end_time']);
        $fields = "sum(R_GETBLCOIN) sum";
        $count  = $this->CI->active_model->get_one($where,$table,$fields);
        return (int)$count['sum'];
    }
    
    
    /**************************************非实时数据统计 START (每日凌晨 2:20 更新)*********************************/
    /**
     * 统计 日活跃、次日留存、3日留存、7日留存
     */
    public function do_backdata_statist()
    {
        $table              = "bl_statisc";
        $time               = time() - 24*3600;
        $data['DATE']       = date('Ymd',$time);
        $data['DAU_DAY']    = $this->DAU_day();
        $data['RESIDUAL_1'] = $this->user_residual_1_();
        $data['RESIDUAL_3'] = $this->user_residual_3_();
        $data['RESIDUAL_7'] = $this->user_residual_7_();
        $data['STATUS']     = 0;
        // 查询该天数据是否存在
        $where  = array('DATE'=>$data['DATE'],'STATUS'=>0);
        $fields = "IDX";
        $info   = $this->CI->active_model->get_one($where,$table,$fields);
        if ($info) {
            $where  = array('IDX'=>$info['IDX']);
            $res    = $this->CI->active_model->update_data($data, $where, $table);
        } else {
            $res    = $this->CI->active_model->insert_data($data, $table);
        }
        if (!$res) {
            return 'FAIL';
        }
        return 'SUCCESS';
    }
    
    
    /**
     * 日活跃用户---统计前一天的总日活跃用户
     */
    public function DAU_day_($time = '')
    {
        if ($time == '') {
            $time   = time();
        }
        $params['start_time']   = strtotime(date('Ymd',$time-24*3600));// 2017-02-26 00:00:00
        $params['end_time']     = strtotime(date('Ymd',$time));// 2017-02-27 00:00:00
        $table  = "bl_loginlog";
        $sql    = "SELECT count(DISTINCT L_USERIDX) num FROM ".$table." WHERE UNIX_TIMESTAMP(ROWTIME) >= '".$params['start_time']."' AND UNIX_TIMESTAMP(ROWTIME) < ".$params['end_time']." AND `STATUS` = 0";
        $info   = $this->CI->statistics_model->fetch($sql , 'row');
        $num    = (int)$info['num'];
        return $num;
    }
    
    /**
     * 次日留存
     * 例如当天是27号：则27号的数据为0 26号为0 25号留存为：统计25号注册、26登陆的留存
     */
    public function user_residual_1_($time = '')
    {
        // 1.获取25号注册的用户
        if ($time == '') {
            $time   = time();
        }
        $date['start_time'] = strtotime(date('Ymd',$time-2*24*3600));// 2017-02-25 00:00:00
        $date['end_time']   = strtotime(date('Ymd',$time-24*3600));// 2017-02-26 00:00:00
        $table              = "bl_userlogin";
        $sql                = "SELECT DISTINCT(U_USERIDX) uuid FROM ".$table." WHERE UNIX_TIMESTAMP(ROWTIME) >= ".$date['start_time']." AND UNIX_TIMESTAMP(ROWTIME) <".$date['end_time'];
        $register           = $this->CI->statistics_model->fetch($sql , 'result');
        if (!$register) {
            return 0;
        }
        // 获取26号登陆的用户
        $date2['start_time']    = strtotime(date('Ymd',$time-24*3600));// 2017-02-26 00:00:00
        $date2['end_time']      = strtotime(date('Ymd',$time));// 2017-02-27 00:00:00
        $table2                 = "bl_loginlog";
        $sql                    = "SELECT DISTINCT(L_USERIDX) uuid  FROM ".$table2." WHERE L_ENTERTYPE = 1 AND UNIX_TIMESTAMP(ROWTIME) >= '".$date2['start_time']."' AND UNIX_TIMESTAMP(ROWTIME) < '".$date2['end_time']."' AND `STATUS` = 0";
        $login                  = $this->CI->statistics_model->fetch($sql , 'result');
        if (!$login) {
            return 0;
        }
        // 取数组交集，获取留存数
        $info_1 = array_column($register, 'uuid');
        $info_2 = array_column($login, 'uuid');
        $arr    = array_intersect($info_1, $info_2);
        return (int)count($arr);
    }
    
    /**
     * 三日留存
     * 例如当天是27号：则27号的数据为0 26号为0 25号为0 24号为0 23号留存为：统计23号注册，24 25 26 三天登陆的留存
     */
    public function user_residual_3_($time = '')
    {
        // 1.获取23号注册的用户
        if ($time == '') {
            $time   = time();
        }
        $date['start_time'] = strtotime(date('Ymd',$time-4*24*3600));// 2017-02-23 00:00:00
        $date['end_time']   = strtotime(date('Ymd',$time-3*24*3600));// 2017-02-24 00:00:00
        $table              = "bl_userlogin";
        $sql                = "SELECT DISTINCT(U_USERIDX) uuid FROM ".$table." WHERE UNIX_TIMESTAMP(ROWTIME) >= ".$date['start_time']." AND UNIX_TIMESTAMP(ROWTIME) <".$date['end_time'];
        $register           = $this->CI->statistics_model->fetch($sql , 'result');
        if (!$register) {
            return 0;
        }
        // 获取24 25 26 三号登陆的用户
        $date2['start_time']    = strtotime(date('Ymd',$time-3*24*3600));// 2017-02-24 00:00:00
        $date2['end_time']      = strtotime(date('Ymd',$time));// 2017-02-27 00:00:00
        $table2                 = "bl_loginlog";
        $sql                    = "SELECT DISTINCT(L_USERIDX) uuid  FROM ".$table2." WHERE L_ENTERTYPE = 1 AND UNIX_TIMESTAMP(ROWTIME) >= '".$date2['start_time']."' AND UNIX_TIMESTAMP(ROWTIME) < '".$date2['end_time']."' AND `STATUS` = 0";
        $login                  = $this->CI->statistics_model->fetch($sql , 'result');
        if (!$login) {
            return 0;
        }
        // 取数组交集，获取留存数
        $info_1 = array_column($register, 'uuid');
        $info_2 = array_column($login, 'uuid');
        $arr    = array_intersect($info_1, $info_2);
        return (int)count($arr);
    }
    
    /**
     * 七日留存
     */
    public function user_residual_7_($time = '')
    {
        // 1.获取19号注册的用户
        if ($time == '') {
            $time   = time();
        }
        $date['start_time'] = strtotime(date('Ymd',$time-8*24*3600));// 2017-02-19 00:00:00
        $date['end_time']   = strtotime(date('Ymd',$time-7*24*3600));// 2017-02-20 00:00:00
        $table              = "bl_userlogin";
        $sql                = "SELECT DISTINCT(U_USERIDX) uuid FROM ".$table." WHERE UNIX_TIMESTAMP(ROWTIME) >= ".$date['start_time']." AND UNIX_TIMESTAMP(ROWTIME) <".$date['end_time'];
        $register           = $this->CI->statistics_model->fetch($sql , 'result');
        if (!$register) {
            return 0;
        }
        // 获取20号->26 7天登陆的用户
        $date2['start_time']    = strtotime(date('Ymd',$time-7*24*3600));// 2017-02-20 00:00:00
        $date2['end_time']      = strtotime(date('Ymd',$time));// 2017-02-27 00:00:00
        $table2                 = "bl_loginlog";
        $sql                    = "SELECT DISTINCT(L_USERIDX) uuid  FROM ".$table2." WHERE L_ENTERTYPE = 1 AND UNIX_TIMESTAMP(ROWTIME) >= '".$date2['start_time']."' AND UNIX_TIMESTAMP(ROWTIME) < '".$date2['end_time']."' AND `STATUS` = 0";
        $login                  = $this->CI->statistics_model->fetch($sql , 'result');
        if (!$login) {
            return 0;
        }
        // 取数组交集，获取留存数
        $info_1 = array_column($register, 'uuid');
        $info_2 = array_column($login, 'uuid');
        $arr    = array_intersect($info_1, $info_2);
        return (int)count($arr);
    }
    
    /**************************************非实时数据统计 END (每日凌晨 2:20 更新)*********************************/
    
    /**
     * 每日签到数据统计(插入数据库)
     */
    public function insert_sign_data_day($params)
    {
        $table  = "bl_statisc_sign_day";
        $data   = array(
            'DATE'          => date('Ymd',strtotime("-1 day")),
            'SIGN_NUM'      => $params['sign_user_day'],
            'APPEDN_NUM'    => $params['appendsign_user_day'],
            'SIGN_POINTS'   => $params['sign_get_points_day'],
            'APPEND_POINTS' => $params['appendsign_reduce_points_day'],
            'POINTS'        => $params['sign_pool'],
            'STATUS'        => 0,
        );
        $res    = $this->CI->statistics_model->insert_data($data,$table);
        return $res;
    }
    
    /**
     * 每月签到数据统计(插入数据库)
     */
    public function insert_sign_data_month($params)
    {
        $table  = "bl_statisc_sign_month";
        $data   = array(
            'DATE'              => date('Ym',strtotime("-1 day")),
            'APPEND_FIRST'      => $params['one_appendsign'],
            'APPEND_SECOND'     => $params['two_appendsign'],
            'APPEND_THIRD'      => $params['three_appendsign'],
            'APPEND_FOURTH'     => $params['four_appendsign'],
            'APPEND_FIFTH'      => $params['five_appendsign'],
            'APPEND_POINTS'     => $params['appendsign_reduce'],
            'SIGN_POINTS'       => $params['sign_get'],
            'POINTS'            => $params['sign_pool'],
            'SIGN_ONEWEEK'      => $params['weekly_continue'],
            'SIGN_TWOWEEKS'     => $params['two_weekly_continue'],
            'SIGN_THREEWEEKS'   => $params['three_weekly_continue'],
            'SIGN_ONEMONTH'     => $params['month_continue_sign'],
            'APPEND_NUM'        => $params['total_appendsign'],
            'SIGN_TOTAL'        => $params['total_sign'],
            'STATUS'            => 0,
        );
        $res    = $this->CI->statistics_model->insert_data($data,$table);
        return $res;
    }
    
}

