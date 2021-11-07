<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* @author    indaco <github@mircoveltri.me>
* @copyright Since 2021 Mirco Veltri
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Priceupdater extends Module
{
    /** @var string Module prefix name */
    public $module_prefix = null;
    /** @var string module template files path */
    public $tpl_path = null;

    /** PS Tables */
    const PRODUCT_TABLE = 'product';
    const PRODUCT_LANG_TABLE = 'product_lang';

    public function __construct()
    {
        $this->name = 'priceupdater';
        $this->tab = 'other';
        $this->version = '1.0.0';
        $this->author = 'indaco';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Price Updater', [], 'Modules.Priceupdater.Admin');
        $this->description = $this->trans('Prestashop module to easily update products prices based on price threshold', [], 'Modules.Priceupdater.Admin');

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', [], 'Modules.Priceupdater.Admin');

        $this->module_prefix = Tools::strtoupper($this->name) . '_';
        $this->tpl_path = $this->name . '/views/templates/admin/';
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install(): bool
    {
        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
            Configuration::updateValue($this->module_prefix . 'VERSION', $this->version);
    }

    public function uninstall(): bool
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall() &&
            Configuration::deleteByName($this->module_prefix . 'VERSION');
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function getHookController($hook_name)
    {
        // Include the controller file
        require_once dirname(__FILE__) . '/controllers/hook/' . $hook_name . '.php';
        // Build the controller name
        $controller_name = get_class($this) . ucwords($hook_name) . 'Controller';
        // Create a new instance for the controller
        $controller = new $controller_name($this, __FILE__, $this->_path);

        return $controller;
    }

    public function getContent()
    {
        $controller = $this->getHookController('getContent');
        return $controller->run();
    }
}
