<?php

namespace app\models;

use yii\db\ActiveRecord;



class Comment extends ActiveRecord {

    public $imageFile;


    public static function tableName()
    {
        return 'comments';
    }

    public function getCommentsByPostId($post_id) {
        $query = \Yii::$app->db->createCommand('SELECT * FROM comments WHERE post_id=:post_id');
        $post1 = $query->bindValues(['post_id' => $post_id]);
        return $post1->queryAll();
    }

    public function getCommentById($comment_id) {
        $query = \Yii::$app->db->createCommand('SELECT * FROM comments WHERE id=:id');
        $post1 = $query->bindValues(['id' => $comment_id]);
        return $post1->queryOne();
    }

    public function create($data) {
        $data['created_at'] = time();
        $query = \Yii::$app->db->createCommand()->insert('comments', $data)->execute();
        return $query;
    }

    public function deleteComments($post_id) {
        $query = \Yii::$app->db->createCommand()->delete('comments', 'post_id = :post_id', [
            'post_id' => $post_id,
        ])->execute();
        return $query;
    }

    public function deleteComment($comment_id) {
        $query = \Yii::$app->db->createCommand()->delete('comments', 'id = :comment_id', [
            'comment_id' => $comment_id,
        ])->execute();
        return $query;
    }


}