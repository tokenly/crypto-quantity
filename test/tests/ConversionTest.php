<?php

use Tokenly\CryptoQuantity\CryptoQuantity;
use Tokenly\CryptoQuantity\EthereumCryptoQuantity;
use \PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\TestCase;
/*
* 
*/
class ConversionTest extends \TestCase
{

    public function testConvertToEthereumQuantity() {
        $q1 = CryptoQuantity::fromFloat(0.12345);
        PHPUnit::assertEquals('12345000', $q1->getSatoshisString());
        PHPUnit::assertEquals(0.12345, $q1->getFloatValue());

        $q2 = EthereumCryptoQuantity::fromCryptoQuantity($q1);
        PHPUnit::assertEquals('123450000000000000', $q2->getSatoshisString());
        PHPUnit::assertEquals(0.12345, $q2->getFloatValue());
        PHPUnit::assertEquals(18, $q2->getPrecision());
    }

    public function testConvertToBitcoinQuantity() {
        $q1 = EthereumCryptoQuantity::fromFloat(0.12345);
        PHPUnit::assertEquals('123450000000000000', $q1->getSatoshisString());
        PHPUnit::assertEquals(0.12345, $q1->getFloatValue());

        $q2 = CryptoQuantity::fromCryptoQuantity($q1);
        PHPUnit::assertEquals('12345000', $q2->getSatoshisString());
        PHPUnit::assertEquals(0.12345, $q2->getFloatValue());
        PHPUnit::assertEquals(8, $q2->getPrecision());
    }

    public function testConvertAndRoundToBitcoinQuantity() {
        $q1 = EthereumCryptoQuantity::fromSatoshis('123459995000000000');
        PHPUnit::assertEquals('123459995000000000', $q1->getSatoshisString());
        PHPUnit::assertEquals(0.123459995, $q1->getFloatValue());

        $q2 = CryptoQuantity::fromCryptoQuantity($q1);
        PHPUnit::assertEquals('12346000', $q2->getSatoshisString());
        PHPUnit::assertEquals(0.12346, $q2->getFloatValue());


        $q1 = EthereumCryptoQuantity::fromSatoshis('123459994000000000');
        PHPUnit::assertEquals('123459994000000000', $q1->getSatoshisString());
        PHPUnit::assertEquals(0.123459994, $q1->getFloatValue());

        $q2 = CryptoQuantity::fromCryptoQuantity($q1);
        PHPUnit::assertEquals('12345999', $q2->getSatoshisString());
        PHPUnit::assertEquals(0.12345999, $q2->getFloatValue());
    }

    public function testConvertUnchangedQuantity() {
        $q1 = CryptoQuantity::fromFloat(0.12345);
        PHPUnit::assertEquals('12345000', $q1->getSatoshisString());
        PHPUnit::assertEquals(0.12345, $q1->getFloatValue());

        $q2 = CryptoQuantity::fromCryptoQuantity($q1);
        PHPUnit::assertEquals('12345000', $q2->getSatoshisString());
        PHPUnit::assertEquals(0.12345, $q2->getFloatValue());
        PHPUnit::assertEquals(8, $q2->getPrecision());
    }

}
