<?php

namespace app\controllers;


class UserController extends AppController
{

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    public function actionAuth() {
        self::setJson();

        $request = \Yii::$app->request;
        $get = $request->post();


        if(empty($get)) {
            return static::errAuthMessage();
        }


        $username = $request->post('login');
        $password = $request->post('password');


        if(!isset($username) || !isset($password)) {
            return static::errAuthMessage();
        }

        $query = \Yii::$app->db->createCommand('SELECT * FROM users WHERE username=:username AND password=:password');
        $post1 = $query->bindValues(['username' => $username, 'password' => $password]);
        $result = $post1->queryOne();

        if(!$result) {
            return static::errAuthMessage();
        }


        $result_json = [
            'status' => true,
            'token' => $result['auth_key'],
        ];

        return static::successAuthMessage($result_json);

    }

    public function successAuthMessage($result_json) {
        \Yii::$app->response->statusCode = 200;
        \Yii::$app->response->statusText = "Successful authorization";
        return $result_json;
    }

    public function errAuthMessage() {
        \Yii::$app->response->statusCode = 401;
        \Yii::$app->response->statusText = "Invalid authorization data";
        return [
            'status' => false,
            'message' => 'Invalid authorization data',
        ];
    }


}