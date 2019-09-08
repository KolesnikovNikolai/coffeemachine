<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "nominal".
 *
 * @property int $id
 * @property double $value
 *
 * @property Wallet[] $wallets
 */
class Nominal extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nominal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value'], 'required'],
            [['value'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'value' => 'Value',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getWallets()
    {
        return $this->hasMany(Wallet::class, ['nominal_id' => 'id']);
    }
}
