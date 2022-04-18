<?php
/**
* 2007-2022 PrestaShop
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
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once("classes/testimonialClass.php");

class Tsmonial extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'tsmonial';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Slimani mouhcine';
        $this->need_instance = 0;
        $this->bootstrap = true; 
        parent::__construct(); 
        $this->displayName = $this->l('TestMonials');
        $this->description = $this->l('TesTmonials  is my first module in prestashop .');
        $this->confirmUninstall = $this->l('Are you Sure ?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }
 
    public function createTabs()
    {
        // get id_parent from tab table in db ; 
        $idParent = (int) Tab::getIdFromClassName('AdminTestimonials');
        // test is it empty : true => creating the menu item in aside menu 
        if (empty($idParent)) {
            $tap = new Tab();
            $tap->name = array();
            foreach (Language::getLanguages(true) as $lang) {
                $tap->name[$lang['id_lang']] = $this->l('Testmonials');
            }
            $tap->class_name = 'AdminTestimonials';
            $tap->id_parent = 0;
            $tap->module = $this->name; 
            $tap->icon = 'library_books';  
            $tap->add();
        } 
        // to add sub item to menu added before 
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Testimonial');
        }
        $tab->class_name = 'AdminTestimonial';
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminTestimonials');
        $tab->module = $this->name;
        $tab->icon = 'library_books';
        $tab->add();
        
        return true;
    } 


    public function install()
    { 
        include(dirname(__FILE__) . '/sql/install.php');
        return parent::install() &&
            $this->createTabs() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php'); 
        // delete the item of module created 
        $idP = (int) Tab::getIdFromClassName('AdminTestimonials');
        $idPar = (int) Tab::getIdFromClassName('AdminTestimonial');
        $tab1 = new Tab($idP) ; 
        $tab2 = new Tab($idPar) ; 
        $tab1->delete() ; 
        $tab2->delete() ; 
        return parent::uninstall();
    }
  
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }
 
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }
 
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
}