<x-mail::message>
# New user registered: {{ $user->name }}

A new account has been created.

**Name:** {{ $user->name }}

**Email:** {{ $user->email }}
</x-mail::message>
