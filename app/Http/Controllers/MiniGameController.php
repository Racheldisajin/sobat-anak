<?php
namespace App\Http\Controllers;
use App\Models\Reward;
class MiniGameController extends Controller { public function index(){ return view('pages.mini-games',[ 'rewards'=>Reward::all() ]); }}
