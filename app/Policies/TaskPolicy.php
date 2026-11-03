<?php
namespace App\Policies; use App\Models\{User,Task}; class TaskPolicy { public function view(User $u,Task $t): bool { return $u->id===$t->user_id; } public function update(User $u,Task $t): bool { return $u->id===$t->user_id; } public function delete(User $u,Task $t): bool { return $u->id===$t->user_id; } }
