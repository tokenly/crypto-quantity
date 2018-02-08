<?php

use Tokenly\CryptoQuantity\CryptoQuantity;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* 
*/
class SerializationTest extends \PHPUnit_Framework_TestCase
{

    const SATOSHI = 1000000;

    public function testSerializeAndUnserialize() {
        $q1 = CryptoQuantity::fromSatoshis(12345);
        $encoded = json_encode($q1);

        $decoded = json_decode($encoded, true);
        $q2 = CryptoQuantity::unserialize($decoded);

        PHPUnit::assertEquals($q1->getSatoshisString(), $q2->getSatoshisString());
        PHPUnit::assertEquals($q1->getFloatValue(), $q2->getFloatValue());
    }

    public function testSerializeAndUnserializePrecision() {
        $q1 = CryptoQuantity::fromSatoshis(1234567, 6);
        PHPUnit::assertEquals(1.234567, $q1->getFloatValue());
        PHPUnit::assertEquals('1234567', $q1->getSatoshisString());
        $encoded = json_encode($q1);

        $decoded = json_decode($encoded, true);
        $q2 = CryptoQuantity::unserialize($decoded);

        PHPUnit::assertEquals($q1->getSatoshisString(), $q2->getSatoshisString());
        PHPUnit::assertEquals($q1->getFloatValue(), $q2->getFloatValue());
    }


}