<?php

namespace app\models;


/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property string $title
 * @property double $price
 * @property int $count
 */
class Product extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'price', 'count'], 'required'],
            [['price'], 'number'],
            [['count'], 'default', 'value' => null],
            [['count'], 'integer', 'min' => 0, 'tooSmall' => 'Нету этого продукта!'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'price' => 'Price',
            'count' => 'Count',
        ];
    }
}
