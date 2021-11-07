<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    indaco <github@mircoveltri.me>
 * @copyright Since 2021 Mirco Veltri
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PriceUpdater\Helpers;

class FormValidator
{
    private $data;
    private $errors;
    private static $fields = ['price_threshold_min', 'price_threshold_max', 'value_plus_minus'];

    public function __construct($form_data)
    {
        $this->data = $form_data;
    }

    public function validateForm()
    {
        foreach (self::$fields as $field) {
            if (!array_key_exists($field, $this->data)) {
                trigger_error("$field is not present in data");
                return;
            }
        }
        $this->validatePriceThresholdMin();
        $this->validatePriceThresholdMax();
        $this->validateValuePlusMinus();
        return $this->errors;
    }

    public function retrieveErrorMessages()
    {
        return implode("\n", $this->errors);
    }

    private function validatePriceThresholdMin()
    {
        $min_price = trim($this->data['price_threshold_min']);
        $max_price = trim($this->data['price_threshold_max']);
        if (empty($min_price)) {
            $this->addError('price_threshold_min', 'Invalid Configuration: <strong>Min Price</strong> is required and must be a numeric value greater than 0.');
        } elseif (!is_numeric($min_price)) {
            $this->addError('price_threshold_min', 'Invalid Configuration: <strong>Min Price</strong> must be a numeric value.');
        } elseif ($min_price >= $max_price) {
            $this->addError('price_threshold_min', 'Invalid Configuration: <strong>Min Price</strong> must be a lower than <strong>Max Price</strong>.');
        }
    }

    private function validatePriceThresholdMax()
    {
        $min_price = trim($this->data['price_threshold_min']);
        $max_price = trim($this->data['price_threshold_max']);
        if (empty($max_price)) {
            $this->addError('price_threshold_max', 'Invalid Configuration: <strong>Max Price</strong> is required.');
        } elseif (!is_numeric($max_price)) {
            $this->addError('price_threshold_max', 'Invalid Configuration: <strong>Max Price</strong> must be a numeric value.');
        } elseif ($max_price <= $min_price) {
            $this->addError('price_threshold_max', 'Invalid Configuration: <strong>Max Price</strong> must be a greater than <strong>Min Price</strong>.');
        }
    }

    private function validateValuePlusMinus()
    {
        $val = trim($this->data['value_plus_minus']);
        if (empty($val)) {
            $this->addError('value_plus_minus', 'Invalid Configuration: <strong>Value (Plus or Minus)</strong> is required.');
        } elseif (!is_numeric($val)) {
            $this->addError('value_plus_minus', 'Invalid Configuration: <strong>Value (Plus or Minus)</strong> must be a numeric value.');
        }
    }

    private function addError($key, $value)
    {
        $this->errors[$key] = $value;
    }
}
