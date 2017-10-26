<?php
class Game_lib extends Base_lib {
    public $memo   = '白鹭平台不存在该游戏，获取撤出该游戏';
    public $game_fields     = 'IDX AS id,G_GAMEIDX AS game_id,G_NAME name,G_FILEDIRECTORY file_directory,G_GAMETYPE game_type,G_BLCOIN blcoin,G_BLCOINCURRENT blcoin_curr,G_INFO info,G_OPERATIONINFO operation_info,G_KEYS keys,G_CLOSE close,G_IMGS imgs,G_ICON icon,G_BUYNUM buy_num,(G_PLAYNUM+G_ADDVALUE) play_num,G_SHARENUM share_num,G_SHAREPLAYNUM shareplay_num,G_GAMESTAR game_star,G_GAMESTARNUM gamestar_num';
    public $game_fields_2   = 'A.IDX AS id ,A.G_GAMEIDX AS game_id,A.G_NAME name,A.G_FILEDIRECTORY file_directory,A.G_GAMETYPE game_type,A.G_BLCOIN blcoin,A.G_BLCOINCURRENT blcoin_curr,A.G_INFO info,A.G_OPERATIONINFO operation_info,A.G_KEYS `keys`,A.G_CLOSE `close`,A.G_IMGS imgs,A.G_ICON icon,A.G_BUYNUM buy_num,(A.G_PLAYNUM+A.G_ADDVALUE) play_num,A.G_SHARENUM share_num,A.G_SHAREPLAYNUM shareplay_num,A.G_GAMESTAR game_star,A.G_GAMESTARNUM gamestar_num';
    public function __construct() {
        parent::__construct();
        $this->load_model('game_model');
    }
    
    /**
     * 获取单机游戏列表（按照被玩次数排序）
     * @param type $params
     */
    public function get_normal_list($params)
    {
        // 获取游戏总条数
        $where          = "(G_GAMETYPE = 0 OR G_GAMETYPE = 1) AND G_CLOSE = 0 AND STATUS = 0";
        $total_count    = $this->CI->game_model->total_count($where,'bl_game');
        if (!$total_count) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            $this->CI->output_json_return();
        }
        $data['pagecount']  = ceil($total_count/$params['pagesize']);
        // 获取游戏列表数据
        $options['where']   = $where;
        $options['order']   = "(G_PLAYNUM+G_ADDVALUE) DESC";
        $options['fields']  = $this->game_fields;
        $options['limit']   = array('page'=>$params['offset'],'size'=>$params['pagesize']);
        $list               = $this->CI->game_model->list_data($options,'bl_game');
        $game_url           = $this->CI->passport->get('game_url');
        // 查询用户收费游戏是否购买
        $buy_his    = array();
        if ($params['uuid'] && $params['token']) {
            $buy_his = $this->get_buygame_his($params['uuid']);
        }
        foreach ($list as &$v) {
            $imgs   = explode(",", trim($v['imgs'],","));
            unset($v['imgs']);
            foreach ($imgs as $key=>$val) {
                $v['imgs'][$key]    = $game_url.$v['file_directory'].$val;
            }
            $v['icon']              = $game_url.$v['file_directory'].$v['icon'];
            $v['file_directory']    = $game_url.$v['file_directory']."/play/index.html";
            $v['buy_status']        = 0;
            if (in_array($v['id'], $buy_his)) {
                $v['buy_status']    = 1;
            }
        }
        $data['list']   = $list;
        return $data;
    }
    
    /**
     * 获取购买游戏历史记录
     */
    public function get_buygame_his($uuid)
    {
        $table              = "bl_gamebuy";
        $options['where']   = array('B_USERIDX'=>$uuid,'STATUS'=>0);
        $options['fields']  = "B_GAMEIDX game_id";
        $list               = $this->CI->game_model->list_data($options,$table);
        $info               = array();
        if ($list) {
            $info   = array_column($list,'game_id');
        }
        return $info;
    }
    
    /**
     * 获取网络游戏
     */
    public function get_online_list($params)
    {
        $options['where']   = array('G_GAMETYPE'=>2,'G_CLOSE'=>0,'STATUS'=>0);
        $total_count        = $this->CI->game_model->total_count($options['where'],'bl_game');
        if (!$total_count) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            $this->CI->output_json_return();
        }
        $data['pagecount']  = ceil($total_count/$params['pagesize']);
        $options['fields']  = $this->game_fields;
        $options['limit']   = array('page'=>$params['offset'],'size'=>$params['pagesize']);
        $options['order']   = "(G_PLAYNUM+G_ADDVALUE) DESC";
        $list               = $this->CI->game_model->list_data($options,'bl_game');
        
        $channel_id         = $this->CI->passport->get('bailu_channel_id');
        $appkey             = $this->CI->passport->get('bailu_appkey');
        $game_url           = $this->CI->passport->get('game_url');
        foreach ($list as $k=>&$v) {
            $imgs   = explode(",", trim($v['imgs'],","));
            unset($v['imgs']);
            foreach ($imgs as $key=>$val) {
                $v['imgs'][$key]    = $game_url."/online/".$v['game_id']."/".$val;
            }
            // 获取白鹭游戏url透传参数
            $qry_str        = '';
            if ($params['uuid'] && $params['token']) {
                $str    = $this->bailu_game_url_qru($channel_id, $appkey, $params['uuid']);
                $qry_str    = $str?$v['file_directory']."?".$str:"";
            }
            $v['file_directory']    = $qry_str;
        }
        $data['list']   = $list;
        return $data;
    }
    
    /**
     * 获取下载游戏
     */
    public function get_download_list($params)
    {
        $options['where']   = array('G_GAMETYPE'=>3,'G_CLOSE'=>0,'STATUS'=>0);
        $total_count        = $this->CI->game_model->total_count($options['where'],'bl_game');
        if (!$total_count) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            $this->CI->output_json_return();
        }
        $data['pagecount']  = ceil($total_count/$params['pagesize']);
        $options['fields']  = $this->game_fields;
        $options['limit']   = array('page'=>$params['offset'],'size'=>$params['pagesize']);
        $options['order']   = "(G_PLAYNUM+G_ADDVALUE) DESC";
        $list               = $this->CI->game_model->list_data($options,'bl_game');
        
        $game_url           = $this->CI->passport->get('game_url');
        foreach ($list as $k=>&$v) {
            $v['icon']  = $game_url."/downloadgame/".$v['game_id']."/".$v['icon'];
            $imgs       = explode(",", trim($v['imgs'],","));
            unset($v['imgs']);
            foreach ($imgs as $key=>$val) {
                $v['imgs'][$key]    = $game_url."/downloadgame/".$v['game_id']."/".$val;
            }
        }
        $data['list']   = $list;
        return $data;
    }
    
    /**
     * 获取游戏总排行榜
     * @param type $params
     */
    public function get_ranking_list($params)
    {
        // 获取游戏总条数
        $options['where']   = array('G_GAMETYPE !='=>3,'G_CLOSE'=>0,'STATUS'=>0);
        $total_count        = $this->CI->game_model->total_count($options['where'],'bl_game');
        if (!$total_count) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            $this->CI->output_json_return();
        }
        // 获取游戏列表
        $data['pagecount']  = ceil($total_count/$params['pagesize']);
        $options['fields']  = $this->game_fields;
        $options['limit']   = array('page'=>$params['offset'],'size'=>$params['pagesize']);
        $options['order']   = "(G_PLAYNUM+G_ADDVALUE) DESC";
        $list               = $this->CI->game_model->list_data($options,'bl_game');
        
        $channel_id         = $this->CI->passport->get('bailu_channel_id');
        $appkey             = $this->CI->passport->get('bailu_appkey');
        $game_url           = $this->CI->passport->get('game_url');
        $buy_his    = array();
        
        // 列表重组
        if ($params['uuid'] && $params['token']) {
            $buy_his = $this->get_buygame_his($params['uuid']);
        }
        foreach ($list as $k=>&$v) {
            $v['buy_status']    = 0;
            // 拼接3张截图
            $imgs               = explode(",", trim($v['imgs'],","));
            unset($v['imgs']);
            foreach ($imgs as $key=>$val) {
                if ($v['game_type'] == 2) {
                    $v['imgs'][$key]    = $game_url."/online/".$v['game_id']."/".$val;
                }elseif ($v['game_type'] == 3) {
                    $v['imgs'][$key]    = $game_url."/downloadgame/".$v['game_id']."/".$val;
                } else {
                    $v['imgs'][$key]    = $game_url.$v['file_directory'].$val;
                }
            }
            // 拼接游戏地址和ICON
            if ($v['game_type'] == 2) {// 网游
                $qry_str        = '';
                if ($params['uuid'] && $params['token']) {
                    $str    = $this->bailu_game_url_qru($channel_id, $appkey, $params['uuid']);
                    $qry_str    = $str?$v['file_directory']."?".$str:"";
                }
                $v['file_directory']    = $qry_str;
            }elseif($v['game_type'] == 3){
                $v['icon']              = $game_url."/downloadgame/".$v['game_id']."/".$v['icon'];
            } else {
                if ($v['game_type'] == 1) {// 收费游戏
                    if (in_array($v['id'], $buy_his)) {
                        $v['buy_status']    = 1;
                    }
                }
                $v['icon']              = $game_url.$v['file_directory'].$v['icon'];
                $v['file_directory']    = $game_url.$v['file_directory']."/play/index.html";
            }
        }
        $data['list']   = $list;
        return $data;
    }
    
    /**
     * 拼接白鹭平台，游戏url透传参数
     * @return type
     */
    public function bailu_game_url_qru($channel_id,$appkey,$uuid)
    {
        $keyData['appId']   = $channel_id;
        $keyData['time']    = time();
        $keyData['userId']  = $uuid;
        $str                = "";
        ksort($keyData);
        reset($keyData);
        foreach ($keyData as $key=>$value) {
            $str  .=  $key ."=". $value;
        }
        $user_info              = $this->CI->utility->get_user_info($uuid);
        if (!$user_info) {
            $this->CI->error_->set_error(Err_Code::ERR_OK);
            return false;
        }
        $keyData['sign']        = md5($str.$appkey);
        $keyData['userName']    = $user_info['name'];
        $keyData['userImg']     = $user_info['image'];
        $keyData['userSex']     = $user_info['sex'];
        $qry_str = http_build_query($keyData);
        return $qry_str;
    }
    
    /**
     * 获取游戏详情
     * @param type $params
     */
    public function game_detail($params)
    {
        $where  = array('IDX'=>$params['id'],'G_CLOSE'=>0,'STATUS'=>0);
        $info   = $this->CI->game_model->get_one($where,'bl_game',$this->game_fields);
        if (!$info) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            $this->CI->output_json_return();
        }
        // 校验游戏是否被收藏、是否被购买
        $info['favorite']   = 0;
        $info['buy_status'] = 0;
        if ($params['uuid'] && $params['token']) {
            $favorite   = $this->check_game_favorite($params['uuid'],$params['id']);
            if ($favorite) {
                $info['favorite']   = 1;
            }
            $buy    = $this->check_game_buy($params['uuid'], $params['id']);
            if ($buy) {
                $info['buy_status']   = 1;
            }
        }
        
        // 拼接游戏图标、路径
        $imgs       = explode(",", trim($info['imgs'],","));
        unset($info['imgs']);
        $game_url   = $this->CI->passport->get('game_url');
        if ($info['game_type'] == 2) {// 网游
            $channel_id = $this->CI->passport->get('bailu_channel_id');
            $appkey     = $this->CI->passport->get('bailu_appkey');
            $qry_str    = "";
            if ($params['uuid'] && $params['token']) {
                $str        = $this->bailu_game_url_qru($channel_id, $appkey, $params['uuid']);
                $qry_str    = $str?$info['file_directory']."?".$str:"";
            }
            $info['file_directory'] = $qry_str;
            foreach ($imgs as $v) {
                $info['imgs'][] = $game_url."/online/".$info['game_id']."/".$v;
            }
        }elseif ($info['game_type'] == 3) {// 下载游戏
            $info['icon']   = $game_url."/downloadgame/".$info['game_id']."/".$info['icon'];
            foreach ($imgs as $v) {
                $info['imgs'][] = $game_url."/downloadgame/".$info['game_id']."/".$v;
            }
        } else {
            $info['icon']   = $game_url.$info['file_directory'].$info['icon'];
            foreach ($imgs as $v) {
                $info['imgs'][] = $game_url.$info['file_directory'].$v;
            }
            $info['file_directory'] = $game_url.$info['file_directory']."play/index.html";
        }
        return $info;
    }

    /**
     * 校验游戏是否被收藏
     * @param int $id 游戏表IDX
     */
    public function check_game_favorite($uuid,$id)
    {
        $table  = "bl_gamefavorites";
        $where  = array('F_USERIDX'=>$uuid,'F_GAMEIDX'=>$id,'STATUS'=>0);
        $fields = "IDX";
        $exists = $this->CI->game_model->get_one($where,$table,$fields);
        if ($exists) {
            return true;
        }
        return false;
    }
    
    /**
     * 校验游戏是否被购买
     * @param type $uuid
     * @param type $id
     */
    public function check_game_buy($uuid,$id)
    {
        $table  = "bl_gamebuy";
        $where  = array('B_USERIDX'=>$uuid,'B_GAMEIDX'=>$id,'STATUS'=>0);
        $fields = "IDX";
        $exists = $this->CI->game_model->get_one($where,$table,$fields);
        if ($exists) {
            return true;
        }
        return false;
    }
    
    /**
     * 收藏游戏
     */
    public function do_favorite($params)
    {
        $table  = "bl_gamefavorites";
        $where  = array('F_GAMEIDX'=>$params['id'],'F_USERIDX'=>$params['uuid'],'STATUS'=>0);
        $fields = "IDX";
        $exists = $this->CI->game_model->get_one($where,$table,$fields);
        if ($exists) {
            return true;
        }
        
        $where_2    = array('IDX'=>$params['id'],'G_CLOSE'=>0,'STATUS'=>0);
        $fields_2   = "G_NAME AS name,G_GAMETYPE AS game_type";
        $game_info  = $this->CI->game_model->get_one($where_2,'bl_game',$fields_2);
        if (!$game_info) {
            $this->CI->error_->set_error(Err_Code::ERR_GAME_NOT_EXISTS_FAIL);
            $this->CI->output_json_return();
        }
        
        $this->CI->game_model->start();
        $name   = $this->CI->utility->get_user_info($params['uuid'],'name');
        $data   = array(
            'F_USERIDX'     => $params['uuid'],
            'F_NICKNAME'    => $name,
            'F_GAMEIDX'     => $params['id'],
            'F_GAMENAME'    => $game_info['name'],
            'F_GAMETYPE'    => $game_info['game_type'],
            'STATUS'        => 0,
        );
        $res = $this->CI->game_model->insert_data($data,$table);
        if (!$res) {
            log_message('error', "do_favorite:游戏收藏失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->game_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_GAME_FAVORITE_FAIL);
            $this->CI->output_json_return();
        }
        $this->CI->game_model->success();
        return true;
    }
    
    /**
     * 取消收藏游戏
     * @param type $params
     */
    public function do_cancel_favorite($params)
    {
        $table  = "bl_gamefavorites";
        $where  = array('F_GAMEIDX'=>$params['id'],'STATUS'=>0);
        $fields = "IDX";
        $exists = $this->CI->game_model->get_one($where,$table,$fields);
        if (!$exists) {
            return true;
        }
        // 取消收藏
        $this->CI->game_model->start();
        $fields_2   = array('STATUS'=>1);
        $where_2    = array('F_GAMEIDX'=>$params['id'],'STATUS'=>0);
        $res = $this->CI->game_model->update_data($fields_2,$where_2,$table);
        if (!$res) {
            log_message('error', "do_cancel_favorite:游戏取消收藏失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->game_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_CANCEL_FAVORITE_FAIL);
            $this->CI->output_json_return();
        }
        $this->CI->game_model->success();
        return true;
    }
    
    /**
     * 获取收藏列表
     * @param type $params
     */
    public function get_favorites_list($params)
    {
        $where          = "A.IDX = B.F_GAMEIDX AND B.F_USERIDX = ".$params['uuid']." AND A.G_CLOSE = 0 AND B.STATUS = 0 AND A.STATUS = 0";
        $table          = "bl_game AS A,bl_gamefavorites AS B";
        $total_count    = $this->CI->game_model->total_count($where,$table,'A.IDX');
        if (!$total_count) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            $this->CI->output_json_return();
        }
        $data['pagecount']  = ceil($total_count/$params['pagesize']);
        $sql                = "SELECT ".$this->game_fields_2." FROM ".$table." WHERE ".$where." ORDER BY B.ROWTIME LIMIT ".$params['offset'].",".$params['pagesize'];
        $list               = $this->CI->game_model->fetch($sql,'result');
        
        $channel_id         = $this->CI->passport->get('bailu_channel_id');
        $appkey             = $this->CI->passport->get('bailu_appkey');
        $game_url           = $this->CI->passport->get('game_url');
        foreach ($list as $k=>&$v) {
            $imgs   = explode(",", trim($v['imgs'],","));
            unset($v['imgs']);
            foreach ($imgs as $key=>$val) {
                if ($v['game_type'] == 2) {
                    $v['imgs'][$key]    = $game_url."/online/".$v['game_id']."/".$val;
                }elseif ($v['game_type'] == 3) {
                    $v['imgs'][$key]    = $game_url."/downloadgame/".$v['game_id']."/".$val;
                }  else {
                    $v['imgs'][$key]    = $game_url.$v['file_directory'].$val;
                }
            }
            if ($v['game_type'] == 2) {// 网游
                // 计算bailu签名
                $qry_str                = $this->bailu_game_url_qru($channel_id, $appkey, $params['uuid']);
                $v['file_directory']    = $v['file_directory']."?".$qry_str;
            }elseif($v['game_type'] == 3){
                $v['icon']              = $game_url."/downloadgame/".$v['game_id']."/".$v['icon'];
            } else {
                $v['icon']              = $game_url.$v['file_directory'].$v['icon'];
                $v['file_directory']    = $game_url.$v['file_directory']."/play/index.html";
            }
        }
        $data['list']   = $list;
        return $data;
    }
    
    /**
     * 将白鹭平台网游同步到本平台（只包含部分数据同步）
     * 每天同步一次
     */
    public function bailu_game_sync()
    {
        // 获取白鹭网游列表
        $url            = 'http://api.open.egret.com/Channel.gameList';
        // $url            = "http://api.egret-labs.org/Channel.gameList";
        $channel_id     = $this->CI->passport->get('bailu_channel_id');
        $para_qry       = 'app_id='.$channel_id;
        $return_content = $this->CI->utility->get($url,$para_qry);
        $bailu_list   = json_decode($return_content ,TRUE);
        if(!$bailu_list['game_list']) {
            log_message('info', "del_bailu_list:网游同步操作，白鹭平台暂无网游，将本平台所有网游同步移除");
            $this->del_bailu_list();
            return true;
        }
        $bl_list    = $bailu_list['game_list'];
        foreach ($bl_list as $v) {
            $bl_list2[$v['gameId']] = $v;
            $ids_1[$v['gameId']]    = $v['gameId'];
        }
        
        // 获取本平台网游
        $options['where']   = array('G_GAMETYPE'=>2,'STATUS'=>0);
        $options['fields']  = "IDX AS id,G_GAMEIDX AS game_id";
        $list               = $this->CI->game_model->list_data($options,'bl_game');
        if (!$list) {
            foreach ($ids_1 as $key=>$val) {
                $ist_data[] = array(
                    'G_GAMEIDX'         => $val,
                    'G_NAME'            => $bl_list2[$val]['name'],
                    'G_FILEDIRECTORY'   => $bl_list2[$val]['url'],
                    'G_INFO'            => $bl_list2[$val]['desc'],
                    'G_ICON'            => $bl_list2[$val]['icon'],
                    'G_OPERATIONINFO'   => $bl_list2[$val]['shortDesc'],
                    'G_CALLBACK'        => $bl_list2[$val]['payCallBackUrl'],
                );
            }
            $this->insert_bailu_list($ist_data);
            log_message('info', "ist_bailu_list:网游同步操作，平台无网游，将白鹭所有网游同步至本平台");
            return true;
        }
        foreach ($list as $k=>$v) {
            $ids_2[$v['id']]  = $v['game_id']; 
        }
        
        // 更新数据
        $ids    = array_intersect($ids_2,$ids_1);
        if ($ids) {
            foreach ($ids as $key=>$val) {
                $upt_data[] = array(
                    'IDX'               =>$key,
                    'G_NAME'            => $bl_list2[$val]['name'],
                    'G_FILEDIRECTORY'   => $bl_list2[$val]['url'],
                    'G_ICON'            => $bl_list2[$val]['icon'],
                    'G_INFO'            => $bl_list2[$val]['desc'],
                    'G_OPERATIONINFO'   => $bl_list2[$val]['shortDesc'],
                    'G_CALLBACK'        => $bl_list2[$val]['payCallBackUrl'],
                );      
            }
            $this->update_bailu_list($upt_data);
        }
        
        // 删除部分
        $ids    = array_diff($ids_2, $ids_1);
        if ($ids) {
            foreach ($ids as $key=>$val) {
                $upt_data[] = array(
                    'IDX'       =>$key,
                    'G_CLOSE'   => 1,
                    'STATUS'    => 1,
                    'G_MEMO'    => $this->memo,
                );      
            }
            log_message('info', "del_bailu_list:网游同步操作，白鹭平台部分网游被移除，则本平台网游执行同步移除");
            $this->update_bailu_list($upt_data);
        }
        
        // 插入部分
        $ids    = array_diff($ids_1, $ids_2);
        if ($ids) {
            foreach ($ids as $key=>$val) {
                $ist_data[] = array(
                    'G_GAMEIDX'         => $val,
                    'G_NAME'            => $bl_list2[$val]['name'],
                    'G_FILEDIRECTORY'   => $bl_list2[$val]['url'],
                    'G_ICON'            => $bl_list2[$val]['icon'],
                    'G_INFO'            => $bl_list2[$val]['desc'],
                    'G_OPERATIONINFO'   => $bl_list2[$val]['shortDesc'],
                    'G_CALLBACK'        => $bl_list2[$val]['payCallBackUrl'],
                );
            }
            $this->insert_bailu_list($ist_data);
        }
        return true;
    }
    
    /**
     * 白鹭平台网游数据更新， 同步更新到本平台
     * @param type $data
     * @return boolean
     */
    public function insert_bailu_list($data)
    {
        foreach ($data as $k=>&$v) {
            $v['G_GAMETYPE']        = 2;
            $v['G_BLCOIN']          = 0;
            $v['G_BLCOINCURRENT']   = 0;
            $v['G_SCOREORDERBY']    = 0;
            $v['G_GAMESCOREUNIT']   = '';
            $v['G_GAMESCOREMAX']    = 0;
            $v['G_GAMESCOREMAXTIME']= 0;
            $v['G_KEYS']            = '';
            $v['G_CLOSE']           = 1;
            $v['G_IMGS']            = "1.png,2.png,3.png";
            // $v['G_ICON']            = "i.png";
            $v['G_BUYNUM']          = 0;
            $v['G_PLAYNUM']         = 0;
            $v['G_SHARENUM']        = 0;
            $v['G_SHAREPLAYNUM']    = 0;
            $v['G_GAMESTAR']        = 5;
            $v['G_GAMESTARNUM']     = 0;
            $v['G_UPTIMEORDERBY']   = $this->zeit;
            $v['G_MEMO']            = '平台同步';
            $v['STATUS']            = 0;
        }
        $this->CI->game_model->insert_batch($data,'bl_game');
        return true;
    }
    
    /**
     * 白鹭平台网游数据更新， 同步更新到本平台
     */
    public function update_bailu_list($data)
    {
        $this->CI->game_model->update_batch($data,'IDX','bl_game');
        log_message('info', "update_bailu_list:网游同步操作，白鹭网游数据同步更新，以获取最新数据");
        return true;
    }
    
    /**
     * 白鹭平台网游 同步跟新到本平台
     */
    public function del_bailu_list($data = array())
    {
        // 全部移除
        if (empty($data)) {
            $fields = array('STATUS'=>1,'G_CLOSE'=>1,'G_MEMO'=>$this->memo);
            $where  = array('G_GAMETYPE'=>2,'STATUS'=>0);
            $res    = $this->CI->game_model->update_data($fields,$where,'bl_game');
            return true;
        }
        // 部分移除
        $this->CI->game_model->update_batch($data,'IDX','bl_game');
        return true;
    }
    
    /**
     * 记录签到游戏最高得分
     */
    public function do_upload_score($uuid,$score)
    {
        // 获取签到游戏
        $game_info  = $this->get_signin_game();
        if (!$game_info) {
            return false;
        }
        // 获取用户签到游戏最好成绩
        $name   = $this->CI->utility->get_user_info($uuid,'name');
        $where  = array('P_USERIDX'=>$uuid,'P_GAMEIDX'=>$game_info['game_id'],'STATUS'=>0);
        $fields = "P_GAMESCORE AS game_score";
        $exists = $this->CI->game_model->get_one($where,'bl_gamescoreusertop',$fields);
        if ($exists) {
            if (($exists['score_orderby'] == 0 && $score > $exists['game_score']) || ($exists['score_orderby'] == 1 && $score < $exists['game_score'])) {
                $fields_2   = array('P_GAMESCORE'=>$score,'P_NICKNAME'=>$name);
                $where_2    = $where;
                $upt_res    = $this->CI->game_model->update_data($fields_2,$where_2,'bl_gamescoreusertop');
                if (!$upt_res) {
                    $this->CI->error_->set_error(Err_Code::ERR_UPDATE_BEST_SCORE_FAIL);
                    return false;
                }
            }
            return true;
        }
        // 插入成绩
        $data   = array(
            'P_USERIDX'     => $uuid,
            'P_NICKNAME'    => $name,
            'P_GAMEIDX'     => $game_info['game_id'],
            'P_GAMENAME'    => $game_info['name'],
            'P_GAMESCORE'   => $score,
            'STATUS'        => 0,
        );
        $ist_res    = $this->CI->game_model->insert_data($data,'bl_gamescoreusertop');
        if (!$ist_res) {
            $this->CI->error_->set_error(Err_Code::ERR_INSERT_BEST_SCORE_FAIL);
            return false;
        }
        return true;
    }
    
    /**
     * 获取签到游戏详情
     */
    public function get_signin_game()
    {
        $where  = array('STATUS'=>0);
        $fields = "IDX AS id,G_GAMEIDX AS game_id,G_NAME AS name,G_SCOREORDERBY AS score_orderby,G_GAMESCOREUNIT AS score_unit";
        $info   = $this->CI->game_model->get_one($where,'bl_signgame',$fields);
        if (!$info) {
            $this->CI->error_->set_error(Err_Code::ERR_SIGNIN_GAME_NOT_EXISTS);
            return false;
        }
        return $info;
    }
    
    /**
     * 获取游戏排行（前10名）
     */
    public function get_score_ranking($params)
    {
        // 获取当前签到游戏
        $game_info  = $this->get_signin_game();
        if (!$game_info) {
            $this->CI->output_json_return();
        }
        $table  = "bl_gamescoreusertop";
        $options['where']   = array('P_GAMEIDX'=>$game_info['game_id'],'STATUS'=>0);
        $options['limit']   = array('size'=>10,'page'=>0);
        $options['fields']  ="P_USERIDX AS uuid,P_NICKNAME AS name,P_GAMESCORE AS score";
        if ($game_info['score_orderby'] == 0) {// 顺序
            $options['order']   = "P_GAMESCORE DESC";
        } else {
            $options['order']   = "P_GAMESCORE ASC";
        }
        $list   = $this->CI->game_model->list_data($options,$table);
        if (!$list) {
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            $this->CI->output_json_return();
        }
        foreach ($list as $k=>&$v) {
            if (base64_encode(base64_decode($v['name'])) == $v['name']) {
                $v['name']  = base64_decode($v['name']);
            }
            if ($v['uuid'] == $params['uuid']) {
                $data['myranking']   = array('ranking'=>$k+1,'score'=>$v['score'],'unit'=>$game_info['score_unit'],'name'=>$v['name']);
            }
            $v['unit']      = $game_info['score_unit'];
            $v['ranking']   = $k+1;
        }
        
        // 获取我的名次
        if (!$data['myranking']) {
            $where  = array('P_USERIDX'=>$params['uuid'],'P_GAMEIDX'=>$game_info['game_id'],'STATUS'=>0);
            $fields = "P_GAMESCORE AS score,P_NICKNAME AS name,";
            $info   = $this->CI->game_model->get_one($where,$table,$fields);
            if ($info) {
                if (base64_encode(base64_decode($info['name'])) == $info['name']) {
                    $info['name']  = base64_decode($info['name']);
                }
                // 获取我的名次
                $where_2    = array('P_GAMEIDX'=>$game_info['game_id'],'P_GAMESCORE>='=>$info['score'],'STATUS'=>0);
                $ranking    = $this->CI->game_model->total_count($where_2,$table);
                $data['myranking']  = array('ranking'=>$ranking,'score'=>$info['score'],'unit'=>$game_info['score_unit'],'name'=>  $info['name']);
            } else {
                $data['myranking']  = array();
            }
        }
        $data['list']   = $list;
        return $data;
    }
    
    /**
     * 游戏分享
     */
    public function do_share($params)
    {
        // 获取游戏信息
        $where      = array('IDX'=>$params['id'],'G_CLOSE'=>0,'STATUS'=>0);
        $fields     ="IDX id,G_NAME name,G_GAMETYPE type,G_SHARENUM share_num";
        $game_info  = $this->CI->game_model->get_one($where,'bl_game',$fields);
        if (!$game_info) {
            $this->CI->error_->set_error(Err_Code::ERR_GAME_NOT_EXISTS_FAIL);
            return false;
        }
        
        if (!$params['uuid'] || !$params['token']) {
            $this->CI->game_model->start();
            // 更新游戏分享次数
            $fields_2   = array('G_SHARENUM'=>$game_info['share_num']+1);
            $upt_res    = $this->CI->game_model->update_data($fields_2,$where,'bl_game');
            if (!$upt_res) {
                log_message('error', "do_share:游戏分享次数更新失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
                $this->CI->error_->set_error(Err_Code::ERR_UPDATE_GAME_SHARE_NUM_FAIL);
                $this->CI->game_model->error();
                return false;
            }
            $this->CI->game_model->success();
            $data['url']    = base_url()."game/pv_statistics?id=".$game_info['id'];
            return $data;
        }
        
        $table  = "bl_gameshare";
        $name   = $this->CI->utility->get_user_info($params['uuid'],'name');
        $this->CI->game_model->start();
        // 分享记录
        $ist_data   = array(
            'T_USERIDX'         => $params['uuid'],
            'T_NICKNAME'        => $name,
            'T_GAMEIDX'         => $params['id'],
            'T_GAMENAME'        => $game_info['name'],
            'T_GAMETYPE'        => $game_info['type'],
            'T_SHAREPLAYNUM'    => 0,
            'STATUS'            => 0,
        );
        $ist_res    = $this->CI->game_model->insert_data($ist_data,$table);
        if (!$ist_res) {
            log_message('error', "do_share:分享记录插入失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->error_->set_error(Err_Code::ERR_GAME_SHARE_FAIL);
            $this->CI->game_model->error();
            return false;
        }
        // 更新游戏分享次数
        $fields_2   = array('G_SHARENUM'=>$game_info['share_num']+1);
        $upt_res    = $this->CI->game_model->update_data($fields_2,$where,'bl_game');
        if (!$upt_res) {
            log_message('error', "do_share:游戏分享次数更新失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->error_->set_error(Err_Code::ERR_UPDATE_GAME_SHARE_NUM_FAIL);
            $this->CI->game_model->error();
            return false;
        }
        $this->CI->game_model->success();
        $data['url']    = base_url()."game/pv_statistics?id=".$ist_res."&type=share";
        log_message('info', "do_share:游戏分享成功;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
        return $data;
    }
    
    /**
     * 统计PV:游戏被玩次数、分享被玩次数
     */
    public function do_pv_statistics($params)
    {
        $this->CI->game_model->start();
        $type       = 1;// 统计普通游戏PV
        $game_id    = $params['id'];
        if ($params['type']) {
            $type       = 2;// 统计分享游戏PV
            $where      = array('IDX'=>$params['id'],'STATUS'=>0);
            $fields     = "T_GAMEIDX AS game_id,T_SHAREPLAYNUM AS share_play_num";
            $share_info = $this->CI->game_model->get_one($where,'bl_gameshare',$fields);
            if (!$share_info) {
                return true;
            }
            $game_id    = $share_info['game_id'];
            $share_fields['T_SHAREPLAYNUM'] = $share_info['share_play_num']+1;
            $upt_share  = $this->CI->game_model->update_data($share_fields,$where,'bl_gameshare');
            if (!$upt_share) {
                log_message('error', "do_pv_statistics:游戏被打开次数更新失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
                $this->CI->game_model->error();
                $this->CI->error_->set_error(Err_Code::ERR_UPDATE_GAME_PLAY_PV_FAIL);
                return false;
            }
        }
        // 获取游戏信息
        $where_2    = array('IDX'=>$game_id,'G_CLOSE'=>0,'STATUS'=>0);
        $fields_2   ="G_NAME name,G_GAMETYPE type,G_SHARENUM share_num,G_SHAREPLAYNUM share_play_num,G_PLAYNUM play_num";
        $game_info  = $this->CI->game_model->get_one($where_2,'bl_game',$fields_2);
        if (!$game_info) {
            log_message('error', "do_pv_statistics:游戏信息获取失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->game_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_GAME_NOT_EXISTS_FAIL);
            return false;
        }
        
        $game_fields['G_PLAYNUM']   = $game_info['play_num']+1;
        if ($type == 2) {
            $game_fields['G_SHAREPLAYNUM']  = $game_info['play_num']+1;
        }
        $upt_game   = $this->CI->game_model->update_data($game_fields,$where_2,'bl_game');
        if (!$upt_game) {
            log_message('error', "do_pv_statistics:游戏分享次数更新失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->game_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_UPDATE_GAME_SHARE_NUM_FAIL);
            return false;
        }
        $this->CI->game_model->success();
        return true;
    }
    
    /**
     * 白鹭签名方式
     * @return type
     */
    public function sign_for_bailu($params)
    {
        $appkey = $this->CI->passport->get('bailu_appkey');
        $str  = "";
        ksort($params);
        reset($params);
        foreach($params as $key=>$value)
        {
            if ($key == 'sign' || $key == 'per' || $key == 'page') {
                continue;
            }
            $str  .=  $key ."=". $value;
        }
        $sign_new =  md5($str.$appkey);   
        return $sign_new;
    }
    
    /**
     * 百联唤起后端支付接口---->后端支付接口唤起前端支付页面
     */
    public function do_wakeup_php($params)
    {
        //人民币转换百联币，检查用户百联币是否足够
        $rate       = $this->CI->passport->get('blcoin_rate');
        $blcoin     = ($params['money']*100) * $rate; // 所需要的百联币
        $user_info  = $this->CI->utility->get_user_info($params['uuid']);
        if (!$user_info) {
            log_message('error', "do_wakeup_php:获取用户信息失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            return false;
        }
        
        //获取游戏支付回调地址
        $where  = array('G_GAMEIDX'=>$params['game_id'],'G_GAMETYPE'=>2,'G_CLOSE'=>0,'STATUS'=>0);
        $fields = "IDX AS id,G_GAMEIDX AS game_no,G_CALLBACK callback";
        $table  = "bl_game";
        $g_info = $this->CI->game_model->get_one($where,$table,$fields);
        if(!$g_info['callback']){
            log_message('error', "do_wakeup_php:获取网游支付回调地址失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";游戏信息:".  json_encode($g_info).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->error_->set_error(Err_Code::ERR_ONLINE_GAME_WITHOUT_CALLBACK);
            return false;
        }
        $this->CI->game_model->start();
        //将订单插入数据库
        $data = array(
            'P_USERIDX'     => $params['uuid'],
            'P_NICKNAME'    => $user_info['name'],
            'P_GAMEIDX'     => $g_info['id'],
            'P_GAMENO'      => $g_info['game_no'],
            'P_PROPIDX'     => $params['goods_id'],
            'P_TOTALFEE'    => $params['money'],
            'P_TOTALBLCOIN' => $blcoin,
            'P_SUBJECT'     => $params['goods_name'],
            'P_DECRIPTION'  => $params['goods_name'],
            'P_BUYSTATUS'   => 2,
            'P_CALLBACK'    => $g_info['callback'],
            'P_EXT'         => $params['ext'],
            'P_PLATFORM'    => 1,
            'STATUS' => 0,
        );
        $order_id = $this->CI->game_model->insert_data($data,'bl_propbuy');
        if (!$order_id) {
            log_message('error', "do_wakeup_php:网游订单表插入失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";网游订单表插入数据:".  json_encode($data).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->game_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_BUY_PROP_FAIL);
            return false;
        }
        $this->CI->game_model->success();
        log_message('info', "do_wakeup_php:网游订单表插入成功;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";网游订单表插入数据:".  json_encode($data).";执行时间：".date('Y-m-d H:i:s',time()));
        $url_get = array(
            'id'            => $order_id,
            'name'          => $params['goods_name'],
            'uuid'          => $params['uuid'],
            'blcoin'        => $blcoin,
            'my_blcoin'     => $user_info['blcoin'], //剩余百联币
            'is_buy'        => $user_info['blcoin']>=$blcoin?1:0,
        );
        $url = $this->CI->passport->get('bailu_callback_url')."?".  http_build_query($url_get);
        return $url;
    }
    
    /**
     * 执行道具购买操作
     */
    public function do_buy_prop($params)
    {
        //获取订单信息
        $table      = "bl_propbuy";
        $where      = array('IDX'=>$params['id'],'STATUS'=>0);
        $fields     = "IDX id,P_GAMEIDX AS game_id,P_GAMENO AS game_no,P_PROPIDX prop_id,P_TOTALFEE total_rmb,P_TOTALBLCOIN total_blcoin,P_BUYSTATUS buy_status,P_CALLBACK callback,P_EXT ext";
        $order_info = $this->CI->game_model->get_one($where,$table,$fields);
        // 判断订单是否未支付状态
        if($order_info['buy_status'] != 2) {
            log_message('error', "do_buy_prop:该网游订单不允许支付;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";订单信息:".  json_encode($order_info).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->error_->set_error(Err_Code::ERR_ORDER_NOT_ALLOW_BUY);
            $this->CI->output_json_return();
        }
        
        $this->CI->game_model->start();
        // 1.查询用户信息-并开启行锁 （检查用户金币是否足够）
        $sql        = "SELECT IDX uuid,U_NAME name,U_BLCOIN blcoin FROM bl_user WHERE IDX = ".$params['uuid']. " AND STATUS = 0 FOR UPDATE";
        $user_info  = $this->CI->game_model->fetch($sql,'row');
        if (!$user_info) {
            log_message('error', "do_buy_prop:获取用户信息失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";用户信息:".  json_encode($user_info).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->game_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_DB_NO_DATA);
            return false;
        }
        if(($user_info['blcoin'] < $order_info['total_blcoin'])) {
            log_message('error', "do_buy_prop:用户百联币不足;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";用户信息:".  json_encode($user_info).";订单信息：".  json_encode($order_info).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->game_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_BLCOIN_NOT_ENOUGHT_FAIL);
            return false;
        }
        
        // 2.扣除百联币
        $fields_1   = array('U_BLCOIN' => $user_info['blcoin'] - $order_info['total_blcoin']);
        $where_1    = array('IDX'=>$params['uuid'],'STATUS'=>0);
        $rst        = $this->CI->game_model->update_data($fields_1,$where_1,'bl_user');
        if (!$rst) {
            log_message('error', "do_buy_prop:用户百联币更新失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";用户信息:".  json_encode($user_info).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->game_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_PROP_BUY_FAIL);
            return false;
        }
        // 3.修改道具购买状态
        $fields_2['P_BUYSTATUS']  = 0;
        $upt_res    = $this->CI->game_model->update_data($fields_2,$where,$table);
        if (!$upt_res) {
            log_message('error', "do_buy_prop:修改网游道具订单失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";修改字段:".  json_encode($fields_2).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->game_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_BUY_PROP_FAIL);
            return false;
        }
        
        // 4.记录百联币变更历史记录
        $bl_data    = array(
            'G_USERIDX'     => $params['uuid'],
            'G_NICKNAME'    => $user_info['name'],
            'G_TYPE'        => 1,
            'G_SOURCE'      => 3,
            'G_BLCOIN'      => $order_info['total_blcoin'],
            'G_TOTALBLCOIN' => $user_info['blcoin'] - $order_info['total_blcoin'],
            'G_INFO'        => '网游充值消耗'.$order_info['total_blcoin']."游戏币",
            'STATUS'        => 0,
        );
        $ist_res    = $this->blcoin_change_his($bl_data);
        if (!$ist_res) {
            log_message('error', "do_buy_prop:插入百联币变更历史记录失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";插入数据:".  json_encode($bl_data).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->game_model->error();
            return false;
        }
        
        //请求白鹭支付回调
        $bailu_post = array(
            'orderId'   => $order_info['id'],
            'userId'    => $params['uuid'],
            'money'     => $order_info['total_rmb'],
            'ext'       => $order_info['ext'],
            'time'      => time(),
        );
        $bailu_post['sign'] = $this->sign_for_bailu($bailu_post);
        //拼接支付回调url
        $call_back_data = $this->CI->utility->post($order_info['callback'] , $bailu_post);
        $call_back_data = json_decode($call_back_data , TRUE);
        //回调成功则修改订单状态为成功
        if($call_back_data['code'] != 0) {
            log_message('error', "do_buy_prop:请求白鹭支付回调,白鹭方支付失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($bailu_post).";url:".  $order_info['callback'].";return_data:".  json_encode($call_back_data).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->game_model->error();
            $this->CI->error_->set_error(Err_Code::ERR_BUY_PROP_FAIL);
            return false;
        }
        $this->CI->game_model->success();
        log_message('info', "do_buy_prop:网游支付成功;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";url:".  $order_info['callback'].";return_data:".  json_encode($call_back_data).";执行时间：".date('Y-m-d H:i:s',time()));
        return true;
    }
    
    /**
     * 获取签到游戏URL地址
     */
    public function get_sign_game($params)
    {
        $where  = array('STATUS'=>0);
        $fields = "G_FILEDIRECTORY file_directory";
        $info   = $this->CI->game_model->get_one($where,'bl_signgame',$fields);
        if (!$info) {
            $this->CI->error_->set_error(Err_Code::ERR_SIGNIN_GAME_NOT_EXISTS);
            return false;
        }
        $path   = $this->CI->passport->get('game_url');
        
        $this->load_library('user_lib');
        $u_info         = $this->CI->user_lib->get_register_info($params['uuid']);
        $para_          = "?user_id=".$u_info['user_id']."&passport_id=".$u_info['passport_id'];
        $data['url']    = $path.$info['file_directory']."/play/index.html".$para_;
        return $data;
    }
    
    /**
     * 购买收费游戏操作
     * @param type $params
     */
    public function do_buy($params)
    {
        // 判断该游戏是否允许购买
        $exist  = $this->check_game_buy($params['uuid'], $params['id']);
        if ($exist) {
            log_message('info', "do_buy:游戏已经购买过;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->error_->set_error(Err_Code::ERR_GAME_BOUGHT_FAIL);
            return false;
        }
        
        // 校验该游戏是否允许购买
        $where  = array('IDX'=>$params['id'],'STATUS'=>0);
        $fields = "G_NAME name,G_GAMETYPE type,G_BLCOIN blcoin,G_BLCOINCURRENT curr_blcoin";
        $game_info  = $this->CI->game_model->get_one($where,"bl_game",$fields);
        if ($game_info['type'] != 1) {
            log_message('error', "do_buy:只有单机游戏才允许购买;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).",游戏信息".  json_encode($game_info).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->error_->set_error(Err_Code::ERR_GAME_NOT_ALLOW_BUY_FAIL);
            return false;
        }
        // 判断用户百联币是否足够
        $u_info = $this->CI->utility->get_user_info($params['uuid']);
        if ($u_info['blcoin'] < $game_info['curr_blcoin']) {
            log_message('error', "do_buy:用户百联币不足;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).",用户信息：".  json_encode($u_info).";购买的游戏信息：".  json_encode($game_info).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->error_->set_error(Err_Code::ERR_BLCOIN_NOT_ENOUGHT_FAIL);
            return false;
        }
        // 执行购买操作
        $this->CI->game_model->start();
        $data   = array(
            'B_USERIDX'         => $params['uuid'],
            'B_NICKNAME'        => $u_info['name'],
            'B_GAMEIDX'         => $params['id'],
            'B_GAMENAME'        => $game_info['name'],
            'B_BLCOIN'          => $game_info['blcoin'],
            'B_BLCOINCURRENT'   => $game_info['curr_blcoin'],
            'STATUS'            => 0,
        );
        $ist_res    = $this->CI->game_model->insert_data($data,'bl_gamebuy');
        if (!$ist_res) {
            log_message('error', "do_buy:bl_gamebuy表购买记录插入失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).",插入数据：".  json_encode($data).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->error_->set_error(Err_Code::ERR_BUY_HIS_INSERT_FAIL);
            $this->CI->game_model->error();
            return false;
        }
        // 更新百联币
        $fields_2   = array('U_BLCOIN'=>$u_info['blcoin'] - $game_info['curr_blcoin']);
        $where_2    = array('IDX'=>$params['uuid'],'STATUS'=>0);
        $table_2    = "bl_user";
        $upt_res    = $this->CI->game_model->update_data($fields_2,$where_2,$table_2);
        if (!$upt_res) {
            log_message('error', "do_buy:百联币更新失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->error_->set_error(Err_Code::ERR_BUY_BLCOIN_DEDUCT_FAIL);
            $this->CI->game_model->error();
            return false;
        }
        
        // 插入百联币变更历史记录
        $ist_data   = array(
            'G_USERIDX'     => $params['uuid'],
            'G_NICKNAME'    => $u_info['name'],
            'G_TYPE'        => 1,
            'G_SOURCE'      => 2,
            'G_BLCOIN'      => $game_info['curr_blcoin'],
            'G_TOTALBLCOIN' => $u_info['blcoin'] - $game_info['curr_blcoin'],
            'G_INFO'        => '购买收费游戏消耗'.$game_info['curr_blcoin']."游戏币",
            'STATUS'        => 0,
        );
        $ist_res    = $this->blcoin_change_his($ist_data);
        if (!$ist_res) {
            log_message('error', "do_buy:插入百联币变更历史记录失败;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).";插入数据：".  json_encode($ist_data).";执行时间：".date('Y-m-d H:i:s',time()));
            $this->CI->game_model->error();
            return false;
        }
        $this->CI->game_model->success();
        log_message('info', "do_buy:单机游戏购买成功;".$this->CI->input->ip_address().";请求参数:".  http_build_query($params).date('Y-m-d H:i:s',time()));
        return false;
    }
    
    /**
     * 百联币变更历史记录
     */
    public function blcoin_change_his($data)
    {
        $table      = 'bl_blcoin_his';
        $ist_res    = $this->CI->game_model->insert_data($data,$table);
        if (!$ist_res) {
            $this->CI->error_->set_error(Err_Code::ERR_INSERT_BLCOIN_CHANGE_HIS_FAIL);
            return false;
        }
        return true;
    }
    
    
}

