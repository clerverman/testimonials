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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class TestimonialClass extends ObjectModel
{
    public $id_tsmonial; 
    public $title;
    public $file;
    public $message;
    public $status;
    public $date_add;
    public $date_upd;
    public $position;
 
    public static $definition = [
        'table' => 'tsmonial',
        'primary' => 'id_tsmonial',
        'fields' => [
            'title' => ['type' => self::TYPE_STRING, 'required' => true, 'validate' => 'isGenericName', 'size' => 60],
            'file' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'message' => ['type' => self::TYPE_HTML, 'required' => true, 'validate' => 'isCleanHtml'],
            'status' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ]; 

     
    public static function getMaxPosition()
    {
        $query = new DbQuery();
        $query->select('MAX(position)');
        $query->from('tsmonial', 'ts');

        $response = Db::getInstance()->getRow($query);

        if ($response['MAX(position)'] == null) {
            return -1;
        }
        return $response['MAX(position)'];
    } 
    public function add($autoDate = true, $nullValues = false)
    {
        $this->position = (int) $this->getMaxPosition() + 1;
        return parent::add($autoDate, $nullValues);
    } 
    public function updatePosition($way, $position)
    {
        $query = new DbQuery();
        $query->select('ts.`id_tsmonial`, ts.`position`');
        $query->from('tsmonial', 'ts');
        $query->orderBy('ts.`position` ASC');
        $tabs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (!$tabs) {
            return false;
        }

        foreach ($tabs as $tab) {
            if ((int) $tab['id_tsmonial'] == (int) $this->id) {
                $moved_tab = $tab;
            }
        }

        if (!isset($moved_tab) || !isset($position)) {
            return false;
        }

        return (Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'tsmonial`
            SET `position`= `position` ' . ($way ? '- 1' : '+ 1') . '
            WHERE `position`
            ' . ($way
            ? '> ' . (int)$moved_tab['position'] . ' AND `position` <= ' . (int)$position
            : '< ' . (int)$moved_tab['position'] . ' AND `position` >= ' . (int)$position
        ))
            && Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'tsmonial`
            SET `position` = ' . (int)$position . '
            WHERE `id_tsmonial` = ' . (int)$moved_tab['id_tsmonial']));
    }
    
}