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

namespace PriceUpdater\Report;

class ReportItem
{
    /** @var string */
    public $id_product;
    /** @var string */
    public $name;
    /** @var string */
    public $ean13;
    /** @var float */
    public $actual_price;
    /** @var float */
    public $new_price;
    /** @var bool */
    public $updated;

    public function __construct($id_product, $name, $ean13, $actual_price, $new_price, $updated)
    {
        $this->id_product = $id_product;
        $this->name = $name;
        $this->ean13 = $ean13;
        $this->actual_price = $actual_price;
        $this->new_price = $new_price;
        $this->updated = $updated;
    }
}
