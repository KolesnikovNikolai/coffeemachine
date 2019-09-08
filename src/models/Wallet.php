<?php

namespace app\models;


use yii\db\ActiveQuery;

/**
 * This is the model class for table "wallet".
 *
 * @property int $id
 * @property int $nominal_id
 * @property int $type_id
 * @property int $count
 *
 * @property Nominal $nominal
 * @property TypeWallet $type
 */
class Wallet extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wallet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nominal_id', 'type_id', 'count'], 'required'],
            [['nominal_id', 'type_id', 'count'], 'default', 'value' => null],
            [['nominal_id', 'type_id'], 'integer'],
            ['count', 'integer', 'min' => 0, 'tooSmall' => 'Нету монет!'],
            [['nominal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Nominal::class, 'targetAttribute' => ['nominal_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => TypeWallet::class, 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nominal_id' => 'Nominal ID',
            'type_id' => 'Type ID',
            'count' => 'Count',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getNominal()
    {
        return $this->hasOne(Nominal::class, ['id' => 'nominal_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(TypeWallet::class, ['id' => 'type_id']);
    }
}
