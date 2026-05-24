<?php

namespace App\Services;

use App\Mail\AdminNewUserNotificationMail;
use App\Mail\UserWelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserService
{
    /**
     * @param  array{email: string, password: string, name: string}  $data
     */
    public function createUser(array $data): User
    {
        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'name' => $data['name'],
        ]);

        $this->sendWelcomeEmail($user);
        $this->notifyAdmin($user);

        return $user;
    }

    private function sendWelcomeEmail(User $user): void
    {
        Mail::to($user->email)->send(new UserWelcomeMail($user));
    }

    private function notifyAdmin(User $user): void
    {
        Mail::to(config('mail.admin_email'))->send(new AdminNewUserNotificationMail($user));
    }
}
