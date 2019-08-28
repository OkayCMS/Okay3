<?php

namespace Okay\Modules\Integration1C\Integration\Export;


use Aura\SqlQuery\QueryFactory;
use Okay\Core\Database;
use Okay\Core\EntityFactory;
use Okay\Core\Settings;
use Okay\Modules\Integration1C\Integration\Integration1C;

abstract class AbstractExport
{

    /** @var Integration1C */
    protected $integration1C;
    
    public function __construct(Integration1C $integration1C)
    {
        $this->integration1C = $integration1C;
    }
    
    /**
     * @return string
     */
    abstract public function export();
    
}
