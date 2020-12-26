<?php

namespace Modules\Core\Http\Controllers\Api;

use Modules\Core\Http\Controllers\BasePublicController;
use Modules\Core\Http\Controllers\Api\PermissionsApiController;
use Mockery\CountValidator\Exception;
use Illuminate\Support\Facades\Auth;
use Modules\Iprofile\Entities\Role;
use Validator;

class BaseApiController extends BasePublicController
{
    private $permissions;
    private $permissionsController;
    private $user;

    public function __construct()
    {
    }

    //Return params from Request
    public function getParamsRequest($request, $params = [])
    {
        $defaultValues = (object)$params;//Convert to object the params
        $this->permissionsController = new PermissionsApiController();

        //Set default values
        $default = (object)[
            "page" => $defaultValues->page ?? false,
            "take" => $defaultValues->take ?? false,
            "filter" => $defaultValues->filter ?? [],
            'include' => $defaultValues->include ?? [],
            'fields' => $defaultValues->fields ?? []
        ];

        // set current auth user
        $this->user = Auth::user();
        $roles = $this->user ? $this->user->roles()->get() : false;//Role data
        $role = ($roles && isset($setting->roleId)) ? $roles->where("id", $setting->roleId)->first() : false;

        //Return params
        $params = (object)[
            "page" => is_numeric($request->input('page')) ? $request->input('page') : $default->page,
            "take" => is_numeric($request->input('take')) ? $request->input('take') :
                ($request->input('page') ? 12 : $default->take),
            "filter" => json_decode($request->input('filter')) ?? (object)$default->filter,
            "include" => $request->input('include') ? explode(",", $request->input('include')) : $default->include,
            "fields" => $request->input('fields') ? explode(",", $request->input('fields')) : $default->fields,
            'role' => $role,
            'roles' => $roles,
            "user" => $this->user
        ];
        return $params;//Response
    }

    //Validate if response Api is successful
    public function validateResponseApi($response)
    {
        //Get response
        $data = json_decode($response->content());

        //If there is errors, throw error
        if (isset($data->errors))
            throw new Exception($data->errors, $response->getStatusCode());
        else {//if response is successful, return response
            return $data->data;
        }
    }

    //Validate if fields are validated according to rules
    public function validateRequestApi($request)
    {
        //Create Validator
        $validator = Validator::make($request->all(), $request->rules());

        //if get errors, throw errors
        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            throw new Exception(json_encode($errors), 400);
        } else {//if vlaidation is sucessful, return true
            return true;
        }
    }

    //Validate if user has permission
    public function validatePermission($request, $permissionName)
    {
        //Get permissions
        $this->permissionsController = new PermissionsApiController();
        $permissions = $this->permissionsController->getAll($request);

        //Validate permissions
        if ($permissions && !isset($permissions[$permissionName]))
            throw new \Exception('Permission Denied', 403);
    }

    //Validate if code is like status response, and return status code
    public function getStatusError($code = false)
    {
        switch ($code) {
            case 204:
                return 204;
                break;
            case 400: //Bad Request
                return 400;
                break;
            case 401:
                return 401;
                break;
            case 403:
                return 403;
                break;
            case 404:
                return 404;
                break;
            case 502:
                return 502;
                break;
            case 504:
                return 504;
                break;
            default:
                return 500;
                break;
        }
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

    //Generate password
    public function generatePassword($length = 12, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if (strpos($available_sets, 'l') !== false)
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if (strpos($available_sets, 'u') !== false)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if (strpos($available_sets, 'd') !== false)
            $sets[] = '23456789';
        if (strpos($available_sets, 's') !== false)
            $sets[] = '!@#$%&*?/_-+';
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++)
            $password .= $all[array_rand($all)];
        $password = str_shuffle($password);
        if (!$add_dashes)
            return $password;
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }

}
