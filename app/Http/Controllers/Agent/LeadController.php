<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\SphereMask;
use Validator;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\LeadPhone;
use App\Models\Sphere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
//use App\Http\Requests\Admin\ArticleRequest;

class LeadController extends Controller {

    public function __construct()
    {
        $this->uid = \Sentinel::getUser()->id;
        view()->share('type', 'article');
    }
     /*
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index()
    {
        // Show the page
        return view('agent.lead.index');
    }

    public function deposited(){
        $leads = Agent::find($this->uid)->leads()->with('phone')->get();
        return view('agent.lead.deposited')->with('leads',$leads);
    }

    public function obtain(){
        $agent = Agent::with('spheres.leads')->find($this->uid);
        $leads=$agent->spheres()->first()->leads()->get();
        $mask = new SphereMask($agent->spheres()->first()->id);
        $mask->setUserID($this->uid);

        $var = $mask->obtain();
        return view('agent.lead.obtain')->with('leads',$leads);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $spheres = Sphere::active()->lists('name','id');
        return view('agent.lead.create')->with('lead',[])->with('spheres',$spheres);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|regex:/\(?([0-9]{3})\)?([\s.-])*([0-9]{3})([\s.-])*([0-9]{4})/',
            'name' => 'required'
        ]);
        $agent = Agent::with('sphereLink')->findOrFail($this->uid);

        if ($validator->fails() || !$agent->sphereLink) {
            if($request->ajax()){
                return response()->json($validator);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }


        $phone = LeadPhone::firstOrCreate(['phone'=>preg_replace('/[^\d]/','',$request->input('phone'))]);

        $lead = new Lead($request->except('phone'));
        $lead->phone_id=$phone->id;
        $lead->date=date('Y-m-d');

        $agent->leads()->save($lead);

        if($request->ajax()){
            return response()->json();
        } else {
            return redirect()->route('agent.lead.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return Response
     */
    public function destroy($id)
    {
        Agent::findOrFail($this->uid)->leads()->whereIn([$id])->delete();
        return response()->route('agent.lead.index');
    }



}
