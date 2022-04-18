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



class TestimonialsViewModuleFrontController extends ModuleFrontController
{
    public function initContent()
    { 
        parent::initContent();
        if (Context::getContext()->customer->logged) {
            $this->context->smarty->assign([
                'path' => '/modules/' . $this->module->name . '/views/img/',
                'datas' => TestimonialClass::getTestimonialByStatus(),
                'action_url' => Context::getContext()->link->getModuleLink('testimonials', 'view', [], true)
            ]);
            $this->setTemplate('module:testimonials/views/templates/front/testimonials_view.tpl');
        }
    }
    protected function fileUpload($file)
    {
        $result = array(
            'error' => array(),
            'image' => '',
        );
        $types = array('Doc', 'jpeg', 'docx', 'png');
        if (isset($_FILES[$file]) && isset($_FILES[$file]['tmp_name']) && !empty($_FILES[$file]['tmp_name']) && ($_FILES[$file]['size'] < 1048576)) {
            $name = str_replace(strrchr($_FILES[$file]['name'], '.'), '', $_FILES[$file]['name']);

            $imageSize = @getimagesize($_FILES[$file]['tmp_name']);
            if (
                !empty($imageSize) &&
                ImageManager::isCorrectImageFileExt($_FILES[$file]['name'], $types)
            ) {
                $imageName = explode('.', $_FILES[$file]['name']);
                $imageExt = $imageName[1];
                $tempName = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                $coverImageName = $name . '-' . rand(0, 1000) . '.' . $imageExt;
                if ($upload_error = ImageManager::validateUpload($_FILES[$file])) {
                    $result['error'][] = $upload_error;
                } elseif (!$tempName || !move_uploaded_file($_FILES[$file]['tmp_name'], $tempName)) {
                    $result['error'][] = $this->trans('An error occurred during move image.', [], 'Modules.Testimonials.Admin');
                } else {
                    $destinationFile = _PS_MODULE_DIR_ . $this->module->name . '/views/img/' . $coverImageName;
                    if (!ImageManager::resize($tempName, $destinationFile, null, null, $imageExt)) {
                        $result['error'][] = $this->trans('An error occurred during the image upload.', [], 'Modules.Testimonials.Admin');
                    }
                }
                if (isset($tempName)) {
                    @unlink($tempName);
                }

                if (!count($result['error'])) {
                    $result['image'] = $coverImageName;
                    $result['width'] = $imageSize[0];
                    $result['height'] = $imageSize[1];
                }
                return $result;
            }
        } else {
            return $result;
        }
    }
    public function postProcess()
    {

        $status = false;
        $testimonial = new TestimonialClass();
        $testimonial->id_costumer = Context::getContext()->customer->id;
        $testimonial->status = 0;
        $testimonial->title = Tools::getValue('title');
        $testimonial->message = Tools::getValue('message');
        $myfile = $this->fileUpload('file');
        if ($myfile['image']) {
            $testimonial->file = $myfile['image'];
        }
        $status = $testimonial->save();
    }
}