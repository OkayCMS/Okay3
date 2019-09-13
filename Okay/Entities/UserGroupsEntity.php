<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;

class UserGroupsEntity extends Entity
{
    protected static $fields = [
        'id',
        'name',
        'discount',
    ];

    protected static $defaultOrderFields = [
        'discount',
    ];

    protected static $table = '__groups';
    protected static $tableAlias = 'g';

    
    public function delete($ids)
    {
        if (!empty($ids)) {
            $update = $this->queryFactory->newUpdate();
            $update->table('__users')
                ->cols(['group_id' => null])
                ->where('group_id=:group_id')
                ->bindValue('group_id', $ids);

            $this->db->query($update);
        }
        
        parent::delete($ids);
        
        return false;
    }
    
}
