<?php


namespace app\models;


class Change
{
    public $nominalArray;
    public $countsCoin;
    protected $currentCountsArray;

    public function __construct(array $nominalArray, array $countsCoin)
    {
        $this->setNominalArray($nominalArray);
        $this->setCountsCoin($countsCoin);
        $this->setCurrentCountsArray($this->getDefaultResult());
    }

    /**
     * @return array
     */
    public function getNominalArray(): array
    {
        return $this->nominalArray;
    }

    /**
     * @param array $nominalArray
     */
    public function setNominalArray(array $nominalArray): void
    {
        $this->nominalArray = $nominalArray;
    }

    /**
     * @return array
     */
    public function getCountsCoin(): array
    {
        return $this->countsCoin;
    }

    /**
     * @param array $countsCoin
     */
    public function setCountsCoin(array $countsCoin): void
    {
        $this->countsCoin = $countsCoin;
    }

    /**
     * @return array
     */
    public function getCurrentCountsArray()
    {
        return $this->currentCountsArray;
    }

    /**
     * @param array $currentCountsArray
     */
    protected function setCurrentCountsArray(array $currentCountsArray): void
    {
        $this->currentCountsArray = $currentCountsArray;
    }

    /**
     * Рекурсивный алгоритм
     * @param int $sum - сумма вычисления
     * @return array - [$keyNominal => $countCoin,...] количество монет на каждый номинал
     */
    public function recursionAlgorithm(int $sum) {
        if ($sum === 0) {
            return $this->getCurrentCountsArray();
        }
        $min[] = PHP_INT_MAX;
        foreach($this->getNominalArray() as $nominalKey => $nominalValue) {
            if ($sum - $nominalValue >= 0 && $this->countsCoin[$nominalKey] > 0) {
                $this->countsCoin[$nominalKey]--;
                $this->currentCountsArray[$nominalKey]++;
                $result = $this->recursionAlgorithm($sum - $nominalValue);
                $this->countsCoin[$nominalKey]++;
                $this->currentCountsArray[$nominalKey]--;
                if (array_sum($result) < array_sum($min)) {
                    $min = $result;
                }
            }
        }
        return $min;
    }

    /**
     * Жадный алгоритм
     * @param int $sum - сумма вычисления
     * @return array - [$keyNominal => $countCoin,...] количество монет на каждый номинал
     */
    public function greedyAlgorithm(int $sum)
    {
        $result = $this->getDefaultResult();
        $currentSum = 0;
        $nominalSort = $this->getNominalArray();
        $counts = $this->getCountsCoin();
        arsort($nominalSort);
        foreach ($nominalSort as $key => $value) {
            while ($currentSum < $sum) {
                if (($currentSum + $value) <= $sum && $counts[$key] > 0) {
                    $currentSum += $value;
                    $result[$key]++;
                    $counts[$key]--;
                } else {
                    break;
                }
            }
        }

        return $result;

    }

    /**
     * Получить ответ по умолчанию
     * @return array - [$keyNominal => $countCoin,...] количество монет 0 на каждый номинал
     */
    public function getDefaultResult(): array
    {
        $result = [];
        foreach ($this->getNominalArray() as $key => $value) {
            $result[$key] = 0;
        }
        return $result;
    }

}
