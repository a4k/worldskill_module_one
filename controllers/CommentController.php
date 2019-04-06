<?php

namespace app\controllers;

use app\models\Post;
use app\models\Tag;
use app\models\Comment;
use app\models\UploadForm;
use yii\web\UploadedFile;


class CommentController extends AppController
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
                    'create'
                ]
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate($post_id) {

        self::setJson();

        $request = \Yii::$app->request;
        $get = $request->post();


        $post = new Post();


        if(!$post->getPostById($post_id)) {
            return static::errNotFoundMessage();
        }

        if(empty($get)) {
            $err = [
                'title' => 'params not exist',
                'message' => 'params not exist',
            ];
            return static::errMessage($err);
        }

        $author = $request->post('author');
        $commentString = $request->post('comment');
        $user = \Yii::$app->user->identity;
        $user_id = 0;
        if($user) {

            $author = 'admin';
            $user_id = $user->getId();
        }

        if(!$user) {

            if(!isset($author)) {
                $err = [
                    'title' => 'author is empty',
                    'message' => 'author are empty',
                ];
                return static::errMessage($err);
            }
        }

        if(!isset($commentString)) {
            $err = [
                'title' => 'comment are empty',
                'message' => 'comment are empty',
            ];
            return static::errMessage($err);
        }
        if(strlen($commentString) > 255) {
            $err = [
                'title' => 'comment ',
                'message' => 'more than 255 chars',
            ];
            return static::errMessage($err);
        }


        $comment = new Comment();

        $postData = [
            'author' => $author,
            'comment' => $commentString,
            'user_id' => $user_id,
            'post_id' => $post_id,
        ];

        $query = $comment->create($postData);

        if(!$query) {
            $err = [
                'title' => 'error create',
                'message' => 'create error comment',
            ];
            return static::errMessage($err);
        }

        $result_json = [
            'status' => true,
        ];

        return static::successMessage($result_json);

    }


    public function actionDelete($post_id, $comment_id) {

        self::setJson();

        $post = new Post();
        $comment = new Comment();


        if(!$post->getPostById($post_id)) {
            return static::errNotFoundMessage();
        }

        if(!$comment->getCommentById($comment_id)) {
            return static::errNotFoundComment();
        }


        $query = $comment->deleteComment($comment_id);

        if(!$query) {
            return static::errDeleteMessage('error deleting');
        }

        $result_json = [
            'status' => true,
        ];

        return static::successDeleteMessage($result_json);
    }

    public function successMessage($result_json) {
        \Yii::$app->response->statusCode = 201;
        \Yii::$app->response->statusText = "Successful creation";
        return $result_json;
    }
    public function successDeleteMessage($result_json) {
        \Yii::$app->response->statusCode = 201;
        \Yii::$app->response->statusText = "Successful delete";
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

    public function errDeleteMessage($msg) {
        \Yii::$app->response->statusCode = 400;
        \Yii::$app->response->statusText = "Deleting error";
        return [
            'status' => false,
            'message' => $msg,
        ];
    }

    public function errNotFoundMessage() {
        \Yii::$app->response->statusCode = 404;
        \Yii::$app->response->statusText = "Post not found";
        return [
            'status' => false,
            'message' => 'Post not found',
        ];
    }

    public function errNotFoundComment() {
        \Yii::$app->response->statusCode = 404;
        \Yii::$app->response->statusText = "Comment not found";
        return [
            'status' => false,
            'message' => 'Comment not found',
        ];
    }


}
