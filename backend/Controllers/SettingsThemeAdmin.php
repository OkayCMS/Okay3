<?php


namespace Okay\Admin\Controllers;


use Aura\SqlQuery\QueryFactory;
use Okay\Core\TemplateConfig;
use Okay\Core\JsSocial;
use Okay\Entities\LanguagesEntity;
use Okay\Core\Languages;

class SettingsThemeAdmin extends IndexAdmin
{

    private $allowImg = array("png", "gif", "jpg", "jpeg", "ico", "svg");
    
    public function fetch(
        TemplateConfig $templateConfig,
        JsSocial $jsSocial,
        LanguagesEntity $languagesEntity,
        Languages $languages,
        QueryFactory $queryFactory
    ) {
        
        if ($this->request->method('POST')) {
            $phones = [];
            
            if ($cssColors = $this->request->post('css_colors')) {
                $templateConfig->updateCssVariables($cssColors);
            }
            
            if ($this->settings->social_share_theme != $this->request->post('social_share_theme')) {
                $templateConfig->clearCompiled();
            }
            
            $this->settings->social_share_theme = $this->request->post('social_share_theme');
            $this->settings->sj_shares = $this->request->post('sj_shares');
            $this->settings->site_email = $this->request->post('site_email');
            if ($sitePhones = $this->request->post('site_phones')) {
                foreach (explode(',', $sitePhones) as $k=>$phone) {
                    $phones[$k] = trim($phone);
                }
            }
            $this->settings->site_phones = $phones;
            
            $this->settings->site_social_links = explode(PHP_EOL, $this->request->post('site_social_links'));
            $this->settings->update('site_working_hours', $this->request->post('site_working_hours'));
            $this->settings->update('product_deliveries', $this->request->post('product_deliveries'));
            $this->settings->update('product_payments', $this->request->post('product_payments'));
            
            $designImagesDir = $this->config->root_dir .'/'. $this->config->design_images;
            
            // Загружаем фавикон сайта
            if ($_FILES['site_favicon']['error'] == UPLOAD_ERR_OK) {
                $tmpName = $_FILES['site_favicon']['tmp_name'];
                $ext = pathinfo($_FILES['site_favicon']['name'],PATHINFO_EXTENSION);
                $siteFaviconName = 'favicon.' . $ext;
                if (in_array($ext, $this->allowImg)) {
                    @unlink($designImagesDir . $this->settings->site_favicon);
                    if (move_uploaded_file($tmpName, $designImagesDir . $siteFaviconName)) {
                        $this->settings->site_favicon = $siteFaviconName;
                        $siteFaviconVersion = ltrim($this->settings->site_favicon_version, '0');
                        if (!$siteFaviconVersion) {
                            $siteFaviconVersion = 0;
                        }
                        $this->settings->site_favicon_version = str_pad(++$siteFaviconVersion, 3, 0, STR_PAD_LEFT);
                    }
                } else {
                    $this->settings->site_favicon = '';
                    $this->design->assign('message_error', 'wrong_favicon_ext');
                }
            } elseif (is_null($this->request->post('site_favicon'))) {
                @unlink($designImagesDir . $this->settings->site_favicon);
                $this->settings->site_favicon = '';
            }
            
            // Загружаем логотип сайта
            $siteLogoName = $this->settings->site_logo;
            $logoLang = '';
            
            $this->settings->iframe_map_code = $this->request->post('iframe_map_code');
            $multilangLogo  = $this->request->post('multilang_logo', 'integer');

            // если лого мультиязычное, добавим префикс в виде лейбла языка
            if ($multilangLogo == 1) {
                $currentLang = $languagesEntity->get($languages->getLangId());
                $logoLang = '_' . $currentLang->label;
            }
            
            if ($_FILES['site_logo']['error'] == UPLOAD_ERR_OK) {
                $tmpName = $_FILES['site_logo']['tmp_name'];
                $ext = pathinfo($_FILES['site_logo']['name'],PATHINFO_EXTENSION);
                $siteLogoName = 'logo' .  $logoLang  . '.' . $ext;
                if (in_array($ext, $this->allowImg)) {
                    // Удаляем старое лого
                    @unlink($designImagesDir . $this->settings->site_logo);
                    
                    // Загружаем новое лого
                    if (move_uploaded_file($tmpName, $designImagesDir . $siteLogoName)) {
                        $siteLogoVersion = ltrim($this->settings->site_logo_version, '0');
                        if (!$siteLogoVersion) {
                            $siteLogoVersion = 0;
                        }
                        if ($multilangLogo == 1) {
                            $this->settings->update('site_logo', $siteLogoName);
                        } else {
                            $this->settings->site_logo = $siteLogoName;
                        }
                        $this->settings->site_logo_version = str_pad(++$siteLogoVersion, 3, 0, STR_PAD_LEFT);
                    }
                } else {
                    $siteLogoName = '';
                    $this->design->assign('message_error', 'wrong_logo_ext');
                }
            } 
            
            // Если раньше лого было не мультиязычным, а теперь будет, нужно его продублировать на все языки
            if ($this->settings->multilang_logo == 0 && $multilangLogo == 1) {
                $currentLang = $languagesEntity->get($languages->getLangId());

                $ext = pathinfo($siteLogoName,PATHINFO_EXTENSION);
                foreach ($languagesEntity->find() as $language) {
                    $languages->setLangId($language->id);
                    
                    $langLogoName = 'logo_' .  $language->label  . '.' . $ext;

                    // Дублируем лого на все языки
                    if (file_exists($designImagesDir . $siteLogoName) && $siteLogoName != $langLogoName) {
                        copy($designImagesDir . $siteLogoName, $designImagesDir . $langLogoName);
                    }
                    
                    $this->settings->update('site_logo', $langLogoName);
                }
                // Удалим старое, не мультиязычное лого
                if (file_exists($designImagesDir . $siteLogoName) && $siteLogoName != 'logo_' .  $currentLang->label  . '.' . $ext) {
                    unlink($designImagesDir . $siteLogoName);
                }
                $languages->setLangId($currentLang->id);
            }
            // Если раньше лого было мультиязычным, а теперь будет не мультиязычным, нужно сохранить его из основного языка
            elseif ($this->settings->multilang_logo == 1 && $multilangLogo == 0) {
                $currentLangId = $languages->getLangId();
                $mainLang = $languagesEntity->getMainLanguage();

                $ext = pathinfo($siteLogoName,PATHINFO_EXTENSION);

                $langLogoName = 'logo_' .  $mainLang->label  . '.' . $ext;
                $siteLogoName = 'logo.' . $ext;
                // Дублируем лого из основного языка
                if (file_exists($designImagesDir . $langLogoName)) {
                    copy($designImagesDir . $langLogoName, $designImagesDir . $siteLogoName);
                }
                
                foreach ($languagesEntity->find() as $language) {
                    $languages->setLangId($language->id);
                    $this->settings->initSettings();
                    
                    // Удалим все мультиязычные лого
                    @unlink($designImagesDir . $this->settings->site_logo);
                }
                // Удалим упоминание о лого в мультиленгах
                $delete = $queryFactory->newDelete();
                $delete->from('__settings_lang')
                    ->where("param ='site_logo'");

                $this->db->query($delete);
                $this->settings->site_logo = $siteLogoName;
                
                // Вернем lang_id и мультиязычные настройки
                $languages->setLangId($currentLangId);
            }
            
            $this->settings->multilang_logo = $multilangLogo;

            $this->settings->initSettings();
            
            // Удаляем логотип
            if (is_null($this->request->post('site_logo'))) {
                unlink($designImagesDir . $this->settings->site_logo);
                if ($multilangLogo == 1) {
                    $this->settings->update('site_logo', '');
                } else {
                    $this->settings->site_logo = '';
                }
            }

            $this->design->assign('message_success', 'saved');
        }

        $sitePhones = !empty($this->settings->site_phones) ? implode(', ', $this->settings->site_phones) : "";
        
        $this->design->assign('css_variables', $templateConfig->getCssVariables());
        $this->design->assign('allow_ext', $this->allowImg);
        $this->design->assign('js_socials', $jsSocial->getSocials());
        $this->design->assign('js_custom_socials', $jsSocial->getCustomSocials());
        $this->design->assign('site_phones', $sitePhones);
        $this->design->assign('site_social_links', implode(PHP_EOL, $this->settings->site_social_links));

        $this->response->setContent($this->design->fetch('settings_theme.tpl'));
    }

}
