<?php

use Tokenly\CryptoQuantity\CryptoQuantity;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* 
*/
class CryptoQuantityTest extends \PHPUnit_Framework_TestCase
{

    const SATOSHI = 100000000;

    public function testFromSatoshis() {
        $q1 = CryptoQuantity::fromSatoshis(12345);
        PHPUnit::assertEquals(12345, $q1->getSatoshisString());
        PHPUnit::assertEquals(0.00012345, $q1->getFloatValue());
    }

    public function testFromFloat() {
        $q1 = CryptoQuantity::fromFloat(2.3);
        PHPUnit::assertEquals(round(2.3 * self::SATOSHI), $q1->getSatoshisString());
        PHPUnit::assertEquals(2.3, $q1->getFloatValue());

        $q2 = CryptoQuantity::fromFloat(220000000.3);
        PHPUnit::assertEquals(220000000.3, $q2->getFloatValue());

        $q3 = CryptoQuantity::fromFloat(0.00000001);
        PHPUnit::assertEquals(0.00000001, $q3->getFloatValue());

        $q4 = CryptoQuantity::fromFloat(0.000000001);
        PHPUnit::assertEquals(0, $q4->getFloatValue());
    }

    public function testFromBigInteger() {
        $q1 = CryptoQuantity::fromBigIntegerSatoshis(new Math_BigInteger(100 * self::SATOSHI));
        PHPUnit::assertEquals(round(100 * self::SATOSHI), $q1->getSatoshisString());
        PHPUnit::assertEquals(100, $q1->getFloatValue());
    }


    public function testPassthroughCall() {
        $q1 = CryptoQuantity::fromFloat(2.3);
        $q2 = $q1->multiply(new Math_BigInteger(10));
        PHPUnit::assertEquals(round(23 * self::SATOSHI), $q2->getSatoshisString());
    }

    public function testConvenienceMethods() {
        PHPUnit::assertEquals('100', CryptoQuantity::satoshisToValue(10000000000));
        PHPUnit::assertEquals('100', CryptoQuantity::satoshisToValue(new Math_BigInteger('10000000000')));
        PHPUnit::assertEquals('100', CryptoQuantity::satoshisToValue(CryptoQuantity::fromSatoshis('10000000000')));
        PHPUnit::assertEquals('10000000000', CryptoQuantity::valueToSatoshis(100));

        PHPUnit::assertTrue(CryptoQuantity::fromSatoshis(1)->gt(CryptoQuantity::fromSatoshis(0)));
        PHPUnit::assertTrue(CryptoQuantity::fromSatoshis(1)->gt(0));
        PHPUnit::assertFalse(CryptoQuantity::fromSatoshis(1)->gt(CryptoQuantity::fromSatoshis(5)));
        PHPUnit::assertFalse(CryptoQuantity::fromSatoshis(1)->gt(5));

        PHPUnit::assertTrue(CryptoQuantity::fromSatoshis(1)->gte(CryptoQuantity::fromSatoshis(0)));
        PHPUnit::assertTrue(CryptoQuantity::fromSatoshis(1)->gte(0));
        PHPUnit::assertTrue(CryptoQuantity::fromSatoshis(1)->gte(1));

        PHPUnit::assertTrue(CryptoQuantity::fromSatoshis(1)->lt(2));
        PHPUnit::assertTrue(CryptoQuantity::fromSatoshis(1)->lte(1));

        PHPUnit::assertTrue(CryptoQuantity::fromSatoshis(0)->isZero());
        PHPUnit::assertFalse(CryptoQuantity::fromSatoshis(1)->isZero());
    }

}
