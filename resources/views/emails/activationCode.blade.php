<p>
    {{ $user->email }} - activate account code: {{ $code }}
</p>
<hr>
<p>
    <a href="{{ route('activation.link', [ 'user_id' => $user->id, 'code' => $code ]) }}">activate link</a>
</p>