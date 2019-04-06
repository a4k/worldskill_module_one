<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;



class Post extends ActiveRecord {

    public $imageFile;

    public static function tableName()
    {
        return 'posts';
    }

    public function getPosts() {
        $query = \Yii::$app->db->createCommand('SELECT * FROM posts');
        return $query->queryAll();
    }

    public function getPostByTitle($title) {
        $query = \Yii::$app->db->createCommand('SELECT * FROM posts WHERE title=:title');
        $post1 = $query->bindValues(['title' => $title]);
        return $post1->queryOne();
    }

    public function getPostById($id) {
        $query = \Yii::$app->db->createCommand('SELECT * FROM posts WHERE id=:id');
        $post1 = $query->bindValues(['id' => $id]);
        return $post1->queryOne();
    }

    public function create($data) {
        $data['created_at'] = time();
        $data['updated_at'] = time();
        $query = \Yii::$app->db->createCommand()->insert('posts', $data)->execute();
        return $query;
    }

    public function edit($post_id, $data) {
        $data['updated_at'] = time();
        $query = \Yii::$app->db->createCommand()->update('posts', $data, 'id = :id', [
            'id' => $post_id,
        ])->execute();
        return $query;
    }

    public function deletePost($post_id) {
        $query = \Yii::$app->db->createCommand()->delete('posts','id = :id', [
            'id' => $post_id,
        ])->execute();
        return $query;
    }


}