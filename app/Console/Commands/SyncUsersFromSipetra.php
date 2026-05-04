<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncUsersFromSipetra extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:users {--full : Abaikan timestamp, sync semua data (mengabaikan incremental)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi master data pengguna (pegawai & mitra) dari Sipetra';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $baseUrl = config('services.sipetra.base_url');
        $token   = config('services.sipetra.api_token');

        if (! $baseUrl || ! $token) {
            $this->error('Konfigurasi SIPETRA_BASE_URL atau SIPETRA_API_TOKEN belum diatur di config/services.php atau .env');
            return self::FAILURE;
        }

        // Jika opsi --full digunakan, maka timestamp terakhir diabaikan (pull seluruh record).
        // Jika tidak, gunakan synced_at terakhir dari cache untuk incremental sync harian.
        $lastSync = $this->option('full') ? null : Cache::get('sipetra_last_synced_at');

        $this->info($lastSync ? "Memulai incremental sync (perubahan sejak {$lastSync})..." : 'Memulai full sync (menarik seluruh data)...');

        $page = 1;
        $created = 0;
        $updated = 0;

        do {
            $this->line("Mengambil halaman {$page}...");

            $response = Http::withToken($token)
                ->acceptJson()
                ->get("{$baseUrl}/api/master/users", array_filter([
                    'page'          => $page,
                    'per_page'      => 100, // Ambil per 100
                    'updated_after' => $lastSync,
                ]));

            if ($response->failed()) {
                $this->error("Gagal menarik data dari Sipetra: HTTP {$response->status()}");
                $this->error($response->body());
                Log::error('sync:users failed', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                return self::FAILURE;
            }

            $payload  = $response->json();
            $lastPage = $payload['meta']['last_page'] ?? 1;
            
            // Sipetra mengembalikan `synced_at` untuk incremental update berikutnya
            $syncedAt = $payload['synced_at'] ?? now()->toIso8601String();

            $pageData = $payload['data'];
            $sipetraIds = collect($pageData)->pluck('sipetra_id')->filter()->toArray();
            $emails = collect($pageData)->pluck('email')->filter()->toArray();

            // Bulk fetch user yang sudah ada untuk mempercepat proses (Eager Loading)
            $existingUsersById = User::whereIn('sipetra_id', $sipetraIds)->get()->keyBy('sipetra_id');
            $existingUsersByEmail = User::whereIn('email', $emails)->get()->keyBy('email');

            foreach ($pageData as $data) {
                $email = $data['email'] ?? "no-email-{$data['sipetra_id']}@bps.go.id";

                // STRATEGI LINKING LOKAL (Tanpa Query Tambahan)
                $user = $existingUsersById->get($data['sipetra_id']) 
                     ?? $existingUsersByEmail->get($email);

                $attributes = [
                    'sipetra_id'     => $data['sipetra_id'],
                    'name'           => $data['name'],
                    'avatar_url'     => $data['avatar_url'],
                    'identity_type'  => $data['identity_type'],
                    'nip'            => $data['nip'],
                    'nip_baru'       => $data['nip_baru'],
                    'sobat_id'       => $data['sobat_id'],
                    'jabatan'        => $data['jabatan'],
                    'golongan'       => $data['golongan'],
                    'unit_kerja'     => $data['unit_kerja'],
                    'kd_satker'      => $data['kd_satker'],
                    'nomor_hp'       => $data['nomor_hp'],
                    'jenis_kelamin'  => $data['gender'],
                    'is_active'      => $data['is_active'],
                    'period'         => $data['period'],
                    'contract_start' => $data['contract_start'],
                    'contract_end'   => $data['contract_end'],
                ];

                // Cek konflik email di data lokal yang sudah kita ambil
                $conflictUser = $existingUsersByEmail->get($email);
                $isConflict = $conflictUser && (!$user || $conflictUser->id !== $user->id);

                if (! $isConflict) {
                    $attributes['email'] = $email;
                }

                if ($user) {
                    $user->update($attributes);
                    $updated++;
                } else {
                    $attributes['email'] = $email;
                    User::create($attributes);
                    $created++;
                }
            }

            $page++;
        } while ($page <= $lastPage);

        // Simpan timestamp sinkronisasi ini (hingga 30 hari sebagai jaga-jaga).
        Cache::put('sipetra_last_synced_at', $syncedAt, now()->addDays(30));

        $this->info("✅ Sinkronisasi Selesai.");
        $this->info("   User baru: {$created}");
        $this->info("   User diupdate: {$updated}");

        return self::SUCCESS;
    }
}
