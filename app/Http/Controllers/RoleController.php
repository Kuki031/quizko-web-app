<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoleRequest;
use App\Models\Role;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class RoleController extends Controller
{
    public function index()
    {
        try {
            $roles = Role::all();
            return response()->json(["roles" => $roles], 200, ["status" => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function store(CreateRoleRequest $request)
    {
        try {

            $request->validated();
            $role = Role::create($request);

            return response()->json(["role" => $role], 201, ['status' => 'success']);
        } catch (ValidationException $e) {
            return response()->json(["error" => $e->errors()], 400, ['status' => 'fail']);
        } catch (Exception $e) {
            return response()->json(["error" => "Nemoguće je kreirati novu ulogu."], 500, ['status' => 'fail']);
        }
    }

    public function show(string $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) return response()->json(["error" => "Uloga sa ID-em $id ne postoji."], 404, ['status' => 'fail']);

            return response()->json(['role' => $role], 200, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $rules = [
                "role" => "required|unique:roles,role" . $id
            ];

            $validateData = $request->validate($rules);

            $role = Role::where('id', $id)->update($validateData);
            if (!$role) return response()->json(["error" => "Uloga sa ID-em $id ne postoji."], 404, ['status' => 'fail']);
        } catch (ValidationException $e) {
            return response()->json(["error" => $e->errors()], 400, ['status' => 'fail']);
        } catch (Exception $e) {
            return response()->json(["error" => "Nemoguće je ažurirati ulogu."], 500, ['status' => 'fail']);
        }
    }

    public function destroy(string $id)
    {
        try {
            $role = Role::find($id);
            if (!$role) return response()->json(["error" => "Uloga sa ID-em $id ne postoji."], 404, ['status' => 'fail']);
            Role::destroy($id);

            return response()->json([], 204, ['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500, ['status' => 'fail']);
        }
    }
}
