@component('mail::message')
# 用户邮箱激活

Hi !&nbsp;&nbsp; ***{{ $name }}*** 这是一封激活邮件, 请注意是否您本人注册, 如果无误, 请点击下面的按钮或链接激活账户。
@component('mail::button', ['url' => config('app.url') . '/activation/'.$id.'/token/'.$token, 'color' => 'blue'])
    激活
@endcomponent

{{ config('app.url') . '/activation/'.$id.'/token/'.$token }}

Thanks,<br>
@component('mail::panel')
    春风十里不及你。
@endcomponent
@endcomponent