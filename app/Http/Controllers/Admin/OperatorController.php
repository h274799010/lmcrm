<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Http\Requests\AdminUsersEditFormRequest;
use App\Http\Requests\OperatorFormRequest;
use App\Models\AccountManager;
use App\Models\Operator;
use App\Models\OperatorSphere;
use App\Models\Sphere;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Datatables;

class OperatorController extends AdminController {

    public function __construct()
    {
        view()->share('type', 'operator');
    }

    public function index()
    {
        $spheres = Sphere::active()->get();

        $role = Sentinel::findRoleBySlug('account_manager');
        $accountManagers = $role->users()->get();

        // Show the page
        return view('admin.operator.index', [
            'spheres' => $spheres,
            'accountManagers' => $accountManagers
        ]);
    }

    public function data(Request $request)
    {
        // находим роль
        $role = Sentinel::findRoleBySlug('operator');
        $operatorsId = $role->users()->lists('id');

        // Если есть параметры фильтра
        if (count($request->only('filter'))) {
            // Получаем параметры
            $eFilter = $request->only('filter')['filter'];

            $filteredIds = array();

            $operatorsSphereIds = array();
            $operatorsAccIds = array();

            // Пробегаемся по параметрам из фильтра
            //
            foreach ($eFilter as $eFKey => $eFVal) {
                switch($eFKey) {
                    case 'sphere':
                        $operatorsSphereIds = array();
                        if($eFVal) {
                            $sphere = Sphere::find($eFVal);
                            $operatorsSphereIds = $sphere->operators()->get()->pluck('id', 'id')->toArray();
                        }
                        break;
                    case 'accountManager':
                        $operatorsAccIds = array();
                        if($eFVal) {
                            $accountManager = AccountManager::find($eFVal);
                            $operatorsAccIds = $accountManager->operators()->get()->pluck('id', 'id')->toArray();
                        }
                        break;
                    default:
                        break;
                }
            }

            // Обьеденяем id агентов по всем фильтрам
            $tmp = array_merge($operatorsSphereIds, $operatorsAccIds);
            // Убираем повторяющиеся записи (оставляем только уникальные)
            $tmp = array_unique($tmp);

            // Ишем обшие id по всем фильтрам
            foreach ($tmp as $val) {
                $flag = 0;
                if(empty($eFilter['sphere']) || in_array($val, $operatorsSphereIds)) {
                    $flag++;
                }
                if(empty($eFilter['accountManager']) || in_array($val, $operatorsAccIds)) {
                    $flag++;
                }
                if( $flag == 2 ) {
                    $filteredIds[] = $val;
                }
            }
            // Если фильтры не пустые - то применяем их
            if( !empty($eFilter['sphere']) || !empty($eFilter['accountManager']) ) {
                $operatorsId = $filteredIds;
            }
        }

        $operators = OperatorSphere::whereIn('id', $operatorsId)
            ->select('id', 'email', 'first_name', 'last_name', 'created_at');

        return Datatables::of($operators)
            ->remove_column('first_name')
            ->edit_column('last_name', function($model) { return $model->last_name.' '.$model->first_name; })
            ->add_column('spheres', function($model) {
                $operator = OperatorSphere::find($model->id);
                $operatorSpheres = $operator->spheres()->get()->lists('name')->toArray();
                $operatorSpheres = implode(', ', $operatorSpheres);

                return $operatorSpheres;
            })
            ->add_column('actions', function($model) { return view('admin.operator.datatables.control',['id'=>$model->id]); })
            ->remove_column('id')
            ->make();
    }

    /**
     * Подгрузка данных для фильтра в списке операторов
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilterOperator(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');

        $sphere_id = $request->input('sphere_id');
        $accountManager_id = $request->input('accountManager_id');

        $result = array();
        if($id) {
            switch ($type) {
                case 'sphere':
                    $sphere = Sphere::find($id);
                    $result['accountManagers'] = $sphere->accountManagers()->select('users.id', \DB::raw('users.email AS name'))->get();
                    break;
                case 'accountManager':
                    $accountManager = AccountManager::find($id);
                    $result['spheres'] = $accountManager->spheres()->select('spheres.id', 'spheres.name')->get();
                    break;
                default:
                    break;
            }
        } else {
            if(!$sphere_id) {
                $role = Sentinel::findRoleBySlug('account_manager');
                $result['accountManagers'] = $role->users()->select('users.id', \DB::raw('users.email AS name'))->get();
            }

            if(!$accountManager_id) {
                $result['spheres'] = Sphere::active()->get();
            }
        }

        return response()->json($result);
    }

    public function create()
    {
        $spheres = Sphere::active()->lists('name','id');
        return view('admin.operator.create_edit')->with('spheres', $spheres);
    }

    public function store(OperatorFormRequest $request)
    {
        $user=\Sentinel::registerAndActivate($request->except('password_confirmation'));
        $user->update(['password'=>\Hash::make($request->input('password'))]);
        $role = \Sentinel::findRoleBySlug('operator');
        $user->roles()->attach($role);

        $user = OperatorSphere::find($user->id);

        foreach ($request->only('spheres') as $sphere) {
            $user->spheres()->sync($sphere);
        }

        return redirect()->route('admin.operator.index');
    }

    public function edit($id)
    {
        //$operator = Sentinel::findById($id);

        $operator = OperatorSphere::find($id);

        // данные сферы
        $spheres = Sphere::active()->lists('name','id');

        $accountManagers = Sentinel::findRoleBySlug('account_manager')->getUsers();

        return view('admin.operator.create_edit', ['operator'=>$operator, 'spheres' => $spheres, 'accountManagers'=>$accountManagers]);
    }

    public function update( Request $request, $id )
    {
        $operator = OperatorSphere::find($id);

        $password = $request->password;
        $passwordConfirmation = $request->password_confirmation;

        if (!empty($password)) {
            if ($password === $passwordConfirmation) {
                $operator->password = \Hash::make($request->input('password'));
            }
        }

        $operator->first_name = $request->input('first_name');
        $operator->last_name = $request->input('last_name');
        $operator->email = $request->input('email');
        $operator->save();

        $operator = OperatorSphere::find($operator->id);
        $operator->spheres()->sync($request->input('spheres'));
        /*$operator->update($request->except('password','password_confirmation'));
        dd($operator);*/

        return redirect()->route('admin.operator.index');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('admin.operator.index');
    }

    public function attachAccountManagers(Request $request)
    {
        $operator=OperatorSphere::findOrFail($request->input('operator_id'));

        $accountManagers = ( $request->input('accountManagers') ?: [] );

        $operator->accountManagers()->sync( $accountManagers );

        return redirect()->back();
    }

}