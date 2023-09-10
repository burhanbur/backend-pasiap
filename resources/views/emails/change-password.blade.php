@component('mail::message')
# Verifikasi Email

Berikut ini adalah tombol konfirmasi untuk penggantian kata sandi Anda. Link di bawah hanya berdurasi 60 menit, segera lakukan perubahan.

@component('mail::button', ['url' => $url])
Verifikasi Ubah Kata Sandi
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
