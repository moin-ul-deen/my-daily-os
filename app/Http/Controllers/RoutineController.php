<?php
namespace App\Http\Controllers;
use App\Models\{Task,TaskTemplate};
use App\Services\TaskGenerationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Routine builder: a TaskTemplate is the repeating routine entry; the
 * dated Task rows it generates are what the user actually completes.
 * Editing a template refreshes only future Pending instances - history
 * (past instances, completed work) is never rewritten. Deleting a
 * template keeps every generated instance (they are detached first).
 */
class RoutineController extends Controller {
 public function __construct(private TaskGenerationService $gen) {}
 private function owned(TaskTemplate $t) { abort_unless($t->user_id === auth()->id(), 403); return $t; }
 private function rules(): array { return [
   'title'=>'required|string|max:255','description'=>'nullable|string','category'=>'required|string|max:100',
   'start_time'=>'nullable|string','end_time'=>'nullable|string','priority'=>'nullable|in:Low,Medium,High,Urgent',
   'recurrence_type'=>'required|in:daily,weekly,weekdays,monthly,custom','recurrence_days'=>'nullable|array',
   'recurrence_days.*'=>'integer|between:0,6','recurrence_interval'=>'nullable|integer|min:1|max:52',
   'starts_on'=>'nullable|date','ends_on'=>'nullable|date|after_or_equal:starts_on',
   'reminder_minutes'=>'nullable|integer|min:0','is_active'=>'nullable|boolean',
 ]; }
 public function index() {
   $templates = TaskTemplate::where('user_id', auth()->id())->orderByDesc('created_at')->get();
   $previews = [];
   foreach ($templates as $tpl) $previews[$tpl->id] = $this->gen->previewDates($tpl, Carbon::today(), 7);
   return view('routine.index', compact('templates','previews'));
 }
 public function store(Request $r) {
   $v = $r->validate($this->rules());
   $v['starts_on'] = $v['starts_on'] ?? now()->toDateString();
   $tpl = TaskTemplate::create(array_merge($v, ['user_id'=>auth()->id(), 'priority'=>$v['priority'] ?? 'Medium', 'recurrence_interval'=>$v['recurrence_interval'] ?? 1]));
   $this->gen->generate($tpl, Carbon::parse($tpl->starts_on), 30);
   return redirect()->route('routine.index')->with('success','Routine saved - your planned activities have been added to your days.');
 }
 public function edit(TaskTemplate $template) { $this->owned($template); $item = $template; $tpl = $template; return view('routine.edit', compact('template','item','tpl')); }
 public function update(Request $r, TaskTemplate $template) {
   $this->owned($template);
   $v = $r->validate($this->rules());
   if (empty($v['starts_on'])) $v['starts_on'] = $template->starts_on->toDateString();
   $template->update($v);
   $this->gen->refreshFuture($template);
   return redirect()->route('routine.index')->with('success','Routine updated - future days now follow the new plan. Past days were not changed.');
 }
 public function destroy($template) {
   $template = TaskTemplate::withTrashed()->find($template);
   if (!$template) return redirect()->route('routine.index')->with('success','Already deleted');
   $this->owned($template);
   if ($template->trashed()) return redirect()->route('routine.index')->with('success','Already deleted');
   DB::transaction(function () use ($template) {
     // Detach every generated instance first (including soft-deleted ones):
     // deleting a routine must never delete the user's activity history.
     Task::withTrashed()->where('task_template_id', $template->id)->update(['task_template_id' => null]);
     $template->delete();
   });
   return redirect()->route('routine.index')->with('success','Routine deleted - your past activities were kept.');
 }
}
