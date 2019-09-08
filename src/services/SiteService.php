<?php


namespace app\services;


use app\models\Change;
use app\models\Nominal;
use app\models\Product;
use app\models\TypeWallet;
use app\models\Wallet;
use Exception;
use Yii;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;

class SiteService
{
    /**
     * Получить данные
     * @return array
     */
    public function getData()
    {
        return [
            'userWallets' => $this->getWalletsByType(TypeWallet::USER_TYPE),
            'machineWallets' => $this->getWalletsByType(TypeWallet::MACHINE_TYPE),
            'deposit' => $this->getWalletsSumByType(TypeWallet::DEPOSIT_TYPE),
            'products' => Product::find()->orderBy('id ASC')->all(),
        ];
    }

    /**
     * Получить кошельки по типу
     * @param int $type
     * @return array
     */
    public function getWalletsByType(int $type)
    {
        return Wallet::find()
            ->where(['type_id' => $type])
            ->orderBy('id ASC')
            ->all();
    }

    /**
     * Получить сумму в кошельках по типу
     * @param int $type
     * @return float
     */
    public function getWalletsSumByType(int $type): float
    {
        return Wallet::find()
            ->select(['SUM(nominal.value * wallet.count) as total'])
            ->innerJoin('nominal', 'wallet.nominal_id = nominal.id')
            ->where(['type_id' => $type])
            ->orderBy('total DESC')
            ->asArray()
            ->one()['total'];
    }

    /**
     * Добавить монетку на депозитный счет
     * @param Nominal $nominal - номинал монетки
     */
    public function addDeposit(Nominal $nominal)
    {
        $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
        try {
            $depositWallet = Wallet::find()
                ->where([
                    'type_id' => TypeWallet::DEPOSIT_TYPE,
                    'nominal_id' => $nominal->id
                ])
                ->one();
            $userWallet = Wallet::find()
                ->where([
                    'type_id' => TypeWallet::USER_TYPE,
                    'nominal_id' => $nominal->id
                ])
                ->one();
            $userWallet->updateCounters(['count' => -1]);
            $userWallet->validate();
            $depositWallet->updateCounters(['count' => 1]);
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
    }

    /**
     * Вернуть сдачу с депозитного счета пользователю
     */
    public function makeChange()
    {
        $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
        try {
            $machineWallets = $this->getWalletsByType(TypeWallet::MACHINE_TYPE);
            //зачисляем с депозитного счета в машинный
            list($nominalArray, $counts, $sum) = $this->sentToMachineWallets($machineWallets);
            //вычисление сдачи
            $findWallets = $this->getChange($nominalArray, $counts, $sum);
            //с машинного на пользовательский счет
            $this->sendToWallets($machineWallets, $findWallets, TypeWallet::USER_TYPE);
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
    }

    /**
     * @param Product $product
     */
    public function buyProduct(Product $product)
    {
        $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
        try {
            $product->updateCounters(['count' => -1]);
            $product->validate();
            if ($product->price > $this->getWalletsSumByType(TypeWallet::DEPOSIT_TYPE)) {
                throw new BadRequestHttpException('Недостаточно средств');
            }
            $machineWallets = $this->getWalletsByType(TypeWallet::MACHINE_TYPE);
            //зачисляем с депозитного счета в машинный
            list($nominalArray, $counts, $sum) = $this->sentToMachineWallets($machineWallets);
            //вычисление сдачи
            $findWallets = $this->getChange($nominalArray, $counts, $sum - $product->price);
            //с машинного на депозитный счет
            $this->sendToWallets($machineWallets, $findWallets, TypeWallet::DEPOSIT_TYPE);
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Спасибо!');
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
    }

    /**
     * Перевод с одного кошелька на другой
     * @param Wallet $from - кошелек плательщика
     * @param Wallet $to - кошелек получателя
     * @param int $count -  количество монеток
     * @throws BadRequestHttpException
     */
    private function send(Wallet $from, Wallet $to, int $count) {
        if ($from->nominal_id !== $to->nominal_id || $count <= 0) {
            throw new BadRequestHttpException('Invalid data!');
        }
        $from->updateCounters(['count' => -1 * $count]);
        $from->validate();
        $to->updateCounters(['count' => $count]);
    }

    /**
     * Отправить все монеты с депозитного на машинный счет
     * @param array $machineWallets - машинные счета
     * @return array [$nominalArray, $counts, $sum] - номиналы, количество монет на машиннома счету, общая сумма
     * @throws BadRequestHttpException
     */
    public function sentToMachineWallets(array $machineWallets): array
    {
        $sum = 0;
        $counts = [];
        $nominalArray = [];
        foreach ($machineWallets as $machineWallet) {
            /**
             * @var $machineWallet Wallet - машинный кошелек
             * @var $depositWallet Wallet - депозитный кошелек
             */
            $depositWallet = Wallet::find()
                ->where([
                    'type_id' => TypeWallet::DEPOSIT_TYPE,
                    'nominal_id' => $machineWallet->nominal_id
                ])
                ->one();
            $nominalArray[$machineWallet->nominal_id] = $machineWallet->nominal->value;
            $counts[$machineWallet->nominal_id] = $machineWallet->count;
            if ($depositWallet->count > 0) {
                $sum += $depositWallet->count * $machineWallet->nominal->value;
                $this->send($depositWallet, $machineWallet, $depositWallet->count);
            }
        }
        return array($nominalArray, $counts, $sum);
    }

    /**
     * Получить по номиналу количестиво монет для сдачи
     * @param $nominalArray - массив номиналов
     * @param $counts - доступное количество монет
     * @param $sum - сумма сдачи
     * @return array
     */
    public function getChange($nominalArray, $counts, $sum): array
    {
        $change = new Change($nominalArray, $counts);
        $findWallets = $change->greedyAlgorithm($sum);
        return $findWallets;
    }

    /**
     * Отправить с машинного счета на другой тип
     * @param array $machineWallets - машинные счета
     * @param array $findWallets - солько отправлять
     * @param int $type - на какой тип отправлять
     * @throws BadRequestHttpException
     */
    public function sendToWallets(array $machineWallets, array $findWallets, int $type): void
    {
        foreach ($machineWallets as $machineWallet) {
            $toWallet = Wallet::find()
                ->where([
                    'type_id' => $type,
                    'nominal_id' => $machineWallet->nominal_id
                ])
                ->one();
            /**
             * @var $toWallet Wallet - на какой кошелек отправить
             */
            if ($findWallets[$machineWallet->nominal_id] > 0) {
                $this->send($machineWallet, $toWallet, $findWallets[$machineWallet->nominal_id]);
            }
        }
    }
}