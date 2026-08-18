<x-filament-panels::page>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .profile-shell{max-width:980px;margin:0 0 32px;}
        .profile-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px}
        .profile-title{font-size:22px;font-weight:700;color:#111827;margin:0 0 4px}
        .profile-subtitle{font-size:13px;color:#6b7280;margin:0}
        .profile-actions{display:flex;align-items:center;gap:10px}
        .btn-outline{border-radius:9999px;border:1px solid #e5e7eb;background:#fff;color:#374151;font-size:13px;font-weight:500;padding:8px 18px;cursor:pointer}
        .btn-outline:hover{background:#f3f4f6}
        .card-profile{background:#fff;border-radius:18px;border:1px solid #e5e7eb;box-shadow:0 8px 30px rgba(15,23,42,.05);margin-bottom:18px;overflow:hidden}
        .card-profile-header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #eef2f7;background:#f9fafb}
        .card-profile-title{font-size:14px;font-weight:600;color:#111827}
        .pill-status{font-size:11px;padding:4px 10px;border-radius:9999px;background:#ecfdf3;color:#166534;font-weight:600}
        .card-profile-body{padding:18px}
        .profile-layout{display:flex;gap:24px;align-items:flex-start}
        .profile-avatar{flex:0 0 180px;display:flex;flex-direction:column;align-items:center;text-align:center}
        .avatar-circle{position:relative;width:104px;height:104px;border-radius:9999px;overflow:hidden;border:3px solid #e5e7eb;background:linear-gradient(145deg,#636CCB,#8b5cf6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:38px;font-weight:700}
        .avatar-initials{letter-spacing:.03em}
        .avatar-helper{font-size:11px;color:#6b7280;margin-top:10px;line-height:1.4}
        .profile-fields{flex:1;min-width:0}
        .meta-row{display:flex;flex-wrap:wrap;margin-top:12px;font-size:12px;color:#6b7280; justify-content:space-between; margin-top:calc(1.5rem);}
        .meta-item{display:inline-flex;align-items:center;gap:6px;padding:4px 9px;border-radius:9999px;background:#f3f4f6;color:#4b5563}
        .meta-dot{width:6px;height:6px;border-radius:9999px;background:#22c55e}
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:9999}
        .modal-card{background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,.16);width:480px;max-width:94vw}
        .modal-header{padding:18px 20px 10px 20px;text-align:center}
        .modal-body{padding:0 24px 20px 24px;text-align:center;color:#4b5563;font-size:14px}
        .modal-footer{padding:16px 20px 20px 20px;display:flex;justify-content:center;gap:10px}
        .modal-title{font-size:18px;font-weight:600;color:#111827;margin-top:8px}
        .modal-icon{width:40px;height:40px;border-radius:9999px;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto}
        .btn-primary{background:#2563eb;color:#fff;padding:8px 18px;border-radius:9999px;font-size:13px;border:none;cursor:pointer;font-weight:500}
        .btn-secondary{background:#f3f4f6;color:#374151;padding:8px 18px;border-radius:9999px;font-size:13px;border:none;cursor:pointer;font-weight:500}
        [x-cloak]{display:none !important;}
        @media (max-width:900px){
            .profile-shell{padding-inline:0}
            .profile-header{flex-direction:column;align-items:flex-start}
            .profile-actions{align-self:stretch;justify-content:flex-start}
            .profile-layout{flex-direction:column;align-items:stretch}
            .profile-avatar{align-items:flex-start;text-align:left}
        }
    </style>

    @php
        $user = \Filament\Facades\Filament::auth()->user();
        $name = $user?->name ?? 'Pengguna';
        $initials = collect(explode(' ', $name))
            ->filter(fn ($segment) => $segment !== '')
            ->map(fn ($segment) => mb_substr($segment, 0, 1))
            ->take(2)
            ->implode('');
    @endphp

    <div
        x-data="{ showAccountConfirm: false, showWargaConfirm: false }"
        x-init="
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('refresh-profile-after-success', () => {
                    setTimeout(() => window.location.reload(), 3000);
                });
            });
        "
    >
        <div class="profile-shell">
        <div class="card-profile">
            <div class="card-profile-header">
                <div class="card-profile-title">Informasi Akun Dashboard</div>
                <div class="pill-status">Aktif</div>
            </div>
            <div class="card-profile-body">
                <div class="profile-layout">
                    <div class="profile-avatar">
                        <div class="avatar-circle">
                            <span class="avatar-initials">{{ $initials !== '' ? $initials : 'A' }}</span>
                        </div>
                        <div class="avatar-helper">
                            Nama: {{ $user?->name ?? '-' }}<br>
                            Email: {{ $user?->email ?? '-' }}
                        </div>
                    </div>
                    <div class="profile-fields">
                        <form wire:submit.prevent="updateAccount" x-ref="accountForm">
                            <div style="display:flex;flex-direction:column;gap:16px">
                                <div style="display:flex;align-items:center;gap:12px">
                                    <div style="font-size:12px;color:#6b7280;width:140px">Alamat Email</div>
                                    <input
                                        type="email"
                                        wire:model.defer="accountData.email"
                                        style="flex:1;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;outline:none"
                                    >
                                </div>
                                <div style="display:flex;align-items:center;gap:12px">
                                    <div style="font-size:12px;color:#6b7280;width:140px">Kata Sandi Saat Ini</div>
                                    <input
                                        type="password"
                                        wire:model.defer="accountData.current_password"
                                        style="flex:1;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;outline:none"
                                    >
                                </div>
                                <div style="display:flex;align-items:center;gap:12px">
                                    <div style="font-size:12px;color:#6b7280;width:140px">Kata Sandi Baru</div>
                                    <input
                                        type="password"
                                        wire:model.defer="accountData.new_password"
                                        style="flex:1;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;outline:none"
                                    >
                                </div>
                                <div style="display:flex;align-items:center;gap:12px">
                                    <div style="font-size:12px;color:#6b7280;width:140px">Konfirmasi Kata Sandi</div>
                                    <input
                                        type="password"
                                        wire:model.defer="accountData.new_password_confirmation"
                                        style="flex:1;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;outline:none"
                                    >
                                </div>
                                <div style="display:flex;align-items:center;gap:12px">
                                    <div style="font-size:12px;color:#6b7280;width:140px">Jabatan</div>
                                    <input
                                        type="text"
                                        value="{{ $user?->role?->display_name ?? 'Administrator' }}"
                                        readonly
                                        style="flex:1;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;outline:none;background:#f9fafb"
                                    >
                                </div>
                            </div>
                            <div style="margin-top:16px;display:flex;justify-content:flex-end;gap:8px">
                                <button
                                    type="button"
                                    @click="showAccountConfirm = true"
                                    style="border-radius:9999px;border:none;background:#2563eb;color:#fff;font-size:13px;font-weight:500;padding:8px 18px;cursor:pointer"
                                >
                                    Simpan Perubahan Akun
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if ($user?->warga_nik)
            <div class="card-profile">
                <div class="card-profile-header">
                    <div class="card-profile-title">Informasi Data Pribadi</div>
                </div>
                <div class="card-profile-body">
                    <form wire:submit.prevent="updateWarga" x-ref="wargaForm">
                        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-bottom:16px">
                            <div>
                                <div style="font-size:12px;color:#6b7280;margin-bottom:4px">Nama</div>
                                <input type="text" wire:model.defer="wargaData.nama" style="width:100%;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;outline:none">
                            </div>
                            <div>
                                <div style="font-size:12px;color:#6b7280;margin-bottom:4px">No. HP</div>
                                <input type="text" wire:model.defer="wargaData.no_hp" style="width:100%;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;outline:none">
                            </div>
                            <div>
                                <div style="font-size:12px;color:#6b7280;margin-bottom:4px">Alamat Email</div>
                                <input type="email" wire:model.defer="wargaData.email" style="width:100%;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;outline:none">
                            </div>
                            <div>
                                <div style="font-size:12px;color:#6b7280;margin-bottom:4px">Jenis Kelamin</div>
                                <select wire:model.defer="wargaData.jenis_kelamin" style="width:100%;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;outline:none;background:#fff">
                                    <option value="">Pilih</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <div style="font-size:12px;color:#6b7280;margin-bottom:4px">Agama</div>
                                <select wire:model.defer="wargaData.agama" style="width:100%;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;outline:none;background:#fff">
                                    <option value="">Pilih</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                            </div>
                            <div>
                                <div style="font-size:12px;color:#6b7280;margin-bottom:4px">Status Tinggal</div>
                                <select wire:model.defer="wargaData.status_tinggal" style="width:100%;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;outline:none;background:#fff">
                                    <option value="">Pilih</option>
                                    <option value="Tetap">Tetap</option>
                                    <option value="Kontrak">Kontrak</option>
                                    <option value="Sementara">Sementara</option>
                                </select>
                            </div>
                        </div>
                        <div style="margin-bottom:16px">
                            <div style="font-size:12px;color:#6b7280;margin-bottom:4px">Alamat</div>
                            <textarea wire:model.defer="wargaData.alamat" rows="3" style="width:100%;border-radius:18px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;outline:none;resize:vertical"></textarea>
                        </div>
                        <div style="display:flex;justify-content:flex-end;gap:8px">
                            <button
                                type="button"
                                @click="showWargaConfirm = true"
                                style="border-radius:9999px;border:none;background:#2563eb;color:#fff;font-size:13px;font-weight:500;padding:8px 18px;cursor:pointer"
                            >
                                Simpan Data Warga
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

        <div
            class="modal-overlay"
            x-show="showAccountConfirm"
            x-cloak
        >
            <div class="modal-card">
                <div class="modal-header">
                    <div class="modal-icon">
                        <i class="fa-solid fa-circle-exclamation" style="font-size:20px;color:#b91c1c;"></i>
                    </div>
                    <div class="modal-title">Konfirmasi Perubahan Akun</div>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom:6px">
                        Apakah anda yakin ingin merubah informasi akun dashboard?
                    </p>
                    <p>
                        Perubahan dapat mempengaruhi alamat email dan kata sandi yang digunakan untuk masuk.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" @click="showAccountConfirm = false">
                        Batalkan
                    </button>
                    <button
                        type="button"
                        class="btn-primary"
                        @click="$refs.accountForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })); showAccountConfirm = false"
                    >
                        Ya, simpan perubahan
                    </button>
                </div>
            </div>
        </div>

        <div
            class="modal-overlay"
            x-show="showWargaConfirm"
            x-cloak
        >
            <div class="modal-card">
                <div class="modal-header">
                    <div class="modal-icon">
                        <i class="fa-solid fa-circle-exclamation" style="font-size:20px;color:#b91c1c;"></i>
                    </div>
                    <div class="modal-title">Konfirmasi Perubahan Data Warga</div>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom:6px">
                        Apakah anda yakin ingin merubah informasi data pribadi?
                    </p>
                    <p>
                        Data nama, kontak, dan alamat akan diperbarui sesuai isian di formulir.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" @click="showWargaConfirm = false">
                        Batalkan
                    </button>
                    <button
                        type="button"
                        class="btn-primary"
                        @click="$refs.wargaForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })); showWargaConfirm = false"
                    >
                        Ya, simpan data warga
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
