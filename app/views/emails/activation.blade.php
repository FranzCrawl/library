Здравствуйте!<br />
<br />
Вы указали этот почтовый адрес при регистрации на сайте {{ Config::get('app.url') }}<br />
Если Вы этого не делали, то просто проигнорируйте это письмо.<br />
<br />
Если это сделали Вы, то для завершении регистрации необходимо перейти по ссылке:<br />
<a href="{{ $activationUrl }}">{{ $activationUrl }}</a>