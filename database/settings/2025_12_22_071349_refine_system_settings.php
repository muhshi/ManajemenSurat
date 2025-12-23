<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        // Remove unused keys defensively
        $keysToDelete = [
            'system.default_office_lat',
            'system.default_office_lng',
            'system.default_geofence_radius_m',
            'system.default_work_start',
            'system.default_work_end',
            'system.default_workdays',
            'system.surat_prefix',
            'system.surat_tugas_template_path',
            'system.laporan_dinas_template_path',
            'system.surat_pernyataan_template_path',
            'system.cert_number_prefix',
            'system.cert_number_seq_by_year',
        ];

        foreach ($keysToDelete as $key) {
            try {
                $this->migrator->delete($key);
            } catch (\Exception $e) {
                // Ignore if not exists
            }
        }

        // Add new template keys defensively
        $newTemplates = [
            'system.template_sk',
            'system.template_surat_keluar',
            'system.template_memo',
            'system.template_surat_pengantar',
        ];

        foreach ($newTemplates as $key) {
            try {
                $this->migrator->add($key, null);
            } catch (\Exception $e) {
                // Ignore if already exists
            }
        }
    }
};
