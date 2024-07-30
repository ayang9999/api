<?php

namespace Ayang\ApiManager\Test\tmp;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class apiClient
{
    
    public Client $client;
    
    public function handel(string $m, string $url, array $p) : \Psr\Http\Message\ResponseInterface
    {
        return $this->client->request($m, $url, $p);
    }
    
    
    
     /**
     */
    public function _get()
    {
        $url = '/get';
        return $this->handel('GET', '/get', ['json' => func_get_args()]);
    }
    
     /**
     * @param string $head_img - $head_img 
     * @param string $sex - $sex m男f女null未知
     * @param string $birthday - $birthday 
     * @param string $nike_name - $nike_name 
     * @param string $wechat_account - $wechat_account 微信号
     * @param string $phone - $phone 
     * @param string $city - $city 
     * @param string $region - $region 区域
     * @param int $age - $age 
     * @param int $height - $height 
     * @param int $weight - $weight 
     * @param string $native - $native 户籍
     * @param string $school - $school 学校
     * @param string $company - $company 公司
     * @param string $education - $education 学历
     * @param string $career - $career 职业
     * @param string $constellation - $constellation 星座
     * @param string $salary - $salary 年薪
     * @param string $love_state - $love_state 恋爱状态
     * @param bool $is_graduate - $is_graduate 1毕业0在读
     * @param string $introduction - $introduction 自我描述
     * @param string $interest - $interest 兴趣爱好
     * @param string $about_half - $about_half 关于另一半
     * @param string $background - $background 家庭背景
     * @param string $point_of_love - $point_of_love 爱情观点
     * @param string $album - $album 相册
     * @param string $marital_state - $marital_state 婚姻状况
     * @param array $tags - $tags 标签
     * @param string $unlock_condition - $unlock_condition 解锁条件
     */
    public function _save(string $head_img = 'www.head.com', string $sex = null, string $birthday = null, string $nike_name = null, string $wechat_account = null, string $phone = null, string $city = null, string $region = null, int $age = null, int $height = 1, int $weight = null, string $native = null, string $school = null, string $company = null, string $education = null, string $career = null, string $constellation = null, string $salary = null, string $love_state = null, bool $is_graduate = null, string $introduction = null, string $interest = null, string $about_half = null, string $background = null, string $point_of_love = null, string $album = null, string $marital_state = null, array $tags = array (
  0 => 'dog',
  1 => 'cat',
), string $unlock_condition = null)
    {
        $url = '/save';
        return $this->handel('POST', '/save', ['query' => func_get_args()]);
    }
    
     /**
     * @param string $code - $code code
     */
    public function _phone(string $code = null)
    {
        $url = '/phone';
        return $this->handel('PUT', '/phone', ['query' => func_get_args()]);
    }
    
     /**
     */
    public function _logout()
    {
        $url = '/logout';
        return $this->handel('DELETE', '/logout', ['query' => func_get_args()]);
    }
}    