<?php


namespace tests\unit\models;


use app\models\Change;

class ChangeTest extends \Codeception\Test\Unit
{
    public function testSetCorrectData()
    {
        $change = new Change([100=>1,30=>2,2=>5,1=>10],[0,20,0,0]);
        expect($change->getNominalArray())->equals([100=>1,30=>2,2=>5,1=>10]);
        expect($change->getCountsCoin())->equals([0,20,0,0]);
        expect($change->getDefaultResult())->equals([100=>0,30=>0,2=>0,1=>0]);
        expect($change->getCurrentCountsArray())->equals([100=>0,30=>0,2=>0,1=>0]);
    }

    public function testGreedyAlgorithm()
    {
        $change = new Change([1,2,5,10],[20,20,20,20]);
        expect($change->greedyAlgorithm(0))->equals([0,0,0,0]);
        expect($change->greedyAlgorithm(1))->equals([1,0,0,0]);
        expect($change->greedyAlgorithm(2))->equals([0,1,0,0]);
        expect($change->greedyAlgorithm(5))->equals([0,0,1,0]);
        expect($change->greedyAlgorithm(10))->equals([0,0,0,1]);
        expect($change->greedyAlgorithm(11))->equals([1,0,0,1]);
        expect($change->greedyAlgorithm(12))->equals([0,1,0,1]);
        expect($change->greedyAlgorithm(13))->equals([1,1,0,1]);
        expect($change->greedyAlgorithm(14))->equals([0,2,0,1]);
        expect($change->greedyAlgorithm(15))->equals([0,0,1,1]);
    }

    public function testGreedyAlgorithmLimitCount()
    {
        $change = new Change([1,2,5,10],[1,1,1,1]);
        expect($change->greedyAlgorithm(4))->equals([1,1,0,0]);
    }

    public function testGreedyAlgorithmIncorrectResult()
    {
        $change = new Change([1,3,4],[20,20,20]);
        expect($change->greedyAlgorithm(6))->equals([2,0,1]);
        expect($change->recursionAlgorithm(6))->equals([0,2,0]);
    }
}