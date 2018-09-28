<?php

use Tokenly\CryptoQuantity\EthereumCryptoQuantity;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* 
*/
class EthereumCryptoQuantityTest extends \PHPUnit_Framework_TestCase
{

    const SATOSHI = 1000000000000000000;

    public function testEthereumFromSatoshis() {
        $q1 = EthereumCryptoQuantity::fromSatoshis(123450000000000);
        PHPUnit::assertEquals('123450000000000', $q1->getSatoshisString());
        PHPUnit::assertEquals(0.00012345, $q1->getFloatValue());
    }

    public function testEthereumFromFloat() {
        $q1 = EthereumCryptoQuantity::fromFloat(2.3);
        PHPUnit::assertEquals(round(2.3 * self::SATOSHI), $q1->getSatoshisString());
        PHPUnit::assertEquals(2.3, $q1->getFloatValue());

        $q2 = EthereumCryptoQuantity::fromFloat(220000000.3);
        PHPUnit::assertEquals(220000000.3, $q2->getFloatValue());

        $q3 = EthereumCryptoQuantity::fromFloat(0.00000001);
        PHPUnit::assertEquals(0.00000001, $q3->getFloatValue());

        $q4 = EthereumCryptoQuantity::fromFloat(0.0000000000000000001);
        PHPUnit::assertEquals(0, $q4->getFloatValue());
    }

    public function testEthereumFromBigInteger() {
        $big_int = new Math_BigInteger('200000000000000000000');
        $q1 = EthereumCryptoQuantity::fromBigIntegerSatoshis($big_int);
        PHPUnit::assertEquals(round(200 * self::SATOSHI), $q1->getSatoshisString());
        PHPUnit::assertEquals(200, $q1->getFloatValue());
    }

    public function testEthereumLargeNumber() {
        // very large number
        $q1 = EthereumCryptoQuantity::fromFloat(10000000000000);
        PHPUnit::assertEquals('10000000000000', $q1->getFloatValue());
    }

    public function testEthereumDivisible() {
        $q1 = EthereumCryptoQuantity::fromFloat(2.3);
        PHPUnit::assertEquals(round(2.3 * self::SATOSHI), $q1->getSatoshisString());
    }

    public function testEthereumPassthroughCall() {
        $q1 = EthereumCryptoQuantity::fromFloat(2.3);
        $q2 = $q1->multiply(new Math_BigInteger(10));
        PHPUnit::assertEquals(round(23 * self::SATOSHI), $q2->getSatoshisString());
    }

    public function testEthereumConvenienceMethods()
    {
        PHPUnit::assertEquals('1000000000000000000', EthereumCryptoQuantity::floatToSatoshis(1));
    }

}
