<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;
use Okay\Core\Image;

class BlogEntity extends Entity
{
    
    protected static $fields = [
        'id',
        'url',
        'visible',
        'date',
        'image',
        'type_post',
        'last_modify',
    ];
    
    protected static $langFields = [
        'name',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'annotation',
        'description',
    ];
    
    protected static $searchFields = [
        'name',
        'meta_keywords',
    ];
    
    protected static $defaultOrderFields = [
        'date DESC',
        'visible DESC',
        'name DESC',
        'id DESC',
    ];

    protected static $table = '__blog';
    protected static $langObject = 'blog';
    protected static $langTable = 'blog';
    protected static $tableAlias = 'b';
    protected static $alternativeIdField = 'url';

    public function delete($ids)
    {
        if (empty($ids)) {
            return false;
        }
        
        $ids = (array)$ids;

        $postTypes = $this->cols(['type_post'])->find(['id' => $ids]);
        
        $comments = $this->entity->get(CommentsEntity::class);
        $commentsIds = $comments->cols(['id'])->find([
            'type' => $postTypes,
            'object_id' => $ids,
        ]);

        $comments->delete($commentsIds);

        /** @var Image $imageCore */
        $imageCore = $this->serviceLocator->getService(Image::class);

        $ids = (array)$ids;
        foreach ($ids as $id) {
            $imageCore->deleteImage(
                $id,
                'image',
                self::class,
                $this->config->original_blog_dir,
                $this->config->resized_blog_dir
            );
        }
        
        if (in_array('news', $postTypes)) {
            $this->settings->lastModifyNews = date("Y-m-d H:i:s");
        }
        if (in_array('blog', $postTypes)) {
            $this->settings->lastModifyPosts = date("Y-m-d H:i:s");
        }
        
        parent::delete($ids);
    }

    /*Выбираем следующию запись от текущей*/
    public function getNextPost($id)
    {
        $id = (int)$id;
        
        $res = $this->cols([
            'date',
            'type_post',
        ])->get($id);

        $select = $this->queryFactory->newSelect();
        $select->cols([
            'id',
        ])->from('__blog')
            ->where('(date = :date AND id > :id OR date > :date2)')
            ->where('visible = 1')
            ->where('type_post = :type_post')
            ->orderBy([
                'date ASC',
                'id'
            ])
            ->limit(1);
        
        $select->bindValues([
            'date' => $res->date,
            'id' => $id,
            'type_post' => $res->type_post,
            'date2' => $res->date,
        ]);
        
        $this->db->query($select);
        $nextId = $this->db->result('id');
        
        if($nextId) {
            return $this->get((int)$nextId);
        } else {
            return false;
        }
    }

    /*Выбираем предыдущую запись от текущей*/
    public function getPrevPost($id)
    {
        $id = (int)$id;
        $res = $this->cols([
            'date',
            'type_post',
        ])->get($id);
        
        $select = $this->queryFactory->newSelect();
        $select->cols([
            'id',
        ])->from('__blog')
            ->where('(date = :date AND id < :id OR date < :date2)')
            ->where('visible = 1')
            ->where('type_post = :type_post')
            ->orderBy([
                'date DESC',
                'id'
            ])
            ->limit(1);

        $select->bindValues([
            'date' => $res->date,
            'id' => $id,
            'type_post' => $res->type_post,
            'date2' => $res->date,
        ]);
        
        $this->db->query($select);
        $nextId = $this->db->result('id');

        if($nextId) {
            return $this->get((int)$nextId);
        } else {
            return false;
        }
        
    }

    public function getRelatedProducts($filter = [])
    {
        $select = $this->queryFactory->newSelect();
        $select->from('__related_blogs')
            ->cols([
                'post_id',
                'related_id',
                'position',
            ])
            ->orderBy(['position']);
        
        
        if (!empty($filter['post_id'])) {
            $select->where('post_id IN (:post_id)')
                ->bindValue('post_id', (array)$filter['post_id']);
        }
        if (!empty($filter['product_id'])) {
            $select->where('related_id IN (:related_id)')
                ->bindValue('related_id', (array)$filter['product_id']);
        }
        
        $this->db->query($select);
        return $this->db->results();
    }

    public function addRelatedProduct($postId, $relatedId, $position = 0)
    {
        $insert = $this->queryFactory->newInsert();
        $insert->into('__related_blogs')
            ->cols([
                'post_id',
                'related_id',
                'position',
            ])
            ->bindValues([
                'post_id' => $postId,
                'related_id' => $relatedId,
                'position' => $position,
            ])
            ->ignore();

        $this->db->query($insert);
        return $relatedId;
    }

    public function deleteRelatedProduct($postId, $relatedId = null)
    {
        $delete = $this->queryFactory->newDelete();
        $delete->from('__related_blogs')
            ->where('post_id=:post_id')
            ->bindValue('post_id', (int)$postId);

        if ($relatedId !== null) {
            $delete->where('related_id=:related_id')
                ->bindValue('related_id', (int)$relatedId);
        }
        $this->db->query($delete);
    }
    
}
