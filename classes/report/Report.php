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

require_once _PS_MODULE_DIR_ . 'priceupdater/classes/report/ReportItemFactory.php';
require_once _PS_MODULE_DIR_ . 'priceupdater/classes/report/ReportTemplate.php';
require_once _PS_MODULE_DIR_ . 'priceupdater/classes/helpers/PriceUpdaterHelper.php';

use PriceUpdater\Helpers\PriceUpdaterHelper;

class Report implements ReportTemplate
{
    /** @var string */
    private $name;
    /** @var array */
    private $data;

    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->data = [];
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    public function addReportItem($productObj, $actual_price, $updated): void
    {
        $report_item = ReportItemFactory::create($productObj->id, $productObj->name, $productObj->ean13, $actual_price, $productObj->price, $updated);
        array_push($this->data, PriceUpdaterHelper::productObjectToArray($report_item));
    }

    public function resultMessage($products, $is_dry_run): string
    {
        if ($is_dry_run) {
            return 'DRY-RUN MODE ENABLED. A total of ' . count($products) . ' products would be updated.';
        } else {
            return 'All good! A total of ' . count($products) . ' products have updated price.';
        }
    }
}
