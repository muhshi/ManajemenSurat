<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('system.default_office_name', 'BPS Kabupaten Demak');
        $this->migrator->add('system.default_office_lat', -6.894561);
        $this->migrator->add('system.default_office_lng', 110.637492);
        $this->migrator->add('system.default_geofence_radius_m', 100);
        $this->migrator->add('system.default_work_start', '08:00');
        $this->migrator->add('system.default_work_end', '16:00');
        $this->migrator->add('system.default_workdays', [1, 2, 3, 4, 5]);
        $this->migrator->add('system.cert_city', 'Demak');
        $this->migrator->add('system.cert_signer_name', '-');
        $this->migrator->add('system.cert_signer_nip', '-');
        $this->migrator->add('system.cert_signer_title', 'Kepala Badan Pusat Statistik Kabupaten Demak');
        $this->migrator->add('system.office_code', '33210');
        $this->migrator->add('system.surat_prefix', 'B');
        $this->migrator->add('system.surat_tugas_template_path', null);
        $this->migrator->add('system.laporan_dinas_template_path', null);
        $this->migrator->add('system.surat_pernyataan_template_path', null);
        $this->migrator->add('system.cert_signer_signature_path', null);
        $this->migrator->add('system.cert_number_prefix', 'BPS-DMK');
        $this->migrator->add('system.cert_number_seq_by_year', []);
    }
};
