<?php

namespace Modules\Core\Http\Controllers\Api;

use Modules\Core\Http\Controllers\BasePublicController;
use Mockery\CountValidator\Exception;
use Illuminate\Support\Facades\Auth;
use Validator;

class BaseApiController extends BasePublicController
{
    private $permissions;
    private $user;

    public function __construct()
    {
    }

    //Return params from Request
    public function getParamsRequest($request, $params = [])
    {
        $defaultValues = (object)$params;//Convert to object the params
        //Set default values
        $default = (object)[
            "page" => $defaultValues->page ?? false,
            "take" => $defaultValues->take ?? false,
            "filter" => $defaultValues->filter ?? [],
            'include' => $defaultValues->include ?? [],
            'fields' => $defaultValues->fields ?? []
        ];

        //Return params
        $params = (object)[
            "page" => is_numeric($request->input('page')) ? $request->input('page') : $default->page,
            "take" => is_numeric($request->input('take')) ? $request->input('take') :
                ($request->input('page') ? 12 : $default->take),
            "filter" => json_decode($request->input('filter')) ?? (object)$default->filter,
            "include" => $request->input('include') ? explode(",", $request->input('include')) : $default->include,
            "fields" => $request->input('fields') ? explode(",", $request->input('fields')) : $default->fields,
        ];
        return $params;//Response
    }

    //Transform data of Paginate
    public function pageTransformer($data)
    {
        return [
            "total" => $data->total(),
            "lastPage" => $data->lastPage(),
            "perPage" => $data->perPage(),
            "currentPage" => $data->currentPage()
        ];
    }

}
