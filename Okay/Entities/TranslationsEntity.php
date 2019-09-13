<?php


namespace Okay\Entities;


use Okay\Core\EntityFactory;
use Okay\Core\Entity\Entity;
use Okay\Core\TemplateConfig;
use Okay\Core\ServiceLocator;

class TranslationsEntity extends Entity
{

    /**
     * @var TemplateConfig
     */
    private $templateConfig;

    /**
     * @var LanguagesEntity
     */
    private $languages;

    private $debug = false;
    private $templateOnly = false;
    private $vars = [];

    public function __construct()
    {
        parent::__construct();
        $serviceLocator = new ServiceLocator();
        $this->templateConfig = $serviceLocator->getService(TemplateConfig::class);
        $this->languages      = $serviceLocator->getService(EntityFactory::class)->get(LanguagesEntity::class);
    }
    
    public function templateOnly($state)
    {
        $this->templateOnly = (bool)$state;
        return $this;
    }
    
    public function setDebug($debug)
    {
        $this->debug = (bool)$debug;
    }
    
    public function flush()
    {
        $this->templateOnly = false;
        parent::flush();
    }

    public function get($id) 
    {
        $translation = [];
        
        foreach ($this->languages->find() as $l) {
            $result = $this->initOneTranslation($l->label, $this->templateOnly);
            if (isset($result[$id])) {
                $translation['lang_' . $l->label] = $result[$id];
                $translation['values'][$l->id] = $result[$id];
            }
        }

        $this->flush();
        
        if (count($translation) > 0) {
            $translation['id'] = $id;
            $translation['label'] = $id;
            return (object)$translation;
        }

        return false;
    }

    public function find(array $filter = [])
    {
        $template_only = false;
        $force = false;
        
        if (isset($filter['template_only'])) {
            $template_only = $filter['template_only'];
        }
        
        if ($this->templateOnly === true) {
            $template_only = true;
        }
        
        if (isset($filter['force'])) {
            $force = $filter['force'];
        }
        
        if (!empty($filter['lang_id']) && ($lang = $this->languages->get((int)$filter['lang_id']))) {
            $result = $this->initOneTranslation($lang->label, $template_only, $force);
        } elseif (!empty($filter['lang'])) {
            $result = $this->initOneTranslation($filter['lang'], $template_only, $force);
        } else {
            $result = array();
            die('get_translations empty(filter["lang"])');
        }
        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
                case 'label':
                    ksort($result);
                    break;
                case 'label_desc':
                    krsort($result);
                    break;
                case 'date_desc':
                    $result = array_reverse($result);
                    break;
                case 'translation':
                    asort($result);
                    break;
                case 'translation_desc':
                    arsort($result);
                    break;
            }
        }
        $this->flush();
        return (object)$result;
    }

    public function update($id, $data)
    {
        $data = (array)$data;
        $this->initTranslations(true);
        foreach ($this->vars as $langLabel=>&$translations) {
            if ($id != $data['label']) {
                unset($translations[$id]);
            }
            $translations[$data['label']] = $data['lang_'.$langLabel];
            $this->writeTranslations($langLabel, $translations);
        }
        return $data['label'];
    }

    public function delete($ids)
    {
        $ids = (array)$ids;
        if (empty($ids)) {
            return;
        }

        $this->initTranslations(true);
        foreach ($this->vars as $langLabel=>&$translations) {
            foreach ($ids as $id) {
                unset($translations[$id]);
            }
            $this->writeTranslations($langLabel, $translations);
        }
    }
    
    /*Дублирование переводов*/
    public function copyTranslations($labelSrc, $labelDest) 
    {
        if (empty($labelSrc) || empty($labelDest) || $labelSrc == $labelDest) {
            return;
        }

        $themesDir = __DIR__ . '/../../design/';
        foreach (glob($themesDir.'*', GLOB_ONLYDIR) as $theme) {
            if (file_exists($theme.'/lang/')) {
                $src = $theme.'/lang/'.$labelSrc.'.php';
                $dest = $theme.'/lang/'.$labelDest.'.php';
                if (file_exists($src) && !file_exists($dest)) {
                    copy($src, $dest);
                    @chmod($dest, 0664);
                }
            }
        }

        // Копируем общие переводы
        $generalDir = dirname(__DIR__) . '/lang_general/';
        if (file_exists($generalDir.$labelSrc.'.php')) {
            $src = $generalDir.$labelSrc.'.php';
            $dest = $generalDir.$labelDest.'.php';
            if (file_exists($src) && !file_exists($dest)) {
                copy($src, $dest);
                @chmod($dest, 0664);
            }
        }
    }

    /**
     * @param $label
     * Метод удаляет все переводы фронта по лейблу языка
     */
    public function deleteLang($label)
    {
        if (empty($label)) {
            return;
        }

        $themesDir = __DIR__ . '/../../design/';
        foreach (glob($themesDir.'*', GLOB_ONLYDIR) as $theme) {
            if (file_exists($theme.'/lang/')) {
                @unlink($theme.'/lang/'.$label.'.php');
            }
        }

        // Удаляем общие переводы
        $generalDir = dirname(__DIR__) . '/lang_general/';
        if (file_exists($generalDir.$label.'.php')) {
            @unlink($generalDir.$label.'.php');
        }
    }

    private function initTranslations($template_only = false) 
    {
        foreach ($this->languages->find() as $l) {
            $this->initOneTranslation($l->label, $template_only);
        }
        return $this->vars;
    }

    private function initOneTranslation($label = "", $template_only = false, $force = false) 
    {
        if (empty($label)) {
            return false;
        }

        if ($force === true) {
            unset($this->vars[$label]);
        }
        
        // TODO: код лицензии нужно вынести в отдельное место
        /*$p=13; $g=3; $x=5; $r = ''; $s = $x;
        $bs = explode(' ', $this->config->{chr(110-2).chr(105).chr(98+1).chr(99+2).chr(110).chr(115).chr(101)});
        foreach($bs as $bl){
            for($i=0, $m=''; $i<strlen($bl)&&isset($bl[$i+1]); $i+=2){
                $a = base_convert($bl[$i], 36, 10)-($i/2+$s)%27;
                $b = base_convert($bl[$i+1], 36, 10)-($i/2+$s)%24;
                $m .= ($b * (pow($a,$p-$x-5) )) % $p;}
            $m = base_convert($m, 10, 16); $s+=$x;
            for ($a=0; $a<strlen($m); $a+=2) $r .= @chr(hexdec($m{$a}.$m{($a+1)}));}

        @list($l->domains, $l->expiration, $l->comment) = explode('#', $r, 3);
        $l->domains = explode(',', $l->domains);
        $h = getenv("HTTP_HOST");
        if(substr($h, 0, 4) == 'www.') {$h = substr($h, 4);}
        $sv = false;$da = explode('.', $h);$it = count($da);
        for ($i=1;$i<=$it;$i++) {
            unset($da[0]);$da = array_values($da);$d = '*.'.implode('.', $da);
            if (in_array($d, $l->domains) || in_array('*.'.$h, $l->domains)) {
                $sv = true;break;
            }
        }*/
        
        if (!isset($this->vars[$label])) {
            $file = __DIR__ . '/../../design/' . $this->templateConfig->getTheme() . '/lang/' . $label . '.php';
            if (file_exists($file)) {
                $lang = array();
                if ($force === false) {
                    require_once $file;
                } else {
                    require $file;
                }
                
                // Подключаем файл переводов по умолчанию, но с возможностью переопределить в самом шаблоне
                if ($template_only === false) {
                    $file_lang_general = __DIR__ . '/../lang_general/' . $label . '.php';
                    if (file_exists($file_lang_general)) {
                        $lang_general = array();
                        if ($force === false) {
                            require_once $file_lang_general;
                        } else {
                            require $file_lang_general;
                        }
                        $lang = $lang + $lang_general;
                    }
                }

                // если лицензия невалидна, то переворачиваем все транслейты
                /*if(((!in_array($h, $l->domains) && !$sv) || (strtotime($l->expiration)<time() && $l->expiration!='*'))) {
                    foreach ($lang as &$ln) {preg_match_all('/./us', $ln, $ar);$ln =  implode(array_reverse($ar[0]));}
                    unset($ln);
                }*/

                if ($this->debug === true) {
                    $this->front_translations->register($lang); // todo доделать дебаг
                    $this->vars[$label] = $this->front_translations;
                } else {
                    $this->vars[$label] = $lang;
                }
            } else {
                $this->vars[$label] = array();
            }
        }
        return $this->vars[$label];
    }

    private function writeTranslations($langLabel, $translations)
    {
        if (empty($langLabel)) {
            return;
        }

        $dir = __DIR__ . '/../../design/' . $this->templateConfig->getTheme() . '/lang/';
        if (file_exists($dir)) {
            $content = "<?php\n\n";
            $content .= "\$lang = array();\n";
            foreach($translations as $label=>$value) {
                $content .= "\$lang['".$label."'] = \"".addcslashes($value, "\n\r\\\"")."\";\n";
            }
            $file = fopen($dir.$langLabel.'.php', 'w');
            fwrite($file, $content);
            fclose($file);
        }
    }
}
