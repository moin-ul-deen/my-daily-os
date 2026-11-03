<?php
namespace App\Http\Controllers; use App\Services\ReportService; use Carbon\Carbon; use Illuminate\Http\Request;
class ReportController extends Controller {
 public function daily(Request $r,ReportService $s){ $d=$r->date?Carbon::parse($r->date):now(); $report=$s->range($r->user(),$d->copy()->startOfDay(),$d->copy()->endOfDay()); return view('reports.daily',compact('report','d')); }
 public function weekly(Request $r,ReportService $s){ $from=now()->startOfWeek(); $to=now()->endOfWeek(); $report=$s->range($r->user(),$from,$to); $prev=$s->range($r->user(),$from->copy()->subWeek(),$to->copy()->subWeek()); return view('reports.weekly',compact('report','prev','from','to')); }
 public function monthly(Request $r,ReportService $s){ $from=now()->startOfMonth(); $to=now()->endOfMonth(); $report=$s->range($r->user(),$from,$to); return view('reports.monthly',compact('report','from','to')); }
 public function yearly(Request $r,ReportService $s){ $from=now()->startOfYear(); $to=now()->endOfYear(); $report=$s->range($r->user(),$from,$to); return view('reports.yearly',compact('report','from','to')); }
 public function custom(Request $r,ReportService $s){ $from=$r->from?Carbon::parse($r->from):now()->startOfMonth(); $to=$r->to?Carbon::parse($r->to):now(); $report=$s->range($r->user(),$from,$to); return view('reports.custom',compact('report','from','to')); }
 public function chartData(Request $r,ReportService $s){ $from=$r->from?Carbon::parse($r->from):now()->subDays(30); $to=$r->to?Carbon::parse($r->to):now(); return response()->json($s->range($r->user(),$from,$to)); }
}
