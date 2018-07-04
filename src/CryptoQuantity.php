<?php

namespace Tokenly\CryptoQuantity;

use Exception;
use JsonSerializable;
use Math_BigInteger as BigInt;

/*
 * CryptoQuantity
 */
class CryptoQuantity implements JsonSerializable
{

    protected static $PRECISION = 8;

    protected $big_integer;
    protected $precision;

    /**
     * Creates a new quantity from a float value
     * @param  float   $float_value   The amount as a float
     * @param  integer $precision     Number of decimal places of precision
     * @return CryptoQuantity         The new CryptoQuantity object
     */
    public static function fromFloat($float_value, $precision = null)
    {
        if ($precision === null) {
            $precision = static::$PRECISION;
        }

        // left of decimal point
        $rounded_float = intval(round($float_value));
        $big_integer_int = (new BigInt($rounded_float))->multiply(self::precisionUnitsAsBigInt(1, $precision));

        // right of decimal point
        $rounded_decimal = intval(round(floatval($float_value - $rounded_float) * pow(10, $precision)));
        $big_integer_decimal = new BigInt($rounded_decimal);

        // add the integer value and the decimal value
        return new static($big_integer_int->add($big_integer_decimal), $precision);
    }

    /**
     * Creates an asset quantity from an integer number of precisions
     * @param  integer|string $integer The amount in precisions
     * @param  integer $precision     Number of decimal places of precision
     * @return CryptoQuantity   The new CryptoQuantity object
     */
    public static function fromSatoshis($integer, $precision = null)
    {
        if ($precision === null) {
            $precision = static::$PRECISION;
        }
        return new static(new BigInt($integer), $precision);
    }

    /**
     * Creates an asset quantity with a value of 0
     * @param  integer $precision     Number of decimal places of precision
     * @return CryptoQuantity   The new CryptoQuantity object
     */
    public static function zero($precision = null)
    {
        return static::fromSatoshis(0, $precision);
    }

    /**
     * Creates an asset quantity from an integer number of precisions
     * @param  Math_BigInteger $big_integer   The amount as a big integer object
     * @param  integer         $precision     Number of decimal places of precision
     * @return CryptoQuantity   The new CryptoQuantity object
     */
    public static function fromBigIntegerSatoshis(BigInt $big_integer, $precision = null)
    {
        if ($precision === null) {
            $precision = static::$PRECISION;
        }
        return new static($big_integer, $precision);
    }

    /**
     * Creates an asset quantity from another cryptoquantity and adjusts the precision
     * This will round if the new precision is smaller than the previous precision
     * @param  Math_BigInteger $big_integer   The amount as a big integer object
     * @param  integer         $precision     Number of decimal places of precision
     * @return CryptoQuantity   The new CryptoQuantity object
     */
    public static function fromCryptoQuantity(CryptoQuantity $source_quantity, $precision = null)
    {
        if ($precision === null) {
            $precision = static::$PRECISION;
        }

        $round_up = false;
        $source_precision = $source_quantity->getPrecision();
        $precision_delta = $precision - $source_precision;
        if ($precision_delta > 0) {
            // add zeros
            $satoshis_string = $source_quantity->getSatoshisString().str_repeat('0', $precision_delta);
        } else if ($precision_delta < 0) {
            // remove digits and check for rounding
            //   (divide by 10e{$precision_delta})
            $satoshis_string = $source_quantity->getSatoshisString();
            $round_digit = substr($satoshis_string, $precision_delta, 1);
            $satoshis_string = substr($satoshis_string, 0, $precision_delta);
            $round_up = ($round_digit >= 5);
        } else {
            return clone $source_quantity;
        }

        $new_quantity = static::fromSatoshis($satoshis_string, $precision);
        if ($round_up) {
            $new_quantity = $new_quantity->add(new BigInt(1));
        }
        return $new_quantity;
    }


    /**
     * Unserialize a quantity object
     * @param  array $serialized_quantity  Serialized quantity data
     * @return CryptoQuantity The quantity class
     */
    public static function unserialize($serialized_quantity) {
        if (is_array($serialized_quantity)) {
            $json_array = $serialized_quantity;
        } else {
            $json_array = json_decode($serialized_quantity, true);
        }
        if (!is_array($json_array)) {
            throw new Exception("Invalid serialized quantity", 1);
        }

        return static::fromSatoshis($json_array['value'], $json_array['precision']);
    }

    // convenience methods
    public static function floatToSatoshis($float)
    {
        return self::fromFloat($float)->getSatoshisString();
    }
    public static function satoshisToFloat($integer)
    {
        return self::fromSatoshis($integer)->getFloatValue();
    }

    // backwards compatible methods
    public static function satoshisToValue($integer)
    {
        return self::satoshisToFloat($integer);
    }
    public static function valueToSatoshis($float)
    {
        return self::floatToSatoshis($float);
    }

    // ------------------------------------------------------------------------

    /**
     * Gets the number of precisions represented as a string
     * @return string A string representation of an integer
     */
    public function getSatoshisString()
    {
        return $this->big_integer->toString();
    }

    /**
     * Gets the amount as a float
     * @return float A float representation of the value
     */
    public function getFloatValue()
    {
        list($quotient, $remainder) = $this->big_integer->divide(self::precisionUnitsAsBigInt(1, $this->precision));
        return floatval($quotient->toString()) + floatval($remainder->toString() / pow(10, $this->precision));
    }
    
    /**
     * Gets the precision for this quantity
     * @return integer Number of digits of precision
     */
    public function getPrecision()
    {
        return $this->precision;
    }


    /**
     * Returns true if greater than
     * @param  CryptoQuantity|int $other quantity to compare to
     * @return boolean
     */
    public function gt($other)
    {
        if (!($other instanceof self)) {
            $other = self::fromSatoshis($other);
        }
        return ($this->compare($other) > 0);
    }

    /**
     * Returns true if greater than or equal to
     * @param  CryptoQuantity|int $other quantity to compare to
     * @return boolean
     */
    public function gte($other)
    {
        if (!($other instanceof self)) {
            $other = self::fromSatoshis($other);
        }
        return ($this->compare($other) >= 0);
    }

    /**
     * Returns true if less than
     * @param  CryptoQuantity|int $other quantity to compare to
     * @return boolean
     */
    public function lt($other)
    {
        if (!($other instanceof self)) {
            $other = self::fromSatoshis($other);
        }
        return ($this->compare($other) < 0);
    }

    /**
     * Returns true if less than or equal to
     * @param  CryptoQuantity|int $other quantity to compare to
     * @return boolean
     */
    public function lte($other)
    {
        if (!($other instanceof self)) {
            $other = self::fromSatoshis($other);
        }
        return ($this->compare($other) <= 0);
    }

    /**
     * Returns true if exactly zero
     * @return boolean
     */
    public function isZero()
    {
        return ($this->compare(self::fromSatoshis(0)) === 0);
    }


    /**
     * Passes through a call to the Math_BigInteger library and returns a new object
     * @return mixed A new CryptoQuantity object or the result of the operation
     */
    public function __call($method, $args)
    {
        $result = call_user_func_array([$this->big_integer, $method], $args);

        // wrap a Math_BigInteger result in a new CryptoQuantity
        if ($result instanceof BigInt) {
            return new static($result, $this->precision);
        }

        return $result;
    }

    /**
     * Passes through accessor to the underlying Math_BigInteger instance
     * @return mixed property value
     */
    public function __get($attribute)
    {
        return $this->big_integer->$attribute;
    }

    /**
     * returns the precisions as a string
     * @return string A string representation of an integer
     */
    public function __toString()
    {
        return $this->getSatoshisString();
    }

    /**
     * Serialize to JSON
     * @return string The string representation of this quantity
     */
    public function jsonSerialize()
    {
        return [
            'value' => $this->getSatoshisString(),
            'precision' => $this->precision,
        ];
    }

    // ------------------------------------------------------------------------

    protected function __construct(BigInt $big_integer, $precision)
    {
        $this->big_integer = $big_integer;
        $this->precision = $precision;
    }

    protected static function precisionUnitsAsBigInt($int, $precision)
    {
        return new BigInt($int . str_repeat('0', $precision));
    }

}
