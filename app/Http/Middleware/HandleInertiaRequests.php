<?php
namespace App\Http\Middleware;
use Inertia\Middleware;
class HandleInertiaRequests extends Middleware { protected $rootView='app'; public function share($request){return array_merge(parent::share($request),['auth'=>['user'=>fn()=> $request->user()?->only('id','name','email','role')],'flash'=>['success'=>fn()=>$request->session()->get('success')]]);} }
