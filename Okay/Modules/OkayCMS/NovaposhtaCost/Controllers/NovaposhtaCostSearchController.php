<?php


namespace Okay\Modules\OkayCMS\NovaposhtaCost\Controllers;


use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Modules\OkayCMS\NovaposhtaCost\Entities\NPCitiesEntity;

class NovaposhtaCostSearchController
{
    
    public function findCity(Request $request, Response $response, NPCitiesEntity $citiesEntity)
    {
        
        $filter['keyword'] = $request->get('query', 'string');
        $filter['limit'] = 10;
        
        $cities = $citiesEntity->find($filter);

        $suggestions = [];
        if (!empty($cities)) {
            foreach ($cities as $city) {
                $suggestion = new \stdClass();

                $suggestion->value = $city->name;
                $suggestion->data = $city;
                $suggestions[] = $suggestion;
            }
        }

        $res = new \stdClass;
        $res->query = $filter['keyword'];
        $res->suggestions = $suggestions;

        $response->setContent(json_encode($res), RESPONSE_JSON);
    }
}
