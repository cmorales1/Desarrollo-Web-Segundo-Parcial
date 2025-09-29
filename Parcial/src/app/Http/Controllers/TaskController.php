<?php
namespace App\Http\Controllers;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller {
    public function index(){ return Task::latest()->get(); }
    public function store(Request $r){ $d=$r->validate(['title'=>'required']); return Task::create($d); }
    public function show(Task $task){ return $task; }
    public function update(Request $r, Task $task){ $d=$r->validate(['title'=>'required']); $task->update($d); return $task; }
    public function destroy(Task $task){ $task->delete(); return response()->noContent(); }
}