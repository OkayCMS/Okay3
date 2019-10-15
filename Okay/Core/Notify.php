<?php


namespace Okay\Core;


use Okay\Entities\BlogEntity;
use Okay\Entities\CallbacksEntity;
use Okay\Entities\CommentsEntity;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\DeliveriesEntity;
use Okay\Entities\FeedbacksEntity;
use Okay\Entities\OrdersEntity;
use Okay\Entities\OrderStatusEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\TranslationsEntity;
use Okay\Entities\UsersEntity;
use Okay\Logic\OrdersLogic;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;

class Notify
{

    private $PHPMailer;
    private $settings;
    private $languages;
    private $entityFactory;
    private $ordersLogic;
    private $templateConfig;
    private $design;
    private $backendTranslations;
    private $rootDir;
    private $logger;
    
    public function __construct(
        Settings $settings,
        Languages $languages,
        EntityFactory $entityFactory,
        Design $design,
        TemplateConfig $templateConfig,
        OrdersLogic $ordersLogic,
        BackendTranslations $backendTranslations,
        PHPMailer $PHPMailer,
        LoggerInterface $logger,
        $rootDir
    ) {
        $this->PHPMailer = $PHPMailer;
        $this->settings = $settings;
        $this->languages = $languages;
        $this->design = $design;
        $this->templateConfig = $templateConfig;
        $this->ordersLogic = $ordersLogic;
        $this->entityFactory = $entityFactory;
        $this->backendTranslations = $backendTranslations;
        $this->logger = $logger;
        $this->rootDir = $rootDir;
    }

    /* SMTP отправка емейла*/
    public function SMTP($to, $subject, $message)
    {
        $this->PHPMailer->IsSMTP(); // telling the class to use SMTP
        $this->PHPMailer->Host       = $this->settings->smtp_server;
        $this->PHPMailer->SMTPDebug  = 0;
        $this->PHPMailer->SMTPAuth   = true;
        $this->PHPMailer->CharSet    = 'utf-8';
        $this->PHPMailer->Port       = $this->settings->smtp_port;
        if ($this->PHPMailer->Port == 465) {
            $this->PHPMailer->SMTPSecure = "ssl";
            // Добавляем протокол, если не указали
            $this->PHPMailer->Host = (strpos($this->PHPMailer->Host, "ssl://") === false) ? "ssl://".$this->PHPMailer->Host : $this->PHPMailer->Host;
        }
        $this->PHPMailer->Username   = $this->settings->smtp_user;
        $this->PHPMailer->Password   = $this->settings->smtp_pass;
        $this->PHPMailer->SetFrom($this->settings->smtp_user, $this->settings->notify_from_name);
        $this->PHPMailer->AddReplyTo($this->settings->smtp_user, $this->settings->notify_from_name);
        $this->PHPMailer->Subject    = $subject;

        $this->PHPMailer->MsgHTML($message);
        $this->PHPMailer->addCustomHeader("MIME-Version: 1.0\n");

        $recipients = explode(',',$to);
        if (!empty($recipients)) {
            foreach ($recipients as $i=>$r) {
                $this->PHPMailer->AddAddress($r);
            }
        } else {
            $this->PHPMailer->AddAddress($to);
        }

        if ($this->PHPMailer->Send()) {
            return;
        }

        if ($this->PHPMailer->SMTPDebug != 0) {
            $this->logger->notice("Can`t send mail to '{$to}', ErrorInfo: {$this->PHPMailer->ErrorInfo}", ['subject' => $subject]);
        } else {
            $this->logger->notice("Can`t send mail to '{$to}', ErrorInfo: For view details should enable debug mode", ['subject' => $subject]);
        }
    }

    /*Отправка емейла*/
    public function email($to, $subject, $message, $from = '', $reply_to = '')
    {
        $headers = "MIME-Version: 1.0\n" ;
        $headers .= "Content-type: text/html; charset=utf-8; \r\n";
        $headers .= "From: $from\r\n";
        if(!empty($reply_to)) {
            $headers .= "reply-to: $reply_to\r\n";
        }
        
        $subject = "=?utf-8?B?".base64_encode($subject)."?=";

        if ($this->settings->use_smtp) {
            $this->SMTP($to, $subject, $message);
        } else {
            mail($to, $subject, $message, $headers);
        }
    }

    /*Отправка емейла клиенту о заказе*/
    public function emailOrderUser($orderId)
    {
        /** @var OrdersEntity $ordersEntity */
        $ordersEntity = $this->entityFactory->get(OrdersEntity::class);
        
        /** @var DeliveriesEntity $deliveriesEntity */
        $deliveriesEntity = $this->entityFactory->get(DeliveriesEntity::class);
        
        /** @var OrderStatusEntity $ordersStatusEntity */
        $ordersStatusEntity = $this->entityFactory->get(OrderStatusEntity::class);
        
        /** @var CurrenciesEntity $currenciesEntity */
        $currenciesEntity = $this->entityFactory->get(CurrenciesEntity::class);
        
        /** @var TranslationsEntity $translationsEntity */
        $translationsEntity = $this->entityFactory->get(TranslationsEntity::class);
        
        if (!($order = $ordersEntity->get(intval($orderId))) || empty($order->email)) {
            return false;
        }
        
        /*lang_modify...*/
        if (!empty($order->lang_id)) {
            $currentLangId = $this->languages->getLangId();
            $this->languages->setLangId($order->lang_id);

            $currencies = $currenciesEntity->find(['enabled'=>1]);
            // Берем валюту из сессии
            if (isset($_SESSION['currency_id'])) {
                $currency = $currenciesEntity->get((int)$_SESSION['currency_id']);
            } else {
                $currency = reset($currencies);
            }
            
            $this->design->assign("currency", $currency);
            $this->settings->initSettings();
            $this->design->assign('settings', $this->settings);
            $this->design->assign('lang', $translationsEntity->find(array('lang_id'=>$order->lang_id)));
        }
        /*/lang_modify...*/
        
        $purchases = $this->ordersLogic->getOrderPurchases($order->id);
        $this->design->assign('purchases', $purchases);
        
        // Способ доставки
        $delivery = $deliveriesEntity->get($order->delivery_id);
        $this->design->assign('delivery', $delivery);
        
        $this->design->assign('order', $order);
        $orderStatuses = $ordersStatusEntity->find(["status"=>intval($order->status_id)]);
        $this->design->assign('order_status', reset($orderStatuses));
        
        // Отправляем письмо
        // Если в шаблон не передавалась валюта, передадим
        if ($this->design->smarty->getTemplateVars('currency') === null) {
            $this->design->assign('currency', current($currenciesEntity->find(['enabled'=>1])));
        }
        $emailTemplate = $this->design->fetch($this->rootDir.'design/'.$this->templateConfig->getTheme().'/html/email/email_order.tpl');
        $subject = $this->design->get_var('subject');
        $from = ($this->settings->notify_from_name ? $this->settings->notify_from_name." <".$this->settings->notify_from_email.">" : $this->settings->notify_from_email);
        $this->email($order->email, $subject, $emailTemplate, $from);
        
        /*lang_modify...*/
        if (!empty($currentLangId)) {
            $this->languages->setLangId($currentLangId);
            
            $currencies = $currenciesEntity->find(['enabled'=>1]);
            // Берем валюту из сессии
            if (isset($_SESSION['currency_id'])) {
                $currency = $currenciesEntity->get((int)$_SESSION['currency_id']);
            } else {
                $currency = reset($currencies);
            }

            $this->design->assign("currency", $currency);
            $this->settings->initSettings();
            $this->design->assign('settings', $this->settings);
        }
        /*/lang_modify...*/
    }

    /*Отправка емейла о заказе администратору*/
    public function emailOrderAdmin($orderId)
    {
        /** @var OrdersEntity $ordersEntity */
        $ordersEntity = $this->entityFactory->get(OrdersEntity::class);

        /** @var DeliveriesEntity $deliveriesEntity */
        $deliveriesEntity = $this->entityFactory->get(DeliveriesEntity::class);
        
        /** @var UsersEntity $usersEntity */
        $usersEntity = $this->entityFactory->get(UsersEntity::class);

        /** @var OrderStatusEntity $ordersStatusEntity */
        $ordersStatusEntity = $this->entityFactory->get(OrderStatusEntity::class);

        /** @var CurrenciesEntity $currenciesEntity */
        $currenciesEntity = $this->entityFactory->get(CurrenciesEntity::class);
        
        if (!($order = $ordersEntity->get(intval($orderId)))) {
            return false;
        }
        
        $purchases = $this->ordersLogic->getOrderPurchases($order->id);
        $this->design->assign('purchases', $purchases);
        
        // Способ доставки
        $delivery = $deliveriesEntity->get($order->delivery_id);
        $this->design->assign('delivery', $delivery);
        
        // Пользователь
        $user = $usersEntity->get(intval($order->user_id));
        $this->design->assign('user', $user);
        
        $this->design->assign('order', $order);

        $orderStatuses = $ordersStatusEntity->find(["status"=>intval($order->status_id)]);
        $this->design->assign('order_status', reset($orderStatuses));
        
        // В основной валюте
        $this->design->assign('main_currency', $currenciesEntity->getMainCurrency());

        // Перевод админки
        $backendTranslations = $this->backendTranslations;
        $file = "backend/lang/".$this->settings->email_lang.".php";
        if (!file_exists($file)) {
            foreach (glob("backend/lang/??.php") as $f) {
                $file = "backend/lang/".pathinfo($f, PATHINFO_FILENAME).".php";
                break;
            }
        }
        require($file);
        $this->design->assign('btr', $backendTranslations);

        // Отправляем письмо
        $emailTemplate = $this->design->fetch($this->rootDir.'backend/design/html/email/email_order_admin.tpl');

        $subject = $this->design->get_var('subject');
        
        $this->email($this->settings->order_email, $subject, $emailTemplate, $this->settings->notify_from_email);
    }

    /*Отправка емейла о комментарии администратору*/
    public function emailCommentAdmin($commentId)
    {

        /** @var CommentsEntity $commentsEntity */
        $commentsEntity = $this->entityFactory->get(CommentsEntity::class);
        
        /** @var ProductsEntity $productsEntity */
        $productsEntity = $this->entityFactory->get(ProductsEntity::class);
        
        /** @var BlogEntity $blogEntity */
        $blogEntity = $this->entityFactory->get(BlogEntity::class);
        
        if (!($comment = $commentsEntity->get(intval($commentId)))) {
            return false;
        }
        
        if ($comment->type == 'product') {
            $comment->product = $productsEntity->get(intval($comment->object_id));
        } elseif ($comment->type == 'blog') {
            $comment->post = $blogEntity->get(intval($comment->object_id));
        } elseif ($comment->type == 'news') {
            $comment->post = $blogEntity->get(intval($comment->object_id));
        }
        
        $this->design->assign('comment', $comment);
        // Перевод админки
        $backendTranslations = $this->backendTranslations;
        $file = "backend/lang/".$this->settings->email_lang.".php";
        if (!file_exists($file)) {
            foreach (glob("backend/lang/??.php") as $f) {
                $file = "backend/lang/".pathinfo($f, PATHINFO_FILENAME).".php";
                break;
            }
        }
        require($file);
        $this->design->assign('btr', $backendTranslations);
        // Отправляем письмо
        $email_template = $this->design->fetch($this->rootDir.'backend/design/html/email/email_comment_admin.tpl');
        $subject = $this->design->get_var('subject');
        $this->email($this->settings->comment_email, $subject, $email_template, $this->settings->notify_from_email);
    }

    /*Отправка емейла администратору о заказе обратного звонка*/
    public function emailCallbackAdmin($callbackId)
    {
        /** @var CallbacksEntity $callbacksEntity */
        $callbacksEntity = $this->entityFactory->get(CallbacksEntity::class);
        
        if (!($callback = $callbacksEntity->get(intval($callbackId)))) {
            return false;
        }
        $this->design->assign('callback', $callback);
        $backendTranslations = $this->backendTranslations;
        $file = "backend/lang/".$this->settings->email_lang.".php";
        if (!file_exists($file)) {
            foreach (glob("backend/lang/??.php") as $f) {
                $file = "backend/lang/".pathinfo($f, PATHINFO_FILENAME).".php";
                break;
            }
        }
        require($file);
        $this->design->assign('btr', $backendTranslations);
        // Отправляем письмо
        $email_template = $this->design->fetch($this->rootDir.'backend/design/html/email/email_callback_admin.tpl');
        $subject = $this->design->get_var('subject');
        $this->email($this->settings->comment_email, $subject, $email_template, "$callback->name <$callback->phone>", "$callback->name <$callback->phone>");
    }

    /*Отправка емейла с ответом на комментарий клиенту*/
    public function emailCommentAnswerToUser($commentId)
    {

        /** @var CommentsEntity $commentsEntity */
        $commentsEntity = $this->entityFactory->get(CommentsEntity::class);

        /** @var TranslationsEntity $translationsEntity */
        $translationsEntity = $this->entityFactory->get(TranslationsEntity::class);

        /** @var ProductsEntity $productsEntity */
        $productsEntity = $this->entityFactory->get(ProductsEntity::class);

        /** @var BlogEntity $blogEntity */
        $blogEntity = $this->entityFactory->get(BlogEntity::class);
        
        if(!($comment = $commentsEntity->get(intval($commentId)))
                || !($parentComment = $commentsEntity->get(intval($comment->parent_id)))
                || !$parentComment->email) {
            return false;
        }

        $templateDir = $this->design->getTemplatesDir();
        $compiledDir = $this->design->getCompiledDir();
        $this->design->setTemplatesDir('design/'.$this->templateConfig->getTheme().'/html');
        $this->design->setCompiledDir('compiled/' . $this->templateConfig->getTheme());
        
        /*lang_modify...*/
        if (!empty($parentComment->lang_id)) {
            $currentLangId = $this->languages->getLangId();
            $this->languages->setLangId($parentComment->lang_id);

            $this->settings->initSettings();
            $this->design->assign('settings', $this->settings);
            $this->design->assign('lang', $translationsEntity->find(array('lang_id'=>$parentComment->lang_id)));
        }
        /*/lang_modify...*/

        if ($comment->type == 'product') {
            $comment->product = $productsEntity->get(intval($comment->object_id));
        } elseif ($comment->type == 'blog') {
            $comment->post = $blogEntity->get(intval($comment->object_id));
        } elseif ($comment->type == 'news') {
            $comment->post = $blogEntity->get(intval($comment->object_id));
        }

        $this->design->assign('comment', $comment);
        $this->design->assign('parent_comment', $parentComment);

        // Отправляем письмо
        $emailTemplate = $this->design->fetch($this->rootDir.'design/'.$this->templateConfig->getTheme().'/html/email/email_comment_answer_to_user.tpl');
        $subject = $this->design->get_var('subject');
        $from = ($this->settings->notify_from_name ? $this->settings->notify_from_name." <".$this->settings->notify_from_email.">" : $this->settings->notify_from_email);
        $this->email($parentComment->email, $subject, $emailTemplate, $from, $from);

        $this->design->setTemplatesDir($templateDir);
        $this->design->setCompiledDir($compiledDir);
        
        /*lang_modify...*/
        if (!empty($currentLangId)) {
            $this->languages->setLangId($currentLangId);
            $this->settings->initSettings();
            $this->design->assign('settings', $this->settings);
        }
        /*/lang_modify...*/
    }

    /*Отправка емейла о восстановлении пароля клиенту*/
    public function emailPasswordRemind($userId, $code)
    {
        /** @var UsersEntity $usersEntity */
        $usersEntity = $this->entityFactory->get(UsersEntity::class);

        /** @var TranslationsEntity $translationsEntity */
        $translationsEntity = $this->entityFactory->get(TranslationsEntity::class);
        
        if(!($user = $usersEntity->get(intval($userId)))) {
            return false;
        }

        $currentLangId = $this->languages->getLangId();

        $this->settings->initSettings();
        $this->design->assign('settings', $this->settings);
        $this->design->assign('lang', $translationsEntity->find(['lang_id'=>$currentLangId]));
        
        $this->design->assign('user', $user);
        $this->design->assign('code', $code);
        
        // Отправляем письмо
        $email_template = $this->design->fetch($this->rootDir.'design/'.$this->templateConfig->getTheme().'/html/email/email_password_remind.tpl');
        $subject = $this->design->get_var('subject');
        $from = ($this->settings->notify_from_name ? $this->settings->notify_from_name." <".$this->settings->notify_from_email.">" : $this->settings->notify_from_email);
        $this->email($user->email, $subject, $email_template, $from);
        
        $this->design->smarty->clearAssign('user');
        $this->design->smarty->clearAssign('code');
    }

    /*Отправка емейла о заявке с формы обратной связи администратору*/
    public function emailFeedbackAdmin($feedbackId)
    {

        /** @var UsersEntity $feedbackEntity */
        $feedbackEntity = $this->entityFactory->get(FeedbacksEntity::class);
        
        if (!($feedback = $feedbackEntity->get(intval($feedbackId)))) {
            return false;
        }
        
        $this->design->assign('feedback', $feedback);
        // Перевод админки
        $backendTranslations = $this->backendTranslations;
        $file = "backend/lang/".$this->settings->email_lang.".php";
        if (!file_exists($file)) {
            foreach (glob("backend/lang/??.php") as $f) {
                $file = "backend/lang/".pathinfo($f, PATHINFO_FILENAME).".php";
                break;
            }
        }
        require($file);
        $this->design->assign('btr', $backendTranslations);
        // Отправляем письмо
        $email_template = $this->design->fetch($this->rootDir.'backend/design/html/email/email_feedback_admin.tpl');
        $subject = $this->design->get_var('subject');
        $this->email($this->settings->comment_email, $subject, $email_template, "$feedback->name <$feedback->email>", "$feedback->name <$feedback->email>");
    }

    /*Отправка емейла с ответом на заявку с формы обратной связи клиенту*/
    public function emailFeedbackAnswerFoUser($comment_id,$text)
    {

        /** @var FeedbacksEntity $feedbackEntity */
        $feedbackEntity = $this->entityFactory->get(FeedbacksEntity::class);

        /** @var TranslationsEntity $translationsEntity */
        $translationsEntity = $this->entityFactory->get(TranslationsEntity::class);
        
        if(!($feedback = $feedbackEntity->get(intval($comment_id)))) {
            return false;
        }

        $templateDir = $this->design->getTemplatesDir();
        $compiledDir = $this->design->getCompiledDir();
        $this->design->setTemplatesDir('design/'.$this->templateConfig->getTheme().'/html');
        $this->design->setCompiledDir('compiled/' . $this->templateConfig->getTheme());
        
        /*lang_modify...*/
        if (!empty($feedback->lang_id)) {
            $currentLangId = $this->languages->getLangId();
            $this->languages->setLangId($feedback->lang_id);

            $this->design->assign('lang', $translationsEntity->find(['lang_id'=>$feedback->lang_id]));
        }
        /*/lang_modify...*/

        $this->design->assign('feedback', $feedback);
        $this->design->assign('text', $text);

        // Отправляем письмо
        $email_template = $this->design->fetch($this->rootDir.'design/'.$this->templateConfig->getTheme().'/html/email/email_feedback_answer_to_user.tpl');
        $subject = $this->design->get_var('subject');
        $from = ($this->settings->notify_from_name ? $this->settings->notify_from_name." <".$this->settings->notify_from_email.">" : $this->settings->notify_from_email);
        $this->email($feedback->email, $subject, $email_template, $from, $from);

        $this->design->setTemplatesDir($templateDir);
        $this->design->setCompiledDir($compiledDir);
        
        /*lang_modify...*/
        if (!empty($currentLangId)) {
            $this->languages->setLangId($currentLangId);
        }
        /*/lang_modify...*/
    }

    /*Отправка емейла на восстановление пароля администратора*/
    public function passwordRecoveryAdmin($email, $code)
    {
        if(empty($email) || empty($code)){
            return false;
        }

        // Перевод админки
        $backendTranslations = $this->backendTranslations;
        $file = "backend/lang/".$this->settings->email_lang.".php";
        if (!file_exists($file)) {
            foreach (glob("backend/lang/??.php") as $f) {
                $file = "backend/lang/".pathinfo($f, PATHINFO_FILENAME).".php";
                break;
            }
        }
        require($file);
        $this->design->assign('btr', $backendTranslations);
        $this->design->assign('code',$code);
        $this->design->assign('recovery_url', 'backend/index.php?module=AuthAdmin&code='.$code);
        $email_template = $this->design->fetch($this->rootDir.'backend/design/html/email/email_admin_recovery.tpl');
        $subject = $this->design->get_var('subject');
        $from = ($this->settings->notify_from_name ? $this->settings->notify_from_name." <".$this->settings->notify_from_email.">" : $this->settings->notify_from_email);
        $this->email($email, $subject, $email_template, $from, $from);
        return true;

    }
    
}
