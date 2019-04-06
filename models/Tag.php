<?php

namespace app\models;

use yii\db\ActiveRecord;



class Tag extends ActiveRecord {

    public $imageFile;


    public static function tableName()
    {
        return 'tags';
    }

    public function getTagsByPostId($post_id) {
        $query = \Yii::$app->db->createCommand('SELECT * FROM tags WHERE post_id=:post_id');
        $post1 = $query->bindValues(['post_id' => $post_id]);
        return $post1->queryAll();
    }

    public function create($data) {
        $query = \Yii::$app->db->createCommand()->insert('tags', $data)->execute();
        return $query;
    }


}