<?php


namespace Okay\Helpers;


use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Core\Notify;
use Okay\Core\Response;
use Okay\Entities\CommentsEntity;
use Okay\Requests\CommonRequest;

class CommentsHelper
{

    private $entityFactory;
    private $commentsRequest;
    private $validateHelper;
    private $design;
    private $notify;
    private $language;

    public function __construct(
        EntityFactory $entityFactory,
        CommonRequest $commentsRequest,
        ValidateHelper $validateHelper,
        Design $design,
        Notify $notify,
        MainHelper $mainHelper
    ) {
        $this->entityFactory = $entityFactory;
        $this->commentsRequest = $commentsRequest;
        $this->validateHelper = $validateHelper;
        $this->design = $design;
        $this->notify = $notify;
        $this->language = $mainHelper->getCurrentLanguage();
    }

    /**
     * Метод возвращает комментарии для товаров или записей блога
     * 
     * @param string $objectType
     * @param int $objectId
     * @return array
     * @throws \Exception
     */
    public function getCommentsList($objectType, $objectId)
    {

        /** @var CommentsEntity $commentsEntity */
        $commentsEntity = $this->entityFactory->get(CommentsEntity::class);
        
        // Комментарии к посту
        $comments = $commentsEntity->mappedBy('id')->find([
            'has_parent' => false,
            'type' => $objectType,
            'object_id' => $objectId,
            'approved' => 1,
            'ip' => $_SERVER['REMOTE_ADDR'],
        ]);
        
        $childrenFilter = [
            'has_parent' => true,
            'type' => $objectType,
            'object_id' => $objectId,
            'approved' => 1,
            'ip' => $_SERVER['REMOTE_ADDR'],
        ];
        
        foreach ($commentsEntity->find($childrenFilter) as $c) {
            if (isset($comments[$c->parent_id])) {
                $comments[$c->parent_id]->children[$c->id] = $c;
            }
        }

        return ExtenderFacade::execute(__METHOD__, $comments, func_get_args());
    }
    
    public function addCommentProcedure($objectType, $objectId)
    {
        if (($comment = $this->commentsRequest->postComment()) !== null) {
            if ($error = $this->validateHelper->getCommentValidateError($comment)) {
                $this->design->assign('error', $error);
            } else {

                /** @var CommentsEntity $commentsEntity */
                $commentsEntity = $this->entityFactory->get(CommentsEntity::class);
                
                // Создаем комментарий
                $comment->object_id = $objectId;
                $comment->type      = $objectType;
                $comment->ip        = $_SERVER['REMOTE_ADDR'];
                $comment->lang_id   = $this->language->id;
                
                // Добавляем комментарий в базу
                $commentId = $commentsEntity->add($comment);
                // Отправляем email
                $this->notify->emailCommentAdmin($commentId);
                
                ExtenderFacade::execute(__METHOD__, $commentId, func_get_args());
                
                Response::redirectTo($_SERVER['REQUEST_URI'].'#comment_'.$commentId);
            }
        }
    }
}