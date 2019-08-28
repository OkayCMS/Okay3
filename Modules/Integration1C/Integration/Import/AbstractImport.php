<?php


namespace Okay\Modules\Integration1C\Integration\Import;


use Aura\SqlQuery\QueryFactory;
use Okay\Core\Database;
use Okay\Core\EntityFactory;
use Okay\Core\Settings;
use Okay\Modules\Integration1C\Integration\Integration1C;

abstract class AbstractImport
{

    /** @var Integration1C */
    protected $integration1C;

    public function __construct(Integration1C $integration1C)
    {
        $this->integration1C = $integration1C;
    }
    
    /**
     * @param string $xmlFile Full path to xml file
     * @return string
     */
    abstract public function import($xmlFile);
    
}
