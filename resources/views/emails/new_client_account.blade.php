@component('mail::message')
# Bem-vindo(a), {{ $client->name }}!

Sua conta em nosso site foi criada com sucesso.

Você pode acessar sua conta usando as seguintes credenciais:

**Email:** {{ $client->email }}
**Senha:** {{ $password }}

Recomendamos que você altere sua senha após o primeiro login por motivos de segurança.

@component('mail::button', ['url' => url('/login')])
Fazer Login
@endcomponent

Obrigado por se juntar a nós!

Atenciosamente,<br>
{{ config('app.name') }}
@endcomponent
