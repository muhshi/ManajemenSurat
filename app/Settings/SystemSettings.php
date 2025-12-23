<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SystemSettings extends Settings
{
    public string $default_office_name;
    public string $office_code;
    public string $cert_city;
    public string $cert_signer_name;
    public string $cert_signer_nip;
    public string $cert_signer_title;
    public ?string $cert_signer_signature_path;

    public ?string $template_sk;
    public ?string $template_surat_keluar;
    public ?string $template_memo;
    public ?string $template_surat_pengantar;

    public static function group(): string
    {
        return 'system';
    }
}
