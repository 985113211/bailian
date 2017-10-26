<?php
/**
 * 一元夺宝操作
 * @author	huhong
 * @date	2016-12-16 15:10
 */
class Duobao_lib extends Base_lib {
    public function __construct() {
        parent::__construct();
        $this->load_model('duobao_model');
    }
    
    /**
     * 获取夺宝商品列表
     */
    public function get_goods_list($params)
    {
        if ($params['type'] === 1) {
            $orderby    = "B.G_BOUGHTNUM DESC";
        } elseif($params['type'] === 2) {
            $orderby    = "B.ROWTIMEUPDATE DESC";
        } elseif($params['type'] === 3) {
            $orderby    = "A.G_BOUGHTNUM*100/(A.G_BLCOIN/A.G_SINGLEBLCOIN) DESC";
        }
        // 获取列表数据
        $select     = "A.IDX id,A.G_DATENO date_no,A.G_GOODSNO goods_no,B.G_NAME name,B.G_ICON icon,B.G_TYPE type";
        $sql        = "SELECT ".$select." FROM bl_dbgoods A JOIN bl_dbgoods_conf B ON A.G_GOODSIDX = B.IDX AND G_BUYSTATUS = 1 AND A.STATUS = 0 AND B.STATUS = 0 ORDER BY ".$orderby;
        $g_list = $this->CI->duobao_model->exec_by_sql($sql,true);
        if (!$g_list) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        // 过滤查询条件
        if ($params['cate_type']) {
            foreach ($g_list as $k=>$v) {
                $type_arr   = explode(",", trim($v['type'],','));
                if (in_array($params['cate_type'], $type_arr)) {
                    $new_list[] = $v;
                }
            }
        } else {
            $new_list   = $g_list;
        }
        if (!$new_list) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $data['list']       = array_slice($new_list,$params['offset'],$params['pagesize']);
        $data['pagecount']  = ceil(count($new_list)/$params['pagesize']);
        return $data;
    }
    
    /**
     * 获取夺宝商品详细信息
     * @param array $params
     * @return array
     */
    public function get_goods_info($params)
    {
        $select         = "A.IDX id,A.G_DATENO date_no,A.G_GOODSNO goods_no,A.G_BOUGHTNUM num,B.G_LIMITBUY allow_num,B.G_NAME name,B.G_IMGS imgs,G_DETAIL detail";
        $condition      = "A.IDX = ".$params['id']." AND A.STATUS =0";
        $join_condition = "A.G_GOODSIDX = B.IDX AND B.STATUS = 0";
        $tb_a           = "bl_dbgoods A";
        $tb_b           = "bl_dbgoods_conf B";
        $g_info = $this->CI->duobao_model->left_join($condition, $join_condition,$select,$tb_a,$tb_b);
        if (!$g_info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $g_info['imgs'] = explode(";", $g_info['imgs']);
        // 获取用户参与夺宝信息
        $user_dbnum = $this->user_dbnum($params['uuid'],$g_info['id']);
        if ($user_dbnum) {
            $g_info['info'] = '您参加本期幸运夺宝'.$user_dbnum.'人次，祝好运';
        } else {
            $g_info['info'] = '您还没有参加本期幸运夺宝哦';
        }
        return $g_info;
    }
    
    /**
     * 查看当前用户可允许购买人次数上限
     * @param type $params
     */
    public function get_allow_dbnum($params)
    {
        
        $select = "A.IDX id,A.G_DATENO date_no,A.G_BLCOIN blcoin,A.G_SINGLEBLCOIN single_blcoin,A.G_BOUGHTNUM num,B.G_LIMITBUY limitbuy_num";
        $sql    = "SELECT ".$select." FROM bl_dbgoods A,bl_dbgoods_conf B WHERE A.IDX=".$params['id']." AND A.STATUS = 0 AND A.G_GOODSIDX = B.IDX AND B.STATUS = 0";
        $g_info = $this->CI->duobao_model->exec_by_sql($sql);
        if (!$g_info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        // 获取用户购买情况
        $user_dbnum = $this->user_dbnum($params['uuid'],$params['id']);
        $a_num      = $g_info['limitbuy_num'] - $user_dbnum;
        $b_num      = ($g_info['blcoin']/$g_info['single_blcoin']-$g_info['num']);
        $data   = array(
            'id'        => $g_info['id'],
            'date_no'   => $g_info['date_no'],
            'allow_num' => $a_num>$b_num?$b_num:$a_num,
            'num'       => $user_dbnum,
        );
        return $data;
    }

    /**
     * 获取用户参与夺宝次数
     * @param int $uuid  用户UUID
     * @param int $goods_id  夺宝商品表IDX
     */
    public function user_dbnum($uuid,$goods_id)
    {
        $sql    = "SELECT SUM(O_BUYNUM) FROM bl_dborder WHERE G_USERIDX = ".$uuid." AND O_GOODSIDX = ".$goods_id;
        return (int)$this->CI->duobao_model->exec_by_sql($sql);
    }
    
    /**
     * 执行夺宝下单操作
     * @param type $params
     */
    public function do_take_order($params)
    {
        // 判断该商品是否允许下单、是否允许下注数
        $select         = "A.G_DATENO date_no,A.G_GOODSIDX goods_id,A.G_GOODSNO goods_no,A.G_BLCOIN blcoin,A.G_SINGLEBLCOIN single_blcoin,A.G_BOUGHTNUM bought_num,A.G_BUYSTATUS buy_status,A.G_GSTATUS g_status,B.G_LIMITBUY limitbuy_num";
        $condition      = "A.IDX = ".$params['id']." AND A.STATUS =0";
        $join_condition = "A.G_GOODSIDX = B.IDX AND B.STATUS = 0";
        $tb_a           = "bl_dbgoods A";
        $tb_b           = "bl_dbgoods_conf B";
        $g_info = $this->CI->duobao_model->left_join($condition, $join_condition,$select,$tb_a,$tb_b);
        if (!$g_info) {
            $this->CI->error_->set_error(Err_Code::ERR_NOT_EXISTS_DBGOODS_FAIL);
            return false;
        }
        if ($g_info['g_status'] != 1) { // 投注中
            $this->CI->error_->set_error(Err_Code::ERR_NOT_ALLOW_TAKE_DUOBAO_FAIL);
            return false;
        }
        $allowbuy_num   = ($g_info['blcoin']/$g_info['single_blcoin'])  - $g_info['bought_num'];
        if ($params['num'] > $g_info['limitbuy_num'] || $params['num'] > $allowbuy_num) {
            $this->CI->error_->set_error(Err_Code::ERR_OVER_DUOBAO_ALLOW_BUY_NUM);
            return false;
        }
        // 判断用户百联币是否足够
        $expend_blcoin  = $g_info['single_blcoin']*$params['num'];
        $u_info         = $this->CI->utility->get_user_info($params['uuid']);
        if ($u_info['blcoin'] < $expend_blcoin) {
            $this->CI->error_->set_error(Err_Code::ERR_BLCOIN_NOT_ENOUGHT_FAIL);
            return false;
        }
        // 随机生成 “中奖夺宝号”
        $dbno_arr   = $this->allo_dbno(array('date_no'=>$g_info['date_no'],'num'=>$params['num']));
        if (!$dbno_arr) {
            return false;
        }
        $dbno_info  = json_encode($dbno_arr);
        $this->CI->duobao_model->start();
        // 扣除用户百联币
        $table  = "bl_user";
        $fields = array("U_BLCOIN"=>$u_info['blcoin'] - $expend_blcoin);
        $where  = array('IDX'=>$params['uuid'],'STATUS'=>0);
        $upt_u  = $this->CI->duobao_model->update_data($fields,$where,$table);
        if (!$upt_u) {
            $this->CI->duobao_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_UPDATE_USERINFO_FAIL);
            return false;
        }
        
        // 插入夺宝订单表
        $dborder_no = $this->CI->utility->get_uniqid('DB');
        $table_1    = "bl_dborder";
        $data_1     = array(
            'O_NO'          => $dborder_no,
            'O_USERIDX'     => $params['uuid'],
            'O_NICKNAME'    => $u_info['name'],
            'O_UIMAGE'      => $u_info['image'],
            'O_GOODSIDX'    => $g_info['goods_id'],
            'O_DATENO'      => $g_info['date_no'],
            'O_BUYNUM'      => $params['num'],
            'O_BLCOIN'      => $expend_blcoin,
            'O_IP'          => $this->ip,
            'O_RECORDNO'    => $dbno_info,
            'STATUS'        => 0,
        );
        $ist_o      = $this->CI->duobao_model->insert_data($data_1,$table_1);
        if (!$ist_o) {
            $this->CI->duobao_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_DUOBAO_TAKE_ORDER_FAIL);
            return false;
        }
        
        // 更改夺宝期数表信息
        $table_2    = "bl_dbgoods";
        $fields_2   = array('G_BOUGHTNUM'=>$g_info['bought_num']+$params['num']);
        $where_2    = array('IDX'=>$params['id'],'STATUS'=>0);
        if ($allowbuy_num == $params['num']) {// 扫底购买
            $fields_2['G_BUYSTATUS']    = 2;// 1进行中[购买中] 2等待开奖3已揭晓
            $fields_2['G_GSTATUS']      = 2; // 1投注中..2等待开奖3已开奖等用户填写地址4等待发货5已发货
        }
        $upt_g      = $this->CI->duobao_model->update_data($fields_2,$where_2,$table_2);
        if (!$upt_g) {
            $this->CI->duobao_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_UPDATE_DB_ORDER_NUM_FAIL);
            return false;
        }
        // 百联币变更历史记录
        $bl_data    = array(
            'G_USERIDX'     => $params['uuid'],
            'G_NICKNAME'    => $u_info['name'],
            'G_TYPE'        => 1,
            'G_SOURCE'      => 4,
            'G_BLCOIN'      => $expend_blcoin,
            'G_TOTALBLCOIN' => $u_info['blcoin'] -$expend_blcoin,
            'G_INFO'        => '夺宝消耗'.$expend_blcoin."游戏币",
            'STATUS'        => 0,
        );
        $this->load_library('game_lib');
        $ist_res    = $this->CI->game_lib->blcoin_change_his($bl_data);
        if (!$ist_res) {
            $this->CI->duobao_model->error();
            return false;
        }
        $this->CI->duobao_model->success();
        // 清空MC夺宝号分配
        if ($fields_2['G_GSTATUS']  == 2) {
            $this->clean_allo_dbno($g_info['date_no']);
        }
        return true;
    }
    
    /**
     * 给用户分配夺宝号
     * @param type $params = array('uuid',)
     */
    public function allo_dbno($params)
    {
        $prefix = $this->CI->passport->get('duobao_no');
        $key    = $prefix.$params['date_no'];
        $info   = $this->CI->cache->memcached->get($key);
        if (!$info) {// 后期将编号信息存储一份到文件
            $this->CI->error_->set_error(Err_Code::ERR_GET_DBNO_MC_INFO_FAIL);
            return false;
        }
        // 分配夺宝号
        $dbno_info  = json_decode($info,true);
        $new_arr    = array_diff($dbno_info['total_dbno'], $dbno_info['allo_dbno']);
        $select_key = array_rand($new_arr,$params['num']);
        if ($params['num'] > 1) {
            // 重组编号
            foreach ($select_key as $v) {
                $arr[]                      = $new_arr[$v];
                $dbno_info['allo_dbno'][]   = $new_arr[$v];
            }
        } else {
            $arr[]                      = $new_arr[$select_key];
            $dbno_info['allo_dbno'][]   = $new_arr[$select_key];
        }
        // 将选中的号码，插入已分配列表中
        $save   = $this->CI->cache->memcached->save($key, json_encode($dbno_info),0);
        if (!$save) {
            $this->CI->error_->set_error(Err_Code::ERR_MC_SERVICE);
            return false;
        }
        return $arr;
    }
    
    /**
     * 清除夺宝号分配
     * @param type $params = array('uuid',)
     */
    public function clean_allo_dbno($date_no)
    {
        $prefix = $this->CI->passport->get('duobao_no');
        $key    = $prefix.$date_no;
        $info   = $this->CI->cache->memcached->get($key);
        if (!$info) {
            return true;
        }
        return $this->CI->cache->memcached->delete($key);
    }
    
    /**
     * 获取最新揭晓列表
     * @param type $params
     */
    public function get_publish_list($params) 
    {
        // 获取数据总条数
        $where          = array('G_BUYSTATUS !='=>1,'STATUS'=>0);
        $table          = "bl_dbgoods";
        $total_count    = $this->CI->duobao_model->total_count($where,$table);
        if (!$total_count) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $condition      = "A.G_BUYSTATUS != 1 AND A.STATUS = 0 ORDER BY A.G_BUYSTATUS ASC,A.ROWTIMEUPDATE DESC LIMIT ".$params['offset'].",".$params['pagesize'];
        $join_condition = "A.G_GOODSIDX = B.IDX";
        $select         = "A.IDX id,A.G_GOODSNO goods_no,A.G_DATENO date_no,A.G_BLCOIN blcoin,A.G_SINGLEBLCOIN single_blcoin,A.G_LUCKNO luckno,A.G_GSTATUS status,A.ROWTIMEUPDATE update_time,B.G_NAME name,B.G_ICON icon";
        $tb_a           = "bl_dbgoods A";
        $tb_b           = "bl_dbgoods_conf B";
        $g_list         = $this->CI->duobao_model->left_join($condition, $join_condition,$select,$tb_a,$tb_b,true);
        if (!$g_list) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        
        // 获取下一期彩票开奖时间  此处时间等待时间待定
//        $ssc_info   = $this->sscopen_info();
//        if (!$ssc_info) {
//            $this->CI->error_->set_error(Err_Code::ERR_GET_SSCINFO_FAIL);
//            return false;
//        }
//        $time       = $ssc_info['nextopen_time'] - time();
        foreach ($g_list as $k=>&$v) {
            if ($v['status'] == 3) {// 1进行中[购买中]2等待开奖3已揭晓
                $v['time']   = strtotime($v['ROWTIMEUPDATE']);
                $v['status'] = 1;
            } else {
                $v['time']  = $time?$time:0;
                $v['status']= 2;
            }
            $v['total_num'] = $v['blcoin']/$v['single_blcoin'];
        }
        $data['pagecount']  = ceil($total_count/$params['pagesize']);
        $data['list']       = $g_list;
        return $data;
    }
    

    /**
     * 获取夺宝商品“中奖/待揭晓”等信息
     * @param type $params
     */
    public function get_publish_info($params)
    {
        // 获取夺宝商品信息
        $select         = "A.IDX id,A.G_DATENO date_no,A.G_GOODSNO goods_no,B.G_NAME name,B.G_IMGS imgs,G_DETAIL detail,A.G_BLCOIN blcoin,A.G_SINGLEBLCOIN single_blcoin,A.G_BUYSTATUS status,A.G_LUCKNO luckno,A.G_USERID luck_uuid,A.G_SSCDATE sscdate,A.D_SSCNO sscno,A.ROWTIMEUPDATE update_time";
        $condition      = "A.IDX = ".$params['id']." AND A.G_BUYSTATUS != 1 AND A.STATUS =0";
        $join_condition = "A.G_GOODSIDX = B.IDX AND B.STATUS = 0";
        $tb_a           = "bl_dbgoods A";
        $tb_b           = "bl_dbgoods_conf B";
        $g_info = $this->CI->duobao_model->left_join($condition, $join_condition,$select,$tb_a,$tb_b);
        if (!$g_info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $g_info['imgs'] = explode(';', trim($g_info['imgs'],';'));
        $luck_mess      = $this->CI->passport->get('dbinfo');
        $user_dbnum     = $this->user_dbnum($params['uuid'], $g_info['id']);
        if ($user_dbnum) {
            $info = $luck_mess['join_info'];
        } else {
            $info = $luck_mess['unjoin_info'];
        }
        // 组合返回数据
//        $ssc_info   = $this->sscopen_info();
//        if (!$ssc_info) {
//            $this->CI->error_->set_error(Err_Code::ERR_GET_SSCINFO_FAIL);
//            return false;
//        }
//        $time       = $ssc_info['nextopen_time'] - time();
        if ($g_info['status'] == 2) {// 等待开奖
            $status     = 2;
            $luck_info  = array(
                'date_no'   => $g_info['date_no'],
                'time'      => $time<=0?0:$time,
            );
        } else {
            // 获取中奖用户信息
            $lucku_info = $this->CI->utility->get_user_info($g_info['luck_uuid']);
            $dbnum      = $this->user_dbnum($g_info['luck_uuid'], $g_info['id']);
            if ($g_info['luck_uuid'] == $params['uuid']) {
                $info = $luck_mess['luck_info'];
            } elseif($user_dbnum) {
                $info = $luck_mess['unluck_info'];
            }
            $status     = 1;
            $luck_info  = array(
                'date_no'   => $g_info['date_no'],
                'num'       => $dbnum,
                'luck_user' => base64_decode($lucku_info['name']),
                'luck_uuid' => $g_info['luck_uuid'],
                'time'      => strtotime($g_info['update_time']),
                'luck_no'   => $g_info['luckno'],
                'sscdate'   => $g_info['sscdate'],
                'ccsno'     => $g_info['sscno'],
            );
        }
        $data   = array(
            'id'        => $g_info['id'],
            'date_no'   => $g_info['date_no'],
            'name'      => $g_info['name'],
            'imgs'      => $g_info['imgs'],
            'total_num' => ($g_info['blcoin']/$g_info['single_blcoin']),
            'status'    => $g_info['status']==3?1:2,
            'info'      => str_replace('x', $user_dbnum, $info),
            'detail'    => $g_info['detail'],
            'luck_info' => $luck_info,
        );
        return $data;
    }
    
    /**
     * 我的夺宝记录列表
     * @param type $params
     */
    public function get_mypublish_list($params)
    {
        $options['where']   = array('G_USERIDX'=>$params['uuid'],'STATUS'=>0);
        $options['fields']  = "O_GOODSIDX id";
        $options['groupby'] = "O_GOODSIDX";
        $order_his          = $this->CI->duobao_model->list_data($options,"bl_dborder");
        if (!$order_his) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $ids    = "";
        foreach ($order_his as $v) {
            $ids .=$v['id']; 
        }
        // 获取总条数
        $sql    = "SELECT COUNT(IDX) AS num FROM bl_dbgoods WHERE STATUS = 0 AND IDX in (".$ids.")";
        $total  = $this->CI->duobao_model->exec_by_sql($sql);
        if (!$total['num']) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        // 获取我的列表
        $sql    = "SELECT A.IDX id,A.G_DATENO date_no,A.G_GSTATUS g_status,B.G_NAME name,B.G_ICON icon,A.G_USERID luck_uuid,A.ROWTIMEUPDATE update_time FROM bl_dbgoods A,bl_dbgoods_conf B WHERE A.G_GOODSIDX = B.IDX AND A.STATUS = 0 AND A.IDX in (".$ids.") ORDER BY A.G_BUYSTATUS ASC,A.ROWTIMEUPDATE DESC LIMIT ".$params['offset'].",".$params['pagesize'];
        $g_list = $this->CI->duobao_model->exec_by_sql($sql,true);
        // 获取开奖数据
//        $ssc_info   = $this->sscopen_info();
//        if (!$ssc_info) {
//            $this->CI->error_->set_error(Err_Code::ERR_GET_SSCINFO_FAIL);
//            return false;
//        }
//        $time       = $ssc_info['nextopen_time'] - time();
        foreach ($g_list as $k=>&$v) {
            $v['is_luck']   = 0;
            if ($v['luck_uuid'] == $params['uuid']) {
                $v['is_luck']   = 1;
                if ($v['g_status'] == 3) {
                    $v['luck_status']   = 1;
                } elseif($v['g_status'] == 4) {
                    $v['luck_status']   = 2;
                }
            }
            if ($v['g_status'] == 2) {
                $v['status']    = 1;// 等待开奖
                $v['time']      = $time?$time:0;
            } else {
                $v['status']    = 2;// 已揭晓
                $v['time']      = strtotime($v['update_time']);
            }
            unset($v['g_status']);unset($v['update_time']);
        }
        $data['pagecount']  = ceil($total['num']/$params['pagesize']);
        $data['list']       = $g_list;
        return $data;
    }
    
    /**
     * 用户获取夺宝中奖 订单信息
     * @param type $params
     */
    public function get_dborder_info($params)
    {
        $condition      = "A.IDX=".$params['id']." AND A.G_USERID = ".$params['uuid']." AND A.G_BUYSTATUS = 3 AND  A.STATUS = 0";
        $join_condition = "A.G_GOODSIDX = B.IDX AND B.STATUS = 0";
        $select         = "A.IDX id,A.G_DATENO date_no,A.G_GOODSNO goods_no,A.G_GSTATUS status,B.G_NAME name,B.G_ICON icon";
        $info           = $this->CI->duobao_model->left_join($condition, $join_condition,$select,"bl_dbgoods AS A","bl_dbgoods_conf AS B");
        if (!$info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        if ($info['status'] == 3) {
            $info['status'] = 1;
        } elseif($info['status'] == 4) {
            $info['status'] = 2;
        } elseif($info['status'] == 5) {
            $info['status'] = 3;
        }
        return $info;
    }
    
    /**
     * 获取夺宝商品参与历史记录
     * @param type $params
     */
    public function get_dbhistory($params)
    {
        // 获取数据总条数
        $table  = "bl_dborder";
        $where  = array('STATUS'=>0);
        $total_count    = $this->CI->duobao_model->total_count($where,$table);
        if (!$total_count) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        // 获取列表
        $options['where']   = $where;
        $options['fields']  = "IDX id,O_USERIDX uuid,O_NICKNAME name,O_UIMAGE image,O_BUYNUM num,O_IP ip,UNIX_TIMESTAMP(ROWTIME) time";
        $options['limit']   = array('size'=>$params['pagesize'],'page'=>$params['offset']);
        $options['orderby'] = "IDX DESC";
        $data['list']       = $this->CI->duobao_model->list_data($options,$table);
        $data['pagecount']  = ceil($total_count/$params['pagesize']);
        return $data;
    }
    
    /**
     * 获取夺宝消息快报
     * @param type $params
     */
    public function get_dbmessage($params)
    {
        $options['where']   = array('STATUS'=>0);
        $options['fields']  = "W_USERIDX uuid,W_NICKNAME name,W_GNAME gname,UNIX_TIMESTAMP(ROWTIME) time";
        $options['limit']   = array('size'=>100,'page'=>0);
        $options['orderby'] = "IDX DESC";
        $data['list']       = $this->CI->duobao_model->list_data($options,"bl_dbwin_his");
        if (!$data['list']) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        return $data;
    }
    
    /**
     * 获取商品分类列表
     * @param type $params
     * @return boolean
     */
    public function get_type_list($params)
    {
        $options['where']   = array('STATUS'=>0);
        $options['fields']  = "IDX id,G_TYPE type,G_NAME name";
        $data['list']       = $this->CI->duobao_model->list_data($options,"bl_dbgoods_type");
        if (!$data['list']) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        return $data;
    }
    
    /**
     * 商品期号订单绑定收货地址
     */
    public function do_bind_address($params)
    {
        // 查询订单状态
        $table  = "bl_dbwin_his";
        $where  = array('W_USERIDX'=>$params['uuid'],'W_GOODSIDX'=>$params['id'],'STATUS'=>0);
        $o_info = $this->CI->duobao_model->get_one($where,$table);
        if (!$o_info) {
            $this->CI->error_->set_error(Err_Code::ERR_NOT_FOUND_USER_DBORDER_FAIL);
            return false;
        }
        if ($o_info['W_RECEIVEIDX']) {
            $this->CI->error_->set_error(Err_Code::ERR_DBORDER_ADDRESS_EXISTS_FAIL);
            return false;
        }
        // 判断该收货地址
        $table_1= "bl_dbaddress";
        $where_1= array('IDX'=>$params['address_id'],'A_USERIDX'=>$params['uuid'],'STATUS'=>0);
        $a_info = $this->CI->duobao_model->get_one($where_1,$table_1);
        if (!$a_info) {
            $this->CI->error_->set_error(Err_Code::ERR_ABOVE_QUOTA_FAIL);
            return false;
        }
        $this->CI->duobao_model->start();
        $fields = array('W_RECEIVEIDX'=>$params['address_id']);
        $upt_w  = $this->CI->duobao_model->update_data($fields,$where,$table);
        if (!$upt_w) {
            $this->CI->duobao_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_UPDATE_USER_DBORDER_FAIL);
            return false;
        }
        $this->CI->duobao_model->success();
        return true;
    }
    
    /**
     * 获取地址列表
     */
    public function get_address_list($params)
    {
        // 获取总条数
        $table  = "bl_dbaddress";
        $where  = array('A_USERIDX'=>$params['uuid'],'STATUS'=>0);
        $count  =$this->CI->duobao_model->total_count($where,$table);
        if (!$count) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        $options['where']   = $where;
        $options['fields']  = "IDX id,A_NAME receive,A_MOBILE mobile,A_ADDRESS address,A_USE is_use";
        $options['orderby'] = "A_USE DESC";
        $options['limit']   = array('size'=>$params['pagesize'],'page'=>$params['offset']);
        $data['list']       = $this->CI->duobao_model->list_data($options,$table);
        $data['pagecount']  = ceil($count/$params['pagesize']);
        return $data;
    }
    
    /**
     * 添加收货信息
     */
    public function do_add_address($params)
    {
        $this->CI->duobao_model->start();
        $fields = array('A_USE'=>0);
        $where  = array('A_USERIDX'=>$params['uuid'],'A_USE'=>1,'STATUS'=>0);
        $upt_a  = $this->update_address($fields,$where);
        if (!$upt_a) {
            $this->CI->duobao_model->error();
            return false;
        }
        $data   = array(
            'A_USERIDX' => $params['uuid'],
            'A_NAME'    => $params['receive'],
            'A_MOBILE'  => $params['mobile'],
            'A_ADDRESS' => $params['address'],
            'A_USE'     => 1,
            'STATUS'    => 0,
        );
        $ist_a  = $this->CI->duobao_model->insert_data($data,"bl_dbaddress");
        if (!$ist_a) {
            $this->CI->duobao_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_ADD_ADDRESS_FAIL);
            return false;
        }
        $this->CI->duobao_model->success();
        return true;
    }
    
    /**
     * 更新地址信息
     * @param type $params
     */
    public function do_update_address($params)
    {
        if ($params['receive']) {
            $fields['A_NAME']   = $params['receive'];
        }
        if ($params['mobile']) {
            $fields['A_MOBILE']   = $params['mobile'];
        }
        if ($params['address']) {
            $fields['A_ADDRESS']   = $params['address'];
        }
        if ($params['is_use'] == 1) {
            $fields['A_USE']    = 1;
        } elseif($params['is_use'] == 2) {
            $fields['A_USE']    = 0;
        }
        if (!$fields) {
            return true;
        }
        $this->CI->duobao_model->start();
        if ($fields['A_USE'] == 1) {
            $upt_a  = $this->update_address(array('A_USE'=>0), array('A_USE'=>1,'A_USERIDX'=>$params['uuid']));
            if (!$upt_a) {
                $this->CI->duobao_model->error();
                return false;
            }
        }
        $where  = array('IDX'=>$params['id']);
        $upt_a  = $this->update_address($fields, $where);
        if (!$upt_a) {
            $this->CI->duobao_model->error();
            return false;
        }
        $this->CI->duobao_model->success();
        return true;
    }
    
    /**
     * 更新地址信息
     * @param array $fields 更新数据
     * @param array $where  更新条件
     * @return bool
     */
    public function update_address($fields,$where)
    {
        $res = $this->CI->duobao_model->update_data($fields,$where,"bl_dbaddress");
        if (!$res) {
            $this->CI->error_->set_error(Err_Code::ERR_UPDATE_ADDRESS_FAIL);
            return false;
        }
        return true;
    }
    
    /**
     * 删除地址
     */
    public function do_del_address($params)
    {
        $this->CI->duobao_model->start();
        $fields = array('STATUS'=>1);
        $where  = array('IDX'=>$params['id'],'A_USERIDX'=>$params['uuid']);
        $upt_a  = $this->update_address($fields, $where);
        if (!$upt_a) {
            $this->CI->duobao_model->error();
            return false;
        }
        $this->CI->duobao_model->success();
        return true;
    }
    
    /**
     * 夺宝统一开奖方法
     */
    public function dbopen()
    {
        // 获取已夺宝完成列表(等待开奖的列表)
        $options['where']   = array('G_BUYSTATUS'=>2,'STATUS'=>0);
        $options['fields']  = "IDX id,G_DATENO date_no,G_GOODSIDX goods_idx,G_NAME name,G_BUYSTATUS b_status,G_GSTATUS g_status,G_BLCOIN blcoin,G_SINGLEBLCOIN single_blcoin,ROWTIMEUPDATE update_time";
        $prepare_list       = $this->CI->duobao_model->list_data($options,"bl_dbgoods");
        if (!$prepare_list) {
            return true;
        }
        
        // 获取最近一期ssc
        $ssc_info   = $this->sscopen_info();
        if (!$ssc_info) {
            log_message('error', "时时彩信息获取失败".time());
            return false;
        }
        $sscno  = implode("", explode(",", trim($ssc_info['opencode'],",")));
        // 判断该时时彩期号是否开过奖
        if ($prepare_list[0]['update_time'] > $ssc_info['opentimestamp']) {
            return true;
        }
        // 开奖操作
        $this->CI->duobao_model->start();
        foreach ($prepare_list as $k=>$v) {
            $luckno     = $this->luck_algorithm(array('sscno'=>$sscno,'total_num'=>($v['blcoin']/$v['single_blcoin'])));// 计算幸运号码
            $luck_user  = $this->get_luck_user($v['id'],$luckno);// 查找中奖用户
            // 更新夺宝期数表
            $data[]     = array(
                'IDX'           => $v['id'],
                'G_BUYSTATUS'   => 3,
                'G_USERID'      => $luck_user['uuid'],
                'G_SSCDATE'     => $ssc_info['expect'],
                'D_SSCNO'       => $sscno,
                'G_LUCKNO'      => $luckno,
                'G_GSTATUS'     => 3,
            );
            // 夺宝中彩历史记录表
            $data_2[]   = array(
                'W_USERIDX'     => $luck_user['uuid'],
                'W_NICKNAME'    => $luck_user['nickname'],
                'W_GOODSIDX'    => $v['id'],
                'W_GNAME'       => $v['name'],
                'W_DATENO'      => $v['date_no'],
                'W_BUYNUM'      => $luck_user['buy_num'],
                'W_LUCKNO'      => $luckno,
                'W_RECEIVEIDX'  => '',
                'STATUS'        => 0,
            );
        }
        
        $upt_g  = $this->CI->duobao_model->update_batch($data,'IDX',"bl_dbgoods");
        if (!$upt_g) {
            log_message('error', "夺宝开奖数据更新失败");
            $this->CI->duobao_model->error();
            return false;
        }
        $ist_w  = $this->CI->duobao_model->insert_batch($data_2,'bl_dbwin_his');
        if (!$ist_w) {
            log_message('error', "夺宝开奖历史数据插入失败");
            $this->CI->duobao_model->error();
            return false;
        }
        $this->CI->duobao_model->success();
        return true;
    }
    
    /**
     * 计算获取幸运号码
     * @param type $params
     */
    public function luck_algorithm($params)
    {
        // 幸运号码=（时时彩开奖号码÷奖品总需人次）取余数+10000001
        return ($params['sscno']%$params['total_num']) + 10000001;
    }
    
    /**
     * 获取用户中奖订单信息
     */
    public function get_luck_user($goods_id,$luckno)
    {
        $table              = "bl_dborder";
        $options['where']   = array('O_GOODSIDX'=>$goods_id,'STATUS'=>0);
        $options['fields']  = "O_USERIDX uuid,O_NICKNAME nickname,O_BUYNUM buy_num,O_RECORDNO dbno";
        $order_list         = $this->CI->duobao_model->list_data($options,$table);
        if (!$order_list) {
            return false;
        }
        foreach ($order_list as $k=>$v) {
            if (in_array($luckno, json_decode($v['dbno'],true))) {
                $array  = array('uuid'=>$v['uuid'],'name'=>$v['name'],'buy_num'=>$v['buy_num']);
                return $array;
            }
        }
        return false;
    }
    
    /**
     * 获取时时彩开奖信息，以及下期开奖时间
     */
    public function sscopen_info($row = 1)
    {
        $key    = $this->CI->passport->get('sscluck_info');
        $info   = $this->CI->cache->memcached->get($key);
        if ($info) {
            return json_decode($info,true);
        }
        $ten_time       = strtotime(date('Ymd 10:00:00'));// 当天10点
        $twentytwo_time = strtotime(date('Ymd 22:00:00'));// 当天22点
        $tttf_time      = strtotime(date('Ymd 23:55:00'));;// 当天23点55分
        $nextday_ten    = $ten_time + 86400;// 第二天10点
        
        $url        = "http://f.apiplus.cn/cqssc-".$row.".json";
        $content    = $this->CI->utility->get($url);
        $content_arr= json_decode($content,true);
        if (!$content_arr) {
            $this->CI->error_->set_error(Err_Code::ERR_GET_CQSSC_INFO_FAIL);
            return false;
        }
        $ssc_info   = $content_arr['data'][0];
        if (!$ssc_info) {
            return false;
        }
        // 获取下期开奖时间
        if ($ssc_info['opentimestamp'] >= $ten_time && $ssc_info['opentimestamp'] < $twentytwo_time) {
            $ssc_info['nextopen_time']  = $ssc_info['opentimestamp'] + 600;
        } elseif($ssc_info['opentimestamp'] >= $twentytwo_time && $ssc_info['opentimestamp'] < $tttf_time) {
            $ssc_info['nextopen_time']  = $ssc_info['opentimestamp'] + 600;
        } else {
            $ssc_info['nextopen_time']  = $nextday_ten;
        }
        $this->CI->cache->memcached->save($key,json_encode($ssc_info),60);
        return $ssc_info;
    }
    
    
    
    // ----------------------------                   测试区            ----------------------
        
    /**
     * 获取时时彩中奖号码
     * @return int 5位的号码
     */
    public function ssc_luckno($row = 1)
    {
        $url        = "http://f.apiplus.cn/cqssc.json";
        $content    = $this->CI->utility->get($url);
        $content_arr= json_decode($content,true);
        if (!$content_arr) {
            $this->CI->error_->set_error(Err_Code::ERR_GET_CQSSC_INFO_FAIL);
            return false;
        }
        return $content_arr;
    }
    
}

