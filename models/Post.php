<?php

namespace app\models;

use yii\db\ActiveRecord;



class Post extends ActiveRecord {

    public $imageFile;

    public static function tableName()
    {
        return 'posts';
    }

    public function getPostByTitle($title) {
        $query = \Yii::$app->db->createCommand('SELECT * FROM posts WHERE title=:title');
        $post1 = $query->bindValues(['title' => $title]);
        return $post1->queryOne();
    }

    public function create($data) {
        $query = \Yii::$app->db->createCommand()->insert('posts', $data)->execute();
        return $query;
    }


}