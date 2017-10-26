<?php
// sign加密的key
$config['sign_key']     = 'BLSIgn7gXLvCu8h668o8buYRd';
$config['token_key']    = 'BLsfmepl7gXLvCu8hsoopyak';
$config['token_expire'] = 30*24*3600;// TOKEN有效期30天
$config['blcoin_rate']  = 1;// 1人民币（分） = 1游戏币
$config['points_rate']  = 1;// 1人民币（分） = 1百联积分
$config['access_token'] = 'BL_ATOKEN_';// access_token保存KEY
$config['duobao_no']    = 'BLDB_NO_';// 夺宝号存储位置  $prefix.$date_no.$goods_no
$config['sscluck_info'] = "BLSSC_INFO_";// 保存时时彩中奖信息

        
$config['exchange_mess_num']    = 5;// 消息快报最多展示条数

$config['game_index']   = '';//游戏中心首页
// 充值模块
$config['order_expire'] = '1800';// 订单有效期(单位s)
$config['order_list']   = '';// 订单列表页面
$config['front_callback']   = '';// 前端充值回调通知地址

$config['blcoin_rate']  = 1;// 游戏币与人民币比例 1（分人民币）/1（游戏币）
$config['blcoin_point_rate']  = 1;// 游戏币:积分比例 1积分=?游戏币
$config['tax_code']           = '6';// 6%


 


// H5游戏存放地址
$config['game_url']     = "http://game.ibl.cn";


// 白鹭接入网游配置
$config['bailu_channel_id']     = 21695;
$config['bailu_appkey']         = 'tJPzbf5rRBrNXYoNkHLZn';
// $config['bailu_callback_url']   = 'http://www.baidu.com';// 白鹭道具支付时,调用前端支付页面
$config['bailu_callback_url']   = 'http://www.ibl.cn/dest/recharge_egret.html';

// 百联OpenApi
$config['bl_appid']     = '4N3H9223G6';// 测试环境: 818c933737454de5a64e273333372764，生产：99ede9846e0b4ad2bdb0a63c61f500be  压测：Yn046239q1  // 4N3H9223G6
$config['bl_secret']    = '99796M3NW6T9KRo4Z7TVz6hbZye809r10';// 测试：3F7yk668BbO36AE73G7O25w82y3727133，生产：0QLyd1e665Iu61E8364q50EoWz668RN37   压测：Gz9N986366HMc22LaUPHV5P79ae1bb5ib// 99796M3NW6T9KRo4Z7TVz6hbZye809r10
$config['bl_salt']      = 0;
// 商户号、密钥(兑换平台)
$config['merid']    = '40161327';// 商户号
$config['secret']   = 'a354a6d123e1d2b98f2bf19c6adfad76';// 商户号密钥

// 商户号、密钥（支付中台)
$config['p_merid']  = '010090150505150';// 商户号
$config['p_secret'] = 'FED8A9737DC75516E1DAC38995F31D45';// 商户号密钥 测试：1B74EC7344C8F1321BE4464551169E27 生产：FED8A9737DC75516E1DAC38995F31D45

// 签到文案信息
$config['max_append_signin']    = 5;// 最多可补签次数
$config['signed_info']  = array(
    'week_append'       => '您可以使用积分补签哦，就可额外获得50积分的周奖励啦！',// 补签文案
    'week_signin'       => '加油哦，再坚持[XX]天就可以额外获得50积分的周奖励啦！',// 每7天显示行，“坚持周签到” 文案
    'last_insist'       => '',// 坚持月签 文案
    'last_append'       => '',// 月 “补签”文案
    'encourage_signin'  => 'i百联期待您每天光临！',//自然月的最后几天,“鼓励”文案
);


// 夺宝提示信息
$config['dbinfo']   = array(
    'unjoin_info'   => '您还没有参加本期幸运夺宝哦',
    'join_info'     => '您参加本期幸运夺宝x人次，祝好运',
    'luck_info'     => '您参加本期幸运夺宝x人次，很幸运',
    'unluck_info'   => '您参加本期幸运夺宝x人次，很遗憾',
);
