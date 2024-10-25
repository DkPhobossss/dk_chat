<?php

namespace Tests\Feature\Chat;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;
    private $users;
    private $chats;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->users = User::factory()->count(4)->create();
        $this->chats= Chat::factory()->count(4)->create();

        //1-2 1-3 1-4 2-4
        $this->chats[0]->users()->attach([$this->users[0]->id, $this->users[1]->id]);
        $this->chats[1]->users()->attach([$this->users[0]->id, $this->users[2]->id]);
        $this->chats[2]->users()->attach([$this->users[0]->id, $this->users[3]->id]);
        $this->chats[3]->users()->attach([$this->users[2]->id, $this->users[3]->id]);
    }

    public function test_guest_cannot_access_chat_list_and_redirect_to_login(): void
    {
        $response = $this->get(route('chats.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_chat_page(): void
    {
        $chat = $this->chats->random();
        $response = $this->get(route('chats.show', $chat->id));

        $response->assertRedirect(route('login'));
    }

    public function test_chat_list_screen_can_be_rendered(): void
    {
        $user = $this->users->random();
        $response = $this->actingAs($user)->get(route('chats.index'));

        $response->assertOk();
    }

    public function test_chat_show_screen_can_be_rendered(): void
    {
        $user = $this->users->first();
        $chats = $this->chats->where('id', '<', 4);
        
        foreach ($chats as $chat) {
            $response = $this->actingAs($user)->get(route('chats.show', $chat->id));
            $response->assertOk();
        } 
    }

    public function test_user_cannot_watch_unexisting_chat(): void
    {
        $user = $this->users->first();
        
        $response = $this->actingAs($user)->get(route('chats.show', 666));
        $response->assertNotFound();
    }

    public function test_user_cannot_watch_someone_else_chat(): void
    {
        $user = $this->users->first();
        $chat = $this->chats->find(4);
        
        $response = $this->actingAs($user)->get(route('chats.show', $chat->id));
        $response->assertNotFound();
    }
}
