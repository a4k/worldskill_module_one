<?php

namespace app\controllers;

use app\models\Post;
use app\models\Tag;
use app\models\Comment;
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
        $isActiveImage = true;

        $imageLink = "/api/post_images/none.jpg";
        if (\Yii::$app->request->isPost && $isActiveImage) {
            $model->image = UploadedFile::getInstance($model, 'image');
            if (!$model->upload()) {
                $err = [
                    'title' => 'image error',
                    'message' => 'invalid file format',
                ];
                return static::errMessage($err);
            }
            $imageLink = $model->image->tempName;
        }


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

    public function actionEdit($post_id) {

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
            return static::errEditMessage($err);
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
            return static::errEditMessage($err);
        }

        $result = $post->getPostByTitle($title);
        if($result) {
            $err = [
                'title' => 'already exist',
                'message' => 'post is exist with this title',
            ];
            return static::errEditMessage($err);
        }

        $model = new UploadForm();
        $isActiveImage = false;

        $imageLink = "/api/post_images/none.jpg";
        if (\Yii::$app->request->isPost && $isActiveImage) {
            $model->image = UploadedFile::getInstance($model, 'image');
            if (!$model->upload()) {
                $err = [
                    'title' => 'image error',
                    'message' => 'invalid file format',
                ];
                return static::errEditMessage($err);
            }
            $imageLink = $model->image->tempName;
        }


        $postData = [
            'title' => $title,
            'anons' => $anons,
            'text' => $text,
            'image' => $imageLink,
        ];

        $query = $post->edit($post_id, $postData);

        if(!$query) {
            $err = [
                'title' => 'error create',
                'message' => 'create error post',
            ];
            return static::errEditMessage($err);
        }

        self::deleteTags($post_id);

        self::createTags($post_id, $tags);

        $tag = new Tag();

        $newPostData = $post->getPostById($post_id);

        $newPostData['tags'] = self::getTags($post_id);

        $result_json = [
            'status' => true,
            'post' => $newPostData,
        ];

        return static::successEditMessage($result_json);
    }

    public function actionDelete($post_id) {

        self::setJson();

        $post = new Post();


        if(!$post->getPostById($post_id)) {
            return static::errNotFoundMessage();
        }


        $query = $post->deletePost($post_id);

        if(!$query) {
            return static::errDeleteMessage('error deleting');
        }

        self::deleteTags($post_id);

        $result_json = [
            'status' => true,
        ];

        return static::successDeleteMessage($result_json);
    }

    public function actionPosts() {
        self::setJson();

        $post = new Post();


        $posts = $post->getPosts();
        $postsData = [];
        foreach ($posts as $postItem) {

            $timestamp = $postItem['created_at'];
            $datetime = date('H:i d.m.Y', $timestamp);
            $postItem["datetime"] = $datetime;

            $postItem['tags'] = self::getTags($postItem['id']);

            unset($postItem['id']);

            array_push($postsData, $postItem);


        }

        $result_json = $postsData;

        return static::successListMessage($result_json);
    }

    public function actionGet($post_id) {

        self::setJson();

        $post = new Post();


        $postItem = $post->getPostById($post_id);

        if(!$postItem) {
            return static::errNotFoundMessage();
        }

        $id = $postItem['id'];

        $timestamp = $postItem['created_at'];
        $datetime = date('H:i d.m.Y', $timestamp);
        $postItem["datetime"] = $datetime;

        $postItem['tags'] = self::getTags($id);

        $postItem['comments'] = self::getComments($id);

        unset($postItem['id']);


        $result_json = $postItem;

        return static::successViewMessage($result_json);
    }

    public function actionTag($tag_name) {
        self::setJson();

        $tag = new Tag();
        $post = new Post();


        $tags = $tag->getTagsByTagName($tag_name);
        $postsId = [];

        foreach ($tags as $tagItem) {

            $postsId[$tagItem['post_id']] = true;

        }

        $postsData = [];

        foreach($postsId as $key => $value) {
            $postItem = $post->getPostById($key);

            $datetime = date('H:i d.m.Y', $postItem['created_at']);
            $postItem["datetime"] = $datetime;

            $postItem['tags'] = self::getTags($postItem['id']);

            unset($postItem['id']);

            array_push($postsData, $postItem);
        }

        $result_json = $postsData;

        return static::successTagMessage($result_json);
    }


    public function getTags($post_id) {

        if(!isset($post_id)) return [];

        $tag = new Tag();

        $newTagData = $tag->getTagsByPostId($post_id);
        $tagsData = [];
        foreach($newTagData as $tagData) {
            array_push($tagsData, $tagData['title']);
        }
        return $tagsData;
    }

    public function createTags($post_id, $tags) {

        if(!isset($post_id)) return;
        if(!isset($tags)) return;


        $tag = new Tag();

        $arTags = explode(',', $tags);

        foreach ($arTags as $tag_title) {
            $postData = [
                'title' => $tag_title,
                'post_id' => $post_id,
            ];
            $tag->create($postData);

        }
    }

    public function deleteTags($post_id) {

        if(!isset($post_id)) return;

        $tag = new Tag();

        $tag->deleteTags($post_id);
    }




    public function getComments($post_id) {

        if(!isset($post_id)) return [];

        $comment = new Comment();

        $newCommentData = $comment->getCommentsByPostId($post_id);
        $data = [];

        foreach($newCommentData as $item) {

            $model = [];

            $model['comment_id'] = $item['id'];
            $model['author'] = $item['author'];
            $model['comment'] = $item['comment'];

            if($item['user_id']) $model['author'] = 'admin';


            $datetime = date('H:i d.m.Y', $item['created_at']);
            $model["datetime"] = $datetime;

            array_push($data, $model);
        }
        return $data;
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

    public function successEditMessage($result_json) {
        \Yii::$app->response->statusCode = 201;
        \Yii::$app->response->statusText = "Successful creation";
        return $result_json;
    }

    public function successListMessage($result_json) {
        \Yii::$app->response->statusCode = 200;
        \Yii::$app->response->statusText = "List posts";
       return $result_json;
    }

    public function successTagMessage($result_json) {
        \Yii::$app->response->statusCode = 200;
        \Yii::$app->response->statusText = "Found posts";
        return $result_json;
    }

    public function successViewMessage($result_json) {
        \Yii::$app->response->statusCode = 200;
        \Yii::$app->response->statusText = "View post";
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

    public function errEditMessage($msg) {
        \Yii::$app->response->statusCode = 400;
        \Yii::$app->response->statusText = "Editing error";
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


}
