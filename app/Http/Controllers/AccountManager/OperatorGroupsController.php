<?php

namespace App\Http\Controllers\AccountManager;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\OperatorGroups;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Validator;

class OperatorGroupsController extends Controller  {

    /**
     * Группы операторов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function groups()
    {
        $groups = OperatorGroups::all();

        return view('accountManager.operatorGroups.index', [ 'groups' => $groups ]);
    }

    /**
     * Вызов форми для создания группы операторов
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('accountManager.operatorGroups.create');
    }

    /**
     * Сохранение группы операторов
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json($validator);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        $group = new OperatorGroups();
        $group->name = $request->input('name');
        $group->save();

        if($request->ajax()){
            return response()->json('reload');
        } else {
            return redirect()->route('accountManager.operatorGroups.list');
        }
    }

    /**
     * Удаление группы операторов
     *
     * @param $group_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function delete($group_id, Request $request)
    {
        OperatorGroups::where('id', '=', $group_id)->first()->delete();

        if($request->ajax()){
            return response()->json('groupDeleted');
        } else {
            return redirect()->route('accountManager.operatorGroups.list');
        }
    }

    /**
     * Просмотр операторов в группе
     *
     * @param $group_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function operators($group_id)
    {
        $group = OperatorGroups::find($group_id);
        $operators = $group->operators()->get();

        return view('accountManager.operatorGroups.operatorList', [ 'group' => $group, 'operators' => $operators ]);
    }

    /**
     * Страница добавления операторов в группу
     *
     * @param $group_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addOperators($group_id) {
        $group = OperatorGroups::find($group_id);

        $operatorRole = Sentinel::findRoleBySlug('operator');

        $operators = $operatorRole->users()->whereNotIn('id', $group->operators()->get()->lists('id'))->get();

        return view('accountManager.operatorGroups.addOperators', [ 'group' => $group, 'operators' => $operators ]);
    }

    /**
     * Добавление оператора в группу
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function putOperator(Request $request) {
        $operator_id = $request->input('operator_id');
        $group_id = $request->input('group_id');

        $group = OperatorGroups::find($group_id);

        if($group->id) {
            $group->operators()->attach($operator_id);

            if($request->ajax()){
                return response()->json('agentAdded');
            } else {
                return redirect()->route('accountManager.operatorGroups.addOperators');
            }
        }
        return response()->json();
    }

    /**
     * Удаление оператора из группы
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deleteOperator(Request $request)
    {
        $operator_id = $request->input('operator_id');
        $group_id = $request->input('group_id');

        $group = OperatorGroups::find($group_id);

        if($group->id) {
            $group->operators()->detach($operator_id);

            if($request->ajax()){
                return response()->json('agentDeleted');
            } else {
                return redirect()->route('accountManager.operatorGroups.operators');
            }
        }
        return response()->json();
    }
}