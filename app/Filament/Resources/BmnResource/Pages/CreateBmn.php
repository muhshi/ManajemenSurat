<?php

namespace App\Filament\Resources\BmnResource\Pages;

use App\Filament\Resources\BmnResource;
use App\Models\Pegawai;
use App\Models\Ruangan;
use Filament\Resources\Pages\CreateRecord;

class CreateBmn extends CreateRecord
{
    protected static string $resource = BmnResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->resolvePolymorphicPj($data);
    }

    protected function resolvePolymorphicPj(array $data): array
    {
        $type = $data['penanggung_jawab_type_radio'] ?? 'none';

        if ($type === 'pegawai') {
            $data['penanggung_jawab_type'] = Pegawai::class;
            $data['penanggung_jawab_id']   = $data['penanggung_jawab_id_pegawai'] ?? null;
        } elseif ($type === 'ruangan') {
            $data['penanggung_jawab_type'] = Ruangan::class;
            $data['penanggung_jawab_id']   = $data['penanggung_jawab_id_ruangan'] ?? null;
        } else {
            $data['penanggung_jawab_type'] = null;
            $data['penanggung_jawab_id']   = null;
        }

        unset($data['penanggung_jawab_type_radio'], $data['penanggung_jawab_id_pegawai'], $data['penanggung_jawab_id_ruangan']);
        return $data;
    }
}
