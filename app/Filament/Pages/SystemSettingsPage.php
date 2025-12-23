<?php

namespace App\Filament\Pages;

use App\Settings\SystemSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class SystemSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'system-settings';
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $title = 'Pengaturan Sistem';
    protected static string $view = 'filament.pages.system-settings-page';
    protected static ?int $navigationSort = 70;

    public ?array $data = [];

    public function mount(SystemSettings $s): void
    {
        $this->form->fill([
            'default_office_name' => $s->default_office_name,
            'office_code' => $s->office_code,
            'cert_city' => $s->cert_city,
            'cert_signer_name' => $s->cert_signer_name,
            'cert_signer_nip' => $s->cert_signer_nip,
            'cert_signer_title' => $s->cert_signer_title,
            'cert_signer_signature_path' => $s->cert_signer_signature_path,
            'template_sk' => $s->template_sk,
            'template_surat_keluar' => $s->template_surat_keluar,
            'template_memo' => $s->template_memo,
            'template_surat_pengantar' => $s->template_surat_pengantar,
            'gemini_api_key' => $s->gemini_api_key,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Kantor')
                ->description('Pengaturan identitas kantor')
                ->schema([
                    TextInput::make('default_office_name')
                        ->label('Nama Kantor')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('office_code')
                        ->label('Kode Kantor')
                        ->required()
                        ->maxLength(20),
                ])->columns(2),

            Section::make('Pejabat Penandatangan')
                ->description('Detail pejabat yang akan menandatangani surat secara default')
                ->schema([
                    TextInput::make('cert_city')->label('Kota Penetapan')->required(),
                    TextInput::make('cert_signer_name')->label('Nama Pejabat')->required(),
                    TextInput::make('cert_signer_nip')->label('NIP')->required(),
                    TextInput::make('cert_signer_title')->label('Jabatan')->required(),
                    FileUpload::make('cert_signer_signature_path')
                        ->label('Scan Tanda Tangan')
                        ->image()
                        ->directory('signatures')
                        ->visibility('public')
                        ->maxSize(2048),
                ])->columns(2),

            Section::make('Template Dokumen (.docx)')
                ->description('Upload file template .docx untuk setiap jenis surat')
                ->schema([
                    FileUpload::make('template_sk')
                        ->label('Template SK')
                        ->directory('templates')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->maxSize(5120),
                    FileUpload::make('template_surat_keluar')
                        ->label('Template Surat Keluar')
                        ->directory('templates')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->maxSize(5120),
                    FileUpload::make('template_memo')
                        ->label('Template Memo')
                        ->directory('templates')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->maxSize(5120),
                    FileUpload::make('template_surat_pengantar')
                        ->label('Template Surat Pengantar')
                        ->directory('templates')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->maxSize(5120),
                ])->columns(2),

            Section::make('AI Integration')
                ->description('Pengaturan untuk pengenalan cerdas berbasis Gemini AI')
                ->schema([
                    TextInput::make('gemini_api_key')
                        ->label('Gemini API Key')
                        ->password()
                        ->helperText('Dapatkan di Google AI Studio (aistudio.google.com)')
                        ->maxLength(255),
                ]),
        ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        app(SystemSettings::class)->fill($state)->save();

        Notification::make()->title('Pengaturan tersimpan')->success()->send();
    }
}
