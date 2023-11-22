<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;

class AddTenancyController extends Controller
{
    public function index(){
        return view('admin-tenancy.function.add-tenancy');
    }
    public function create_action(Request $request){
        $domain = $request->input('domain');
        $plan = $request->input('plan');
      
        $tenant = Tenant::create([
          'domain' => $domain,
          'plan' => $plan,
        ]);
      
        $tenant->domains()->create(['domain' => $domain . '.localhost']);
      
        return view('admin-tenancy.function.add-tenancy');
      }
    
}
