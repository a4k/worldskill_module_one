<?php
use yii\widgets\ActiveForm;
?>
    <form id="w0" action="/post/create" method="post" enctype="multipart/form-data">

        <input type="text" name="title" value="test">
        <input type="text" name="anons" value="anons">
        <input type="text" name="text" value="text">
        <input type="text" name="tags" value="tag1, tag2">
        <input type="file" name="image">
        <button>Submit</button>

    </form>
