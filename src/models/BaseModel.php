<?php


namespace app\models;


use yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;

class BaseModel extends ActiveRecord
{
    /**
     * Ошибка после валидации
     * @throws BadRequestHttpException
     */
    public function afterValidate()
    {
        parent::afterValidate();
        $this->checkErrors();
    }

    /**
     * Метод выбросит исключение с текстом первой ошибки валидации.
     * При сохранении модели нет необходимости проверять на наличие ошибок.
     * @throws BadRequestHttpException
     */
    private function checkErrors()
    {
        $errors = $this->getFirstErrors();
        if (!empty($errors)) {
            throw new BadRequestHttpException(array_shift($errors));
        }
    }
}