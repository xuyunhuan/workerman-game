<?php
namespace Server;
/**
 * 用户模型
 *
 * @auther moxiaobai
 * @since  2014/11/13
 *
 */

use \Lib\Store;
use \Server\Model;

class Member extends Model {

    private $_store;

    protected $_result = array(
        'SUCCESS'                  =>  array('code'=>1,    'msg' => 'successful'),
        'NO_DATA_EXIST'            =>  array('code'=>100,  'msg' => 'No data exist'),
        'ERROR_USERNAME'           =>  array('code'=>400,  'msg' => '用户名为空'),
        'ERROR_PASSWORD'           =>  array('code'=>401,  'msg' => '用户密码为空'),
        'ERROR_USERNAME_PASSWORD'  =>  array('code'=>402,  'msg' => '用户或者密码错误'),
    );

    public function __Construct() {
        $this->_store    = Store::instance('game');

        parent::__Construct();
    }

    /**
     * @param  array $params
     * @return string 二进制文件
     */
    public function getPbUserInfo($params) {
        $mid    = $params[0];
        $result = $this->userInfoByMid($mid);
        if($result) {
            $tip = $this->_result['SUCCESS'];
            $this->_pbResult->setCode($tip['code']);
            $this->_pbResult->setMsg($tip['msg']);

            foreach($result as $val) {
                $this->_pbResult->appendData($val);
            }
        } else {
            $tip = $this->_result['NO_DATA_EXIST'];
            $this->_pbResult->setCode($tip['code']);
            $this->_pbResult->setMsg($tip['msg']);
        }

        return $this->formatBuffer($this->_pbResult);
    }

    /**
     * 验证用户登录
     *
     * @param  array $params
     * @return mixed
     */
    public function authLogin($params)
    {
        $username = $params[0];
        $password = md5(md5($params[1]));

        $sql = "SELECT m_id,m_nickname,m_email,m_regtime FROM `t_member`
                WHERE m_nickname='{$username}' and m_password = '{$password}'
                limit 1";
        $result = $this->_db->row($sql);
        if ($result) {
            $tip = $this->_result['SUCCESS'];

            foreach ($result as $val) {
                $this->_pbResult->appendData($val);
            }
        } else {
            $tip = $this->_result['ERROR_USERNAME_PASSWORD'];
        }
        $this->_pbResult->setCode($tip['code']);
        $this->_pbResult->setMsg($tip['msg']);
        $buffer = $this->formatBuffer($this->_pbResult);
        return $buffer;
    }

    /**
     * 注册用户
     * @param $username
     * @param $password
     * @return array
     */
    public function authReg($username, $password) {
        $password = md5(md5($password));

        $data = array(
            'm_nickname' => $username,
            'm_password' => $password,
            'm_email'    => 'tt@qq.com',
            'm_ip'       => '2147483647',
            'm_regtime'  => time()
        );
        $result = $this->_db->insert('t_member')->cols($data)->query();
        return $result;
    }

    /**
     * 获取用户信息
     *
     * @param $mid
     * @return mixed
     */
    private function userInfoByMid($mid) {
        $key = "GAME_USER_INFO_{$mid}";

        $userInfo = $this->_store->get($key);
        if($userInfo) {
            return $userInfo;
        }

        $sql = "SELECT m_id,m_nickname,m_email,m_regtime FROM `t_member`
                WHERE m_id='{$mid}'
                limit 1";
        $result = $this->_db->row($sql);
        if($result) {
            $this->_store->set($key, $result);
        }

        return $result;

    }
}