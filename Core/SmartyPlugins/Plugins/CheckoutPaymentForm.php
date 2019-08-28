<?php


namespace Okay\Core\SmartyPlugins\Plugins;


use Okay\Core\Design;
use Okay\Core\SmartyPlugins\Func;

class CheckoutPaymentForm extends Func
{

    protected $tag = 'checkout_payment_form';
    
    private $design;
    private $rootDir;
    
    public function __construct(Design $design, $rootDir)
    {
        $this->design = $design;
        $this->rootDir = $rootDir;
    }

    public function run($params)
    {
        $module_name = preg_replace("/[^A-Za-z0-9]+/", "", $params['module']);

        return null;// todo нужно обновить все модули оплаты
        
        $form = '';
        if(!empty($module_name) && is_file("payment/$module_name/$module_name.php")) {
            include_once("payment/$module_name/$module_name.php");
            $module = new $module_name();
            $form = $module->checkout_form($params['order_id']);
            $tpl = "payment/".$module_name."/form.tpl";
            if (!empty($form)) {
                foreach ($form as $var => $val) {
                    $this->design->assign($var, $val);
                }
            }
            $this->design->assign('payment_module',$module_name);
        }
        
        if (!empty($tpl) && !empty($form) && file_exists($this->rootDir.'/'.$tpl)){
            return $this->design->fetch($tpl);
        } else{
            return false;
        }
    }
}