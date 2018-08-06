# lib-esub-mailchimp

Adalah library untuk me-menej data email subscriber di mailchimp.com, secara
umum, module ini harus bekerja di bawah module [lib-esub](https://github.com/getmim/lib-esub).

## Instalasi

Jalankan perintah di bawah di folder aplikasi:

```
mim app install lib-esub-mailchimp
```

## Konfigurasi

Tambahkan informasi lists id dan api-key pada konfigurasi aplikasi seperti
di bawah:

```php
return [
    // ...
    'libEsubMailchimp' => [
        'list' => 'e3ddc7e3d1',
        'apikey' => 'abc123adc123abc123abc123abc123-us1'
    ]
    // ...
];
```

Properti `api-key` diisi dengan app-key yang bisa diambil dari halaman
`Account > Extras > API Keys` pada seksi `Your API keys`.

Sementara properti `list` adalah listid default yang akan digunakan jika
listid tidak didefinisikan pada pemanggilan fungsi-fungsi tertentu. Nilai
ini bisa diambil dari halaman `Lists > [Lists Name] > Settings > List name and campaign defaults`
pada seksi `List ID`.

## Penggunaan

Jika menggunakan module `lib-esub`, maka silahkan mengacu pada dokumentasi
module tersebut untuk cara penggunaan.

Module ini membawa satu class public dengan nama `LibEsubMailchimp\Library\Mailchimp`
yang bisa digunakan untuk memenej email subscriber:

```php
use LibEsubMailchimp\Library\Mailchimp;

$mc = new Mailchimp;

$mc->get($rpp, $page); // get all email subscriber
$mc->addMember($email, $fname, $lname);
$mc->removeMember($email);
```

Library ini memiliki beberapa method lain, yaitu:

### getLists(): array
### setList(listid): void
### get(int $rpp, int $page): array
### addMember(string $email, string $fname=null, string $lname=null): ?object
### getMember(string $email): ?object
### removeMember(string $email): bool
### lastError(): ?string

## Lisensi

Module ini menggunakan library [MailChimp API](https://github.com/drewm/mailchimp-api),
silahkan mengacu ke library tersebut untuk lisensi.