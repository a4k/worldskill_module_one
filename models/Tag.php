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

    public function getTagsByTagName($tag_name) {
        $query = \Yii::$app->db->createCommand('SELECT * FROM tags WHERE title=:tag_name');
        $post1 = $query->bindValues(['tag_name' => $tag_name]);
        return $post1->queryAll();
    }

    public function create($data) {
        $data['created_at'] = time();
        $query = \Yii::$app->db->createCommand()->insert('tags', $data)->execute();
        return $query;
    }

    public function deleteTags($post_id) {
        $query = \Yii::$app->db->createCommand()->delete('tags', 'post_id = :post_id', [
            'post_id' => $post_id,
        ])->execute();
        return $query;
    }


}