<?php

namespace Ayang\ApiManager\Test\example;


use Ayang\ApiManager\Attr\api;
use Ayang\ApiManager\Attr\apiPrefix;
use Ayang\ApiManager\Attr\middleware;
use Ayang\ApiManager\Attr\param;
use Ayang\ApiManager\Attr\request;
use Ayang\ApiManager\Attr\response;
use Ayang\ApiManager\Attr\respField;
use Ayang\ApiManager\Attr\fullUrl;
#[apiPrefix('/user')]
class userController
{
    #[api(name:"获取用户信息", path: "/get", method: "get", desc: "返回参考保存用户信息字段", category: "用户信息")]
    #[middleware(['check_login'])]
    #[respField("real_status", "bool", "实名认证 0未提交1审核中2通过3拒绝")]
    #[respField("education_status", "bool", "学历认证 0未提交1审核中2通过3拒绝")]
    #[respField("work_status", "bool", "company认证 0未提交1审核中2通过3拒绝")]
    #[respField("activity", "string", "活跃度")]
    #[respField("is_reg", "bool", "是否注册")]
    #[request([])]
    public function get()
    {
    }

    #[api(name:"保存用户信息", path: "/save", method: "post", desc: "", category: "用户信息")]
    #[middleware(['check_login'])]
    #[fullUrl("/save")]
    #[param("head_img", "string", "", )]
    #[param("sex", "string", "m男f女null未知", )]
    #[param("birthday", "string", "", )]
    #[param("nike_name", "string", "", )]
    #[param("wechat_account", "string", "微信号", )]
    #[param("phone", "string", "", )]
    #[param("city", "string", "", )]
    #[param("region", "string", "区域", )]
    #[param("age", "int", "", )]
    #[param("height", "int", "", )]
    #[param("weight", "int", "", )]
    #[param("native", "string", "户籍", )]
    #[param("school", "string", "学校", )]
    #[param("company", "string", "公司", )]
    #[param("education", "string", "学历", )]
    #[param("career", "string", "职业", )]
    #[param("constellation", "string", "星座", )]
    #[param("salary", "string", "年薪", )]
    #[param("love_state", "string", "恋爱状态", )]
    #[param("is_graduate", "bool", "1毕业0在读", )]
    #[param("introduction", "string", "自我描述", )]
    #[param("interest", "string", "兴趣爱好", )]
    #[param("about_half", "string", "关于另一半", )]
    #[param("background", "string", "家庭背景", )]
    #[param("point_of_love", "string", "爱情观点", )]
    #[param("album", "string", "相册", )]
    #[param("marital_state", "string", "婚姻状况", )]
    #[param("tags", "string", "标签", )]
    #[param("unlock_condition", "string", "解锁条件", )]
    #[request(
        [
            'nike_name' => '遇见二狗',
            'head_img' => 'http://just-love.oss-cn-shanghai.aliyuncs.com/face/1/664652c92d47c',
            'phone' => '17521161690',
            'city' => '上海',
            'region' => '嘉定',
            'age' => '20',
            'height' => '100',
            'weight' => '100',
            'birthday' => '2002-10-10',
            'sex' => 'm',
            'native' => '江苏',
            'school' => '家里蹲大学',
            'company' => '又焦又虑',
            'education' => '博士',
            'career' => '职业',
            'constellation' => '金牛',
            'salary' => '100万',
            'love_state' => '单身',
            'is_graduate' => '1',
            'introduction' => '自我描述',
            'interest' => '兴趣爱好',
            'about_half' => '关于另一半',
            "background" => "我的家庭背景",
            "point_of_love" => "我的爱情观点",
            "unlock_condition" => [
                "education" => ["zhaunke"],
                "birthday" => ["2018", "2019"],
                "height" => [170, 190]
            ],
//            'album' => ["www.img.com"],
            'marital_state' => '未婚',
            'tags' => ['吃饭', '睡觉', '打豆豆'],
        ]
    )]
    public function update()
    {
    }

    #[api(name:"保存用户手机号", path: "/phone", method: "put", desc: "保存用户手机号", category: "用户信息")]
    #[param("code", "string", "code", )]
    #[middleware(exclude_middlewares: ['check_login2'])]
    #[request(['code' => "f65ca51a3fe79fcccdbde3593ea8d5f09e66f1ad418b02a13b728656b5bf7839"], right: false)]
    public function getPhoneNumber()
    {
    }

    #[api(name:"注销用户", path: "/logout", method: "delete", desc: "", category: "用户信息")]
    #[request(['code' => "f65ca51a3fe79fcccdbde3593ea8d5f09e66f1ad418b02a13b728656b5bf7839"], needRequest: false)]
    public function logout()
    {
    }
}