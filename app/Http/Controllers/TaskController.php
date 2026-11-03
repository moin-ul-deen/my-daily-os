<?php
namespace App\Http\Controllers;
use App\Models\{Task,TaskTemplate,TaskCompletion};
use App\Services\TaskGenerationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TaskController extends Controller {
 private function owned(Task $t){ abort_unless($t->user_id===auth()->id(),403); return $t; }
 public function index(Request $r){ $q=Task::where('user_id',auth()->id()); if($r->date) $q->whereDate('date',$r->date); if($r->category) $q->where('category',$r->category); if($r->search) $q->where('title','like','%'.$r->search.'%'); $tasks=$q->orderBy('date')->orderBy('start_time')->paginate(30); return view('tasks.index',compact('tasks')); }
 public function create(){ return view('tasks.create'); }
 public function store(Request $r, TaskGenerationService $gen){ $v=$r->validate(['title'=>'required|string|max:255','description'=>'nullable|string','category'=>'required|string','date'=>'required|date','start_time'=>'nullable','end_time'=>'nullable','priority'=>'required|in:Low,Medium,High,Urgent','status'=>'nullable|in:Pending,In progress,Completed,Skipped,Cancelled','reminder_minutes'=>'nullable|integer','recurrence_type'=>'nullable|in:none,daily,weekly,weekdays,monthly,custom','recurrence_days'=>'nullable|array','recurrence_interval'=>'nullable|integer|min:1']);
   if(!empty($v['recurrence_type']) && $v['recurrence_type']!=='none'){ $tpl=TaskTemplate::create(['user_id'=>auth()->id(),'title'=>$v['title'],'description'=>$v['description']??null,'category'=>$v['category'],'start_time'=>$v['start_time']??null,'end_time'=>$v['end_time']??null,'priority'=>$v['priority'],'recurrence_type'=>$v['recurrence_type'],'recurrence_days'=>$v['recurrence_days']??null,'recurrence_interval'=>$v['recurrence_interval']??1,'starts_on'=>$v['date'],'reminder_minutes'=>$v['reminder_minutes']??null]);
     // Generate 30 dated instances ahead via the shared service (same logic as the routine builder).
     $gen->generate($tpl, Carbon::parse($v['date']), 30);
   } else { Task::create(array_merge($v,['user_id'=>auth()->id(),'status'=>$v['status']??'Pending'])); }
   return redirect()->route('tasks.index')->with('success','Task saved'); }
 public function edit(Task $task){ $this->owned($task); return view('tasks.edit',compact('task')); }
 public function update(Request $r, Task $task){ $this->owned($task); $v=$r->validate(['title'=>'required|string|max:255','description'=>'nullable|string','category'=>'required|string','date'=>'required|date','start_time'=>'nullable','end_time'=>'nullable','priority'=>'required|in:Low,Medium,High,Urgent','status'=>'required|in:Pending,In progress,Completed,Skipped,Cancelled']); $task->update($v); if($v['status']==='Completed' && !$task->completed_at){ $task->update(['completed_at'=>now()]); TaskCompletion::create(['user_id'=>auth()->id(),'task_id'=>$task->id,'completed_at'=>now()]); } return redirect()->route('tasks.index')->with('success','Task updated'); }
 public function destroy($task){ $task=Task::withTrashed()->find($task); if(!$task) return redirect()->route('tasks.index')->with('success','Already deleted'); $this->owned($task); if($task->trashed()) return redirect()->route('tasks.index')->with('success','Already deleted');
   // Soft delete: the row (and its TaskCompletion history) stays in the database.
   // Resolving withTrashed() above makes repeat clicks land here instead of a 404.
   DB::transaction(function() use ($task){ $task->delete(); });
   return redirect()->route('tasks.index')->with('success','Task deleted'); }
 public function complete(Task $task){ $this->owned($task); $task->update(['status'=>'Completed','completed_at'=>now()]); TaskCompletion::firstOrCreate(['user_id'=>auth()->id(),'task_id'=>$task->id],['completed_at'=>now()]); return back(); }
 public function reopen(Task $task){ $this->owned($task); $task->update(['status'=>'Pending','completed_at'=>null]); return back(); }
 public function postpone(Task $task){ $this->owned($task); $task->update(['date'=>Carbon::parse($task->date)->addDay()]); return back(); }
 public function duplicate(Task $task){ $this->owned($task); $n=$task->replicate(); $n->status='Pending'; $n->completed_at=null; $n->save(); return back()->with('success','Duplicated'); }
}
