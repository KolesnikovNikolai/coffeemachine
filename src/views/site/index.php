<?php

/* @var $this yii\web\View */
/* @var $userWallets app\models\Wallet[]  */
/* @var $machineWallets app\models\Wallet[]  */
/* @var $products app\models\Product[]  */
/* @var $deposit double */
/* @var $message string */

$this->title = 'My Yii Application';
?>
<div class="site-index">
    <div class="body-content">
        <div class="row">
            <div class="col-lg-4">
                <h2>Кошелек пользователя</h2>

                <?php
                    foreach ($userWallets as $wallet) {
                        echo \yii\helpers\Html::a(
                                $wallet->nominal->value . ' руб = ' . $wallet->count . ' шт',
                                ['/site/add-deposit', 'id' => $wallet->nominal->id],
                                ['class'=>'btn btn-primary']
                        ) . '<br><br/>';
                    }
                ?>

                <?= '<br><p>Внесенная сумма = ' . $deposit . ' руб</p>'?>
                <?= \yii\helpers\Html::a(
                    'Вернуть',
                    ['/site/change', 'id' => $wallet->nominal->id],
                    ['class'=>'btn btn-primary']
                )
                ?>
            </div>
            <div class="col-lg-4">
                <h2>Товары</h2>

                <?php
                foreach ($products as $product) {
                    echo \yii\helpers\Html::a(
                            $product->title . ' = '
                            . $product->price . ', '
                            . $product->count . ' шт',
                            ['/site/buy', 'id' => $product->id],
                            ['class'=>'btn btn-primary']
                        ) . '<br><br/>';
                }
                ?>
            </div>
            <div class="col-lg-4">
                <h2>Кошелек Кофе машины</h2>

                <?php
                foreach ($machineWallets as $wallet) {
                    echo '<p>'
                        . $wallet->nominal->value . ' руб = ' . $wallet->count . ' шт'
                        . '</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
