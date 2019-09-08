<?php

namespace app\models;


use yii\db\ActiveQuery;

/**
 * This is the model class for table "type_wallet".
 *
 * @property int $id
 * @property string $name
 *
 * @property Wallet[] $wallets
 */
class TypeWallet extends BaseModel
{
    const USER_TYPE = 1;
    const MACHINE_TYPE = 2;
    const DEPOSIT_TYPE = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'type_wallet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getWallets()
    {
        return $this->hasMany(Wallet::class, ['type_id' => 'id']);
    }
}
