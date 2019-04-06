<?php

namespace app\controllers;

use app\models\Post;
use app\models\UploadForm;
use yii\web\UploadedFile;


class PostController extends AppController
{

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::className(),
                'optional' => [
                    'posts', 'get'
                ]
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'edit', 'delete', 'posts', 'get'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['posts', 'get'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'edit', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex() {
        return $this->render('index');
    }

    public function actionCreate() {
        self::setJson();

        $request = \Yii::$app->request;
        $get = $request->post();


        if(empty($get)) {
            $err = [
                'title' => 'params not exist',
                'message' => 'params not exist',
            ];
            return static::errMessage($err);
        }

        $title = $request->post('title');
        $anons = $request->post('anons');
        $text = $request->post('text');
        $tags = $request->post('tags');


        if(!isset($title) || !isset($anons) || !isset($text)) {
            $err = [
                'title' => 'params are empty',
                'message' => 'title or anons or text are empty',
            ];
            return static::errMessage($err);
        }


        $post = new Post();


        $result = $post->getPostByTitle($title);
        if($result) {
            $err = [
                'title' => 'already exist',
                'message' => 'post is exist with this title',
            ];
            return static::errMessage($err);
        }

        $model = new UploadForm();

        if (\Yii::$app->request->isPost) {
            $model->image = UploadedFile::getInstance($model, 'image');
            $imageLink = $model->upload();
            print_r($model);
            /*if (!$imageLink) {
                $err = [
                    'title' => 'image error',
                    'message' => 'invalid file format',
                ];
                return static::errMessage($err);
            }*/
        }
        print_r('sdf');
        die();


        $postData = [
            'title' => $title,
            'anons' => $anons,
            'text' => $text,
            'image' => $imageLink,
        ];

        $query = $post->create($postData);

        if(!$query) {
            $err = [
                'title' => 'error create',
                'message' => 'create error post',
            ];
            return static::errMessage($err);
        }

        $result = $post->getPostByTitle($title);
        if(!$result) {
            $err = [
                'title' => 'not find post',
                'message' => 'post is not find',
            ];
            return static::errMessage($err);
        }

        $post_id = $result['id'];

        self::createTags($post_id, $tags);


        $result_json = [
            'status' => true,
            'post_id' => $post_id,
        ];

        return static::successMessage($result_json);

    }

    public function actionEdit() {
        return $this->render('index');
    }

    public function actionDelete() {
        return $this->render('index');
    }

    public function actionPosts() {
        self::setJson();

        $request = \Yii::$app->request;
        $get = $request->post();

        if(empty($get)) {
            $err = [
                'title' => 'params not exist',
                'message' => 'params not exist',
            ];
            return static::errMessage($err);
        }

        $result_json = [
            'status' => true,
            'token' => 'test',
        ];

        return static::successMessage($result_json);
    }

    public function actionGet() {
        $posts = Post::find()->select('title, anons, text, tags, image')->all();
        return $this->render('posts', compact('posts'));
    }

    public function createTags($post_id, $tags) {

        if(!isset($post_id)) return;
        if(!isset($tags)) return;


        $post = new Post();

        $arTags = explode(',', $tags);

        foreach ($arTags as $tag) {
            $postData = [
                'title' => $tag,
                'post_id' => $post_id,
            ];
            $post->create($postData);

        }
    }

    public function successMessage($result_json) {
        \Yii::$app->response->statusCode = 201;
        \Yii::$app->response->statusText = "Successful creation";
        return $result_json;
    }

    public function errMessage($msg) {
        \Yii::$app->response->statusCode = 400;
        \Yii::$app->response->statusText = "Creating error";
        return [
            'status' => false,
            'message' => $msg,
        ];
    }

}
