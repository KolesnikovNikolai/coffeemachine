<?php

use yii\db\Migration;

/**
 * Class m190904_110958_add_tabels
 */
class m190904_110958_add_tabels extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //Продукты
        $this->createTable('product', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'price' => $this->double()->notNull(),
            'count' => $this->integer()->notNull(),
        ]);
        $this->batchInsert(
            'product',
            ['title', 'price', 'count'],
            [
                ['Чай', 13, 10],
                ['Кофе', 18, 20],
                ['Кофе с молоком', 21, 20],
                ['Сок', 35, 15],
            ]
        );
        //Номинал
        $this->createTable('nominal', [
            'id' => $this->primaryKey(),
            'value' => $this->double()->notNull(),
        ]);
        $this->batchInsert(
            'nominal',
            ['id', 'value'],
            [
                [1, 1],
                [2, 2],
                [3, 5],
                [4, 10],
            ]
        );
        //Тип кошелька
        $this->createTable('type_wallet', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
        ]);
        $this->batchInsert(
            'type_wallet',
            ['id', 'name'],
            [
                [1, 'Пользовательский'],
                [2, 'Технический'],
                [3, 'Депозит'],
            ]
        );
        //Кошельки
        $this->createTable('wallet', [
            'id' => $this->primaryKey(),
            'nominal_id' => $this->integer()->notNull(),
            'type_id' => $this->integer()->notNull(),
            'count' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            'fk-wallet-nominal_id',
            'wallet',
            'nominal_id',
            'nominal',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-wallet-type_id',
            'wallet',
            'type_id',
            'type_wallet',
            'id',
            'CASCADE'
        );
        $this->batchInsert(
            'wallet',
            ['type_id', 'nominal_id', 'count'],
            [
                [1, 1, 10],
                [1, 2, 30],
                [1, 3, 20],
                [1, 4, 15],
                [2, 1, 100],
                [2, 2, 100],
                [2, 3, 100],
                [2, 4, 100],
                [3, 1, 0],
                [3, 2, 0],
                [3, 3, 0],
                [3, 4, 0],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-wallet-type_id', 'wallet');
        $this->dropForeignKey('fk-wallet-nominal_id', 'wallet');
        $this->dropTable('wallet');
        $this->dropTable('type_wallet');
        $this->dropTable('nominal');
        $this->dropTable('product');
    }
}
