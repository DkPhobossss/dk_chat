<?php

namespace Tests\Feature\Api;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;
    private $users;
    private $chats;
    private $messages;

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

        $this->messages = [];

        array_push($this->messages, Message::factory()->create([
            'user_id' => $this->users[0]->id,
            'chat_id' => $this->chats[0]->id
        ]));
        array_push($this->messages, Message::factory()->create([
            'user_id' => $this->users[0]->id,
            'chat_id' => $this->chats[0]->id
        ]));
        array_push($this->messages, Message::factory()->create([
            'user_id' => $this->users[1]->id,
            'chat_id' => $this->chats[0]->id
        ]));
        array_push($this->messages, Message::factory()->create([
            'user_id' => $this->users[0]->id,
            'chat_id' => $this->chats[1]->id
        ]));
        array_push($this->messages, Message::factory()->create([
            'user_id' => $this->users[2]->id,
            'chat_id' => $this->chats[1]->id
        ]));
        array_push($this->messages, Message::factory()->create([
            'user_id' => $this->users[2]->id,
            'chat_id' => $this->chats[3]->id
        ]));
        array_push($this->messages, Message::factory()->create([
            'user_id' => $this->users[3]->id,
            'chat_id' => $this->chats[3]->id
        ]));
    }

    public function test_guest_cannot_affect_message()
    {
        $response = $this->post(route('chats.messages.store', $this->chats[0]->id), ['body' => 'test']);
        $response->assertUnauthorized();

        $response = $this->put(route('chats.messages.update', [$this->chats[0]->id, $this->messages[0]->id]), ['body' => 'test']);
        $response->assertUnauthorized();

        $response = $this->delete(route('chats.messages.destroy', [$this->chats[0]->id, $this->messages[0]->id]));
        $response->assertUnauthorized();

        $response = $this->patch(route('chats.messages.restore', [$this->chats[0]->id, $this->messages[0]->id]));
        $response->assertUnauthorized();

        $response = $this->patch(route('chats.messages.update.last_seen', [$this->chats[0]->id, $this->messages[0]->id]));
        $response->assertUnauthorized();
    }


    private function assertMessageStructure(TestResponse $response)
    {
        $response->assertJsonStructure(
            [
                'id',
                'body',
                'user' => [
                    'id',
                    'name'
                ],
                'updated_at',
                'created_at'
            ]
        );
    }

    public function test_auth_user_cannot_affect_message_in_unexist_chat()
    {
        $user = $this->users[0];

        $response = $this->actingAs($user)->post(route('chats.messages.store', 666), ['body' => 'hello']);
        $response->assertNotFound();

        $response = $this->actingAs($user)->put(route('chats.messages.update', [666, $this->messages[0]->id]), ['body' => 'test']);
        $response->assertNotFound();

        $response = $this->actingAs($user)->delete(route('chats.messages.destroy', [666, $this->messages[0]->id]));
        $response->assertNotFound();

        $response = $this->actingAs($user)->patch(route('chats.messages.restore', [666, $this->messages[0]->id]));
        $response->assertNotFound();

        $response = $this->actingAs($user)->patch(route('chats.messages.update.last_seen', [666, $this->messages[0]->id]));
        $response->assertNotFound();
    }

    public function test_auth_user_can_store_message_in_his_chat()
    {
        $user = $this->users[0];
        $response = $this->actingAs($user)->post(route('chats.messages.store', $this->chats[0]), ['body' => 'hello']);

        $response->assertOk();

        $this->assertMessageStructure($response);
        $messageId = Message::latest('id')->value('id');

        $response->assertJsonFragment([
            'id' => $messageId
        ]);
    }

    public function test_auth_user_can_affect_his_message_in_his_chat()
    {
        $user = $this->users[0];

        $chatId = $this->chats[0]->id;
        //last message in chat for last_seen
        $message = $this->messages[1];

        $response = $this->actingAs($user)->put(route('chats.messages.update', [$chatId, $message->id]), ['body' => 'test']);
        $response->assertOk();
        $this->assertMessageStructure($response);
        $message = $message->fresh();
        $response->assertJsonFragment([
            'body' => $message['body']
        ]);

        $response = $this->actingAs($user)->delete(route('chats.messages.destroy', [$chatId, $message->id]));
        $response->assertOk();
        $this->assertMessageStructure($response);
        $message = $message->fresh();
        $response->assertJsonFragment([
            'body' => null
        ]);

        $response = $this->actingAs($user)->patch(route('chats.messages.restore', [$chatId, $message->id]));
        $response->assertOk();
        $this->assertMessageStructure($response);
        $message = $message->fresh();
        $response->assertJsonFragment([
            'body' => $message['body']
        ]);

        $response = $this->actingAs($user)->patch(route('chats.messages.update.last_seen', [$chatId, $message->id]));

        $response->assertOk();
        $response->assertExactJson(['result' => 1]);
    }

    public function test_auth_user_cannot_affect_not_his_message() {
        $user = $this->users[0];
        $chatId = $this->chats[0]->id;
        $notHisChatId = $this->chats[3]->id;
        $message = $this->messages[2];


        $response = $this->actingAs($user)->post(route('chats.messages.store', $notHisChatId), ['body' => 'hello']);
        $response->assertNotFound();
        
        $response = $this->actingAs($user)->put(route('chats.messages.update', [$chatId, $message->id]), ['body' => 'test']);
        $response->assertNotFound();

        $response = $this->actingAs($user)->delete(route('chats.messages.destroy', [$chatId, $message->id]));
        $response->assertNotFound();

        $response = $this->actingAs($user)->patch(route('chats.messages.restore', [$chatId, $message->id]));
        $response->assertNotFound();
    }

    public function test_auth_user_cannot_set_older_message_id(){
        $user = $this->users[0];
        $chatId = $this->chats[0]->id;

        //set last message in chat as last_seen
        $response = $this->actingAs($user)->patch(route('chats.messages.update.last_seen', [$chatId, $this->messages[1]->id]));
        $response->assertOk();
        $response->assertExactJson(['result' => 1]);

        $response = $this->actingAs($user)->patch(route('chats.messages.update.last_seen', [$chatId, $this->messages[0]->id]));
        $response->assertOk();
        $response->assertExactJson(['result' => 0]);
    }
}