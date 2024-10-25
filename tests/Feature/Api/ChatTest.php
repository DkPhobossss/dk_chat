<?php

namespace Tests\Feature\Api;

use App\Models\Chat;
use App\Models\Message;
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
        $this->chats = Chat::factory()->count(4)->create();

        //1-2 1-3 1-4 2-4
        $this->chats[0]->users()->attach([$this->users[0]->id, $this->users[1]->id]);
        $this->chats[1]->users()->attach([$this->users[0]->id, $this->users[2]->id]);
        $this->chats[2]->users()->attach([$this->users[0]->id, $this->users[3]->id]);
        $this->chats[3]->users()->attach([$this->users[2]->id, $this->users[3]->id]);

        Message::factory()->create([
            'user_id' => $this->users[0]->id,
            'chat_id' => $this->chats[0]->id
        ]);
    }

    public function test_guest_cannot_get_chat_list(): void
    {
        $response = $this->get(route('chats.list'));

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_get_chat_data(): void
    {
        $chat = $this->chats->random();
        $response = $this->get(route('chats.data', $chat->id));

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_store_chat(): void
    {
        $response = $this->post(route('chats.store'), ['user_id' => 1]);

        $response->assertUnauthorized();
    }

    public function test_auth_user_receving_his_list_of_chats(): void
    {
        $user = $this->users[0];
        $response = $this->actingAs($user)->get(route('chats.list'));

        $response->assertOk();
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'created_at',
                'updated_at',
                'last_seen_message_id',
                'last_message',
            ],
        ]);

        $chats = $response->json();

        foreach ($chats as $chat) {
            $this->assertTrue(
                is_null($chat['last_seen_message_id']) ||
                    is_numeric($chat['last_seen_message_id'])
            );
            $this->assertTrue(
                (is_null($chat['last_message']) ||
                    (is_array($chat['last_message']) && !empty($chat['last_message']['id']) && !empty($chat['last_message']['body'])))
            );
        }
    }

    public function test_auth_user_getting_his_chats_data()
    {
        $user = $this->users[0];

        foreach ($user->chats as $chat) {
            $response = $this->actingAs($user)->get(route('chats.data', $chat->id));

            $response->assertOk();

            $response->assertJsonStructure([
                'id',
                'messages' => [
                    '*' => [
                        'id',
                        'body',
                        'user' => [
                            'id',
                            'name',
                        ],
                        'updated_at',
                        'created_at'
                    ]
                ],
                'users' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
            ]);
        }
    }

    public function test_auth_user_cannot_get_someone_else_chat_data() {
        $user = $this->users[0];

        $response = $this->actingAs($user)->get(route('chats.data', $this->chats[3]->id));

        $response->assertNotFound();
    }

    public function test_auth_user_cannot_get_unexisting_chat_data() {
        $user = $this->users[0];

        $response = $this->actingAs($user)->get(route('chats.data', 666));

        $response->assertNotFound();
    }

    public function test_auth_user_create_chat_with_someone() {
        $user = $this->users[1];
        $response = $this->actingAs($user)->post(route('chats.store'), ['user_id' => $this->users[2]->id]);

        $response->assertOk();

        $chatId = Chat::latest('id')->value('id');

        $response->assertJsonStructure([
            'id',
            'created_at',
            'updated_at',
            'name',
            'last_message',
            'last_seen_message_id',
        ]);

        $response->assertJsonFragment([
            'id' => $chatId
        ]);
    }

    public function test_auth_user_create_chat_with_someone_who_doesnt_exist() {
        $user = $this->users[0];
        $response = $this->actingAs($user)->post(route('chats.store'), ['user_id' => 666]);

        $response->assertNotFound();
    }

    public function test_auth_user_cant_create_chat_with_himself() {
        $user = $this->users[0];
        $response = $this->actingAs($user)->post(route('chats.store'), ['user_id' => $user->id]);

        $response->assertNotFound();
    }

    public function test_auth_user_open_existing_chat_when_post_store_request_to_chat_with_person() {
        $user = $this->users[0];
        $response = $this->actingAs($user)->post(route('chats.store'), ['user_id' => $this->users[1]->id]);

        $response->assertOk();

        $response->assertExactJson([
            'id' => $this->chats[0]->id
        ]);
    }
}
