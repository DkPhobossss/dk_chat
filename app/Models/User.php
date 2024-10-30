<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function chats() : BelongsToMany {
        return $this->belongsToMany(Chat::class)->withPivot('last_seen_message_id');
    }

    public static function getUserChats(User $user)
    {
        return $user->chats()->orderByDesc('updated_at')->with(['messages' => function ($query) {
            $query->select('id', 'chat_id', 'body')->latest()->take(1);
        }])->get();
    }

    public static function searchByName(string $name) : Collection {
        $searchFunction = DB::getDriverName() === 'sqlite' ? 'INSTR' : 'LOCATE';

        return User::select('id', 'name')
            ->where('name', 'like', '%' . $name .'%')
            ->where('id' , '!=' , Auth::id())
            ->orderByRaw( $searchFunction . '(?, name) ASC', [$name]) 
            ->take(5)
            ->get();
    }
}
