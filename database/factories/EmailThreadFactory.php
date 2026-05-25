<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EmailThread;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EmailThread>
 */
class EmailThreadFactory extends Factory
{
    protected $model = EmailThread::class;

    public function definition(): array
    {
        return [
            'ticket_id'    => Ticket::factory(),
            'message_id'   => '<' . Str::uuid() . '@mail.example.com>',
            'in_reply_to'  => null,
            'from_address' => fake()->safeEmail(),
            'from_name'    => fake()->name(),
            'direction'    => fake()->randomElement(['inbound', 'outbound']),
            'raw_headers'  => null,
        ];
    }
}
