<?php namespace Tests\Feature; use App\Models\User; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class AuthTest extends TestCase { use RefreshDatabase;
 public function test_register_and_login(): void { $this->post('/register',['name'=>'Test','email'=>'t@example.com','password'=>'password123','password_confirmation'=>'password123'])->assertRedirect('/dashboard'); $this->assertAuthenticated(); auth()->logout(); $this->post('/login',['email'=>'t@example.com','password'=>'password123'])->assertRedirect('/dashboard'); }
 public function test_unauthorized_redirect(): void { $this->get('/dashboard')->assertRedirect('/login'); }
 public function test_logout(): void { $u=User::factory()->create(); $this->actingAs($u)->post('/logout')->assertRedirect('/login'); $this->assertGuest(); }
}
