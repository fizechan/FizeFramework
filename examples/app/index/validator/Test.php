<?php


namespace app\index\validator;

use fize\security\Validator;


class Test extends Validator
{

    public function __construct()
    {
        parent::__construct();

        $this->rules = [
            'username' => [
                'isset',
                'notEmpty',
                'maxLength' => 50,
            ],
            'nickname' => 'require',
            'password' => [
                'isset',
                'confirm' => 'password_confirm'
            ],
            'email'    => 'email',
        ];

        $this->names = [
            'username' => '用户名',
            'nickname' => '昵称',
            'password' => '密码',
            'email'    => '邮箱',
        ];

        $this->scenes = [
            'add'       => [],
            'edit'      => [],
            'tvalidate' => ['username', 'password', 'email'],
        ];

        $this->sceneDatas = [
            'tvalidate' => [
                'username'         => 'FizeChan',
                'email'            => 'chenfengzhan@qq.com',
                'password'         => '123456',
                'password_confirm' => '123456'
            ]
        ];
    }
}