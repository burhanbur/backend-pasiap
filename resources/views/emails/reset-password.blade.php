@component('mail::message')
# Verifikasi Email

Berikut ini adalah tombol reset kata sandi Anda. Link di bawah hanya berdurasi 60 menit, segera lakukan perubahan.

@component('mail::button', ['url' => $url])
Verifikasi Reset Kata Sandi
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent