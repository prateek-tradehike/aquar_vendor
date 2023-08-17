<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the NekloEULA that is bundled with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt
 *
 * Copyright (c)  Neklo (http://store.neklo.com/)
 */

namespace Neklo\ProductPosition\Helper;

class Parser extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Parse query string to array
     *
     * @param string $str
     * @return array
     */
    public function parseQueryStr($str)
    {
        $argSeparator = '&';
        $result = [];
        $partsQueryStr = explode($argSeparator, $str);

        foreach ($partsQueryStr as $partQueryStr) {
            if ($this->validateQueryStr($partQueryStr)) {
                $param = $this->explodeAndDecodeParam($partQueryStr);
                $param = $this->handleRecursiveParamForQueryStr($param);
                $result = $this->appendParam($result, $param);
            }
        }
        return $result;
    }

    /**
     * Validate query pair string
     *
     * @param string $str
     * @return bool
     */
    private function validateQueryStr($str)
    {
        if (!$str || (strpos($str, '=') === false)) {
            return false;
        }
        return true;
    }

    /**
     * Prepare param
     *
     * @param string $str
     * @return array
     */
    private function explodeAndDecodeParam($str)
    {
        $preparedParam = [];
        $param = explode('=', $str);
        $preparedParam['key'] = urldecode(array_shift($param));
        $preparedParam['value'] = urldecode(array_shift($param));

        return $preparedParam;
    }

    /**
     * Append param to general result
     *
     * @param array $result
     * @param array $param
     * @return array
     */
    private function appendParam(array $result, array $param)
    {
        $key   = $param['key'];
        $value = $param['value'];

        if ($key) {
            if (is_array($value) && array_key_exists($key, $result)) {
                $result[$key] = $this->mergeRecursiveWithoutOverwriteNumKeys($result[$key], $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Merge array recursive without overwrite keys.
     * PHP function array_merge_recursive merge array
     * with overwrite num keys
     *
     * @param array $baseArray
     * @param array $mergeArray
     * @return array
     */
    public function mergeRecursiveWithoutOverwriteNumKeys(array $baseArray, array $mergeArray)
    {
        foreach ($mergeArray as $key => $value) {
            if (is_array($value)) {
                if (array_key_exists($key, $baseArray)) {
                    $baseArray[$key] = $this->mergeRecursiveWithoutOverwriteNumKeys($baseArray[$key], $value);
                } else {
                    $baseArray[$key] = $value;
                }
            } else {
                if ($key) {
                    $baseArray[$key] = $value;
                } else {
                    $baseArray[] = $value;
                }
            }
        }

        return $baseArray;
    }

    /**
     * Handle param recursively
     *
     * @param array $param
     * @return array
     */
    private function handleRecursiveParamForQueryStr(array $param)
    {
        $value = $param['value'];
        $key = $param['key'];

        $subKeyBrackets = $this->getLastSubkey($key);
        $subKey = $this->getLastSubkey($key, false);
        if ($subKeyBrackets) {
            if ($subKey) {
                $param['value'] = [$subKey => $value];
            } else {
                $param['value'] = [$value];
            }
            $param['key'] = $this->removeSubkeyPartFromKey($key, $subKeyBrackets);
            $param = $this->handleRecursiveParamForQueryStr($param);
        }

        return $param;
    }

    /**
     * Remove subkey part from key
     *
     * @param string $key
     * @param string $subKeyBrackets
     * @return string
     */
    private function removeSubkeyPartFromKey($key, $subKeyBrackets)
    {
        return substr($key, 0, strrpos($key, $subKeyBrackets));
    }

    /**
     * Get last part key from query array
     *
     * @param string $key
     * @param bool $withBrackets
     * @return string
     */
    private function getLastSubkey($key, $withBrackets = true)
    {
        $subKey = '';
        $leftBracketSymbol  = '[';
        $rightBracketSymbol = ']';

        $firstPos = strrpos($key, $leftBracketSymbol);
        $lastPos  = strrpos($key, $rightBracketSymbol);

        if (($firstPos !== false || $lastPos !== false)
            && $firstPos < $lastPos
        ) {
            $keyLenght = $lastPos - $firstPos + 1;
            $subKey = substr($key, $firstPos, $keyLenght);
            if (!$withBrackets) {
                $subKey = ltrim($subKey, $leftBracketSymbol);
                $subKey = rtrim($subKey, $rightBracketSymbol);
            }
        }
        return $subKey;
    }

    public function unserialize($data)
    {
        if (false === $data || null === $data || '' === $data || '[]' === $data || 'a:0:{}' === $data) {
            return [];
        }

        $items = json_decode($data, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $items;
        } else {
            return [];
        }
    }
}
