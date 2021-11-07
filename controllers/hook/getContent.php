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

require_once _PS_MODULE_DIR_ . 'priceupdater/classes/report/ReportMaker.php';
require_once _PS_MODULE_DIR_ . 'priceupdater/classes/helpers/FormValidator.php';

use PriceUpdater\Report\ReportMaker;
use PriceUpdater\Helpers\FormValidator;

class PriceupdaterGetContentController
{
    /** @var null */
    private $module = null;
    /** @var Context|null */
    private $context = null;
    /** @var \PrestaShopBundle\Translation\TranslatorComponent|null */
    private $translator = null;
    private $db = null;
    private $report = null;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->translator = $this->context->getTranslator();
        $this->db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $this->report = ReportMaker::make($this->module->module_prefix . 'Report');
    }

    public function run()
    {
        return $this->processConfiguration();
    }

    /**
     * @return string
     *
     * @throws PrestaShopException
     */
    public function renderForm()
    {
        $helper = new HelperForm();
        // Title and toolbar
        $helper->title = $this->module->displayName;
        $helper->table = $this->module->name;
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->show_cancel_button = true;
        $helper->submit_action = 'submit' . $this->module->name;
        // Language
        $defaultLang = $this->context->language->id;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        // General
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getModuleConfigFromDB(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $defaultLang,
        ];

        return $helper->generateForm([$this->makeConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function makeConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->translator->trans('Settings', [], 'Modules.Priceupdater.Admin'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->translator->trans('Dry-run mode', [], 'Modules.Priceupdater.Admin'),
                        'name' => $this->module->module_prefix . 'MODE_DRYRUN',
                        'is_bool' => true,
                        'desc' => $this->translator->trans('Use this module in dry-run mode', [], 'Modules.Priceupdater.Admin'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->translator->trans('Yes', [], 'Modules.Priceupdater.Admin')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->translator->trans('No', [], 'Modules.Priceupdater.Admin')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'required' => true,
                        'label' => $this->translator->trans('Min Price', [], 'Modules.Priceupdater.Admin'),
                        'name' => $this->module->module_prefix . 'PRICE_THRESHOLD_MIN',
                        'prefix' => Currency::getDefaultCurrency()->symbol,
                        'desc' => $this->translator->trans('All products with price greater than', [], 'Modules.Priceupdater.Admin'),
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'required' => true,
                        'label' => $this->translator->trans('Max Price', [], 'Modules.Priceupdater.Admin'),
                        'name' => $this->module->module_prefix . 'PRICE_THRESHOLD_MAX',
                        'prefix' => Currency::getDefaultCurrency()->symbol,
                        'desc' => $this->translator->trans('All products with price lower than', [], 'Modules.Priceupdater.Admin'),
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'required' => true,
                        'label' => $this->translator->trans('Value (Plus or Minus)', [], 'Modules.Priceupdater.Admin'),
                        'name' => $this->module->module_prefix . 'VALUE_PLUS_MINUS',
                        'prefix' => '<i class="icon icon-exchange"></i>',
                        'desc' => $this->translator->trans('Final price = actual +/- value. A negative value here to calculate the subtraction', [], 'Modules.Priceupdater.Admin'),
                        'col' => 3
                    ),
                ),
                'submit' => array(
                    'title' => $this->translator->trans('Save & Run', [], 'Modules.Priceupdater.Admin'),
                ),
            ),
        );
    }

    /**
     * Process form data.
     *
     * @param $form_data
     *
     * @return array
     */
    protected function postProcess($form_data): array
    {
        $validation = new FormValidator($form_data);
        $errors = $validation->validateForm();
        if (!empty($errors)) {
            return [
                'is_valid' => false,
                'messages' => $errors
            ];
        }

        $dbOp_response = $this->updateModuleConfigToDB($this->db, $form_data);
        if (!$dbOp_response['is_valid']) {
            return [
                'is_valid' => false,
                'messages' => array($dbOp_response['message'])
            ];
        }

        $is_dry_run = $form_data['mode_dryrun'];
        $price_threshold_min = $form_data['price_threshold_min'];
        $price_threshold_max = $form_data['price_threshold_max'];
        $value_plus_minus = $form_data['value_plus_minus'];

        $products = $this->findAllProductsByMinMaxPrice($this->db, $price_threshold_min, $price_threshold_max);
        $dbOp_response = $this->bulkUpdateProductsPrice($products, $value_plus_minus, $is_dry_run);

        if (!$dbOp_response['is_valid']) {
            return [
                'is_valid' => false,
                'messages' => array($dbOp_response['message'])
            ];
        }

        return [
            'is_valid' => true,
            'messages' => array($dbOp_response['message']),
            'is_dry_run' => $is_dry_run,
            'data' => $this->report->getData()
        ];
    }

    /**
     * @return array
     *
     * @throws PrestaShopException
     */
    private function getModuleConfigFromDB(): array
    {
        $vars = array();
        $results = $this->db->executeS('SELECT `name`, `value` FROM `' . _DB_PREFIX_ . $this->module->name . '`');

        foreach ($results as $row) {
            $vars[$this->module->module_prefix . $row["name"]] = $row["value"];
        }

        unset($results);
        return $vars;
    }

    /**
     * @param $db
     * @param $form_data
     *
     * @return array
     */
    private function updateModuleConfigToDB($db, $form_data): array
    {
        foreach ($form_data as $key => $value) {
            if (!$db->update($this->module->name, [
                'value' => pSQL($value),
                'date_upd' => date('Y-m-d H:i:s'),
            ], 'name = "' . $key . '"', 0, false, false, true)
            ) {
                return [
                    'is_valid' => false,
                    'message' => 'Something went wrong updating config values on the database'
                ];
            }
        }

        return [
            'is_valid' => true,
            'message' => ''
        ];
    }

    /**
     * Set values for the inputs.
     *
     * @return array
     */
    private function getConfigFormData(): array
    {
        return array(
            'mode_dryrun' => (string)(Tools::getValue($this->module->module_prefix . 'MODE_DRYRUN')),
            'price_threshold_min' => (string)(Tools::getValue($this->module->module_prefix . 'PRICE_THRESHOLD_MIN')),
            'price_threshold_max' => (string)(Tools::getValue($this->module->module_prefix . 'PRICE_THRESHOLD_MAX')),
            'value_plus_minus' => (string)(Tools::getValue($this->module->module_prefix . 'VALUE_PLUS_MINUS')),
        );
    }

    /**
     * @return string
     *
     * @throws PrestaShopException
     */
    private function processConfiguration()
    {
        $submitOutput = $this->handleFormSubmit();
        return $this->renderForm() . $submitOutput;
    }

    private function handleFormSubmit(): string
    {
        $output = '';
        if ((bool)Tools::isSubmit('submit' . $this->module->name)) {
            $form_data = $this->getConfigFormData();

            $processResult = $this->postProcess($form_data);

            if (!$processResult['is_valid']) {
                foreach ($processResult['messages'] as $message) {
                    $output .= $this->module->displayError($this->translator->trans($message, [], 'Modules.Priceupdater.Admin'));
                }
                return $output;
            } else {
                $this->context->smarty->assign([
                    'is_dry_run' => $processResult['is_dry_run'],
                    'messages' => $processResult['messages'],
                    'report_name' => $this->report->getName(),
                    'report_data' => $processResult['data'],
                    'currency' => Currency::getDefaultCurrency()->symbol
                ]);
                $output .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->tpl_path . 'report.tpl');
            }
        }
        return $output;
    }

    /**
     * @param $db
     * @param $price_threshold_min
     * @param $price_threshold_max
     *
     * @return array
     */
    private function findAllProductsByMinMaxPrice($db, $price_threshold_min, $price_threshold_max): array
    {
        $result = $db->executeS(
            'SELECT id_product
            FROM ' . _DB_PREFIX_ . $this->module::PRODUCT_TABLE . '
            WHERE (price >= "' . $price_threshold_min . '" AND price <= "' . $price_threshold_max . '")
            GROUP by id_product'
        );

        return $result;
    }

    /**
     * @param $db
     * @param $is_dry_run
     * @param $products
     * @param $value_plus_minus
     *
     * @return array
     */
    private function bulkUpdateProductsPrice($products, $value_plus_minus, $is_dry_run): array
    {
        foreach ($products as $product) {
            $dbOpResult = $this->updateSingleProductPrice($product, $value_plus_minus, $is_dry_run);
            if ($dbOpResult['is_valid']) {
                $this->report->addReportItem($dbOpResult['product_obj'], $dbOpResult['actual_price'], true);
            } else {
                return [
                    'is_valid' => false,
                    'message' => $dbOpResult['message']
                ];
            }
        }

        return [
            'is_valid' => true,
            'message' => $this->report->resultMessage($products, $is_dry_run)
        ];
    }

    /**
     * @param $db
     * @param $is_dry_run
     * @param $products
     * @param $value_plus_minus
     *
     * @return object
     * @throws PrestaShopException
     */
    private function updateSingleProductPrice($product, $value_plus_minus, $is_dry_run):array
    {
        $lang_id = (int) Configuration::get('PS_LANG_DEFAULT');
        $productObj = new Product($product['id_product'], false, $lang_id);
        // Validate product object
        if (Validate::isLoadedObject($productObj)) {
            $actual_price = $productObj->price;
            $productObj->price = $actual_price + $value_plus_minus;
            if (!$is_dry_run) {
                $productObj->save();
            }

            return [
                'is_valid' => true,
                'message' => '',
                'actual_price' => $actual_price,
                'product_obj' => $productObj
            ];
        } else {
            return [
                'is_valid' => false,
                'message' => 'Something went wrong updating the product price for Product ID: '. $product['id_product'],
                'actual_price' => $productObj->price,
                'product_obj' => null
            ];
        }
    }
}
