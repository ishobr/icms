<?php

$config['domain'] = 'Nama domain';
$config['mail'] = 'info@namadomain.com';
$config['site'] = 'Nama situs';

$config['theme'] = 'pro';

$config['dsn'] = 'mysql://username:password@localhost/dbname';

$config['cache']['backend'] = 'memcache';
$config['cache']['options'] = array('host' => '127.0.0.1', 'port' => '11211');

$config['date_fmt'] = '%A, %d %b %Y';
$config['time_fmt'] = '%H:%M WIB';
$config['datetime_fmt'] = '%A, %d %b %Y %H:%M WIB';

$config['use_cron'] = false;

$config['mail'] = array();
$config['mail']['reg_subject'] = 'Informasi Aktifasi Keanggotaan anda di ' . $config['site'];
$config['mail']['reg_body'] = "%s, terima kasih mau bergabung dengan {$config['domain']}\n\nUntuk aktifasi keanggotaan anda silahkan klik link di bawah ini:\n\nhttp://{$config['domain']}/user/activate/%s\n\nSelanjutnya anda bisa login dengan menggunakan alamat email anda ini.\n\n";
$config['mail']['forget_subject'] = "Sandi baru anda di {$config['site']}";
$config['mail']['forget_body'] = "Kami menerima permintaan anda untuk mereset sandi (password) anda di {$config['site']}.\n\nInilah sandi baru anda: %s\n\nJangan lupa selalu menjaga rahasia sandi anda.\n\n";
$config['mail']['send_body'] = "Rekan anda %s <%s> mengirim artikel ini untuk anda.\n\nPesan:\n%s\n\n%s\n\n%s\nSelengkapnya: http://{$config['domain']}%s\n\nPERNYATAAN: Email ini dikirim dari fasilitas 'Rekomendasikan ke Rekan' yang ada di situs {$config['site']} ({$config['domain']}). Fasilitas ini kami sediakan semata untuk mempermudah pengunjung berbagi informasi.\n";
