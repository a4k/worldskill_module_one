<?php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $image;

    public function rules()
    {
        return [
            [['image'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxSize' => 2097152],
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            $link = 'api/post_images/' . $this->image->baseName . '.' . $this->image->extension;
            $this->image->saveAs($link);
            return true;
        } else {
            return false;
        }
    }
}