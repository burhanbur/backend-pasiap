@component('mail::message')
# Verifikasi Email

Halo {{ $name }}, Kamu telah berhasil membuat akun pendaftaran, silakan verifikasi akun kamu dengan menakan tombol Verifikasi Akun di bawah ini untuk dapat melanjutkan proses pendaftaran.

@component('mail::button', ['url' => $url])
Verifikasi Akun
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
