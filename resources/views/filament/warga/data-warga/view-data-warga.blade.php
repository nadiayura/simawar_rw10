<x-filament::page>
    <style>
        .profile-shell{width:100%;margin:0 0 32px;}
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
        .field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-bottom:16px}
        .field-label{font-size:12px;color:#6b7280;margin-bottom:4px}
        .field-value{width:100%;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;background:#f9fafb;color:#111827;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}
        .field-select{width:100%;border-radius:9999px;border:1px solid #e5e7eb;padding:10px 40px 10px 14px;font-size:13px;background:#f9fafb;color:#111827;appearance:none;-webkit-appearance:none;-moz-appearance:none;background-image:url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%23111827' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;background-size:12px 8px}
        .field-value-textarea{width:100%;border-radius:18px;border:1px solid #e5e7eb;padding:10px 14px;font-size:13px;background:#f9fafb;color:#111827;resize:vertical}
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
            .profile-layout{flex-direction:column;align-items:stretch}
            .profile-avatar{align-items:flex-start;text-align:left}
        }
    </style>

    @php
        $record = $this->record;
        $name = $record?->nama ?? 'Warga';
        $initials = collect(explode(' ', (string) $name))
            ->filter(fn ($segment) => $segment !== '')
            ->map(fn ($segment) => mb_substr($segment, 0, 1))
            ->take(2)
            ->implode('');
    @endphp

    <div
        class="profile-shell"
        x-data="{ editing: false, showConfirm: false }"
        x-init="
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('refresh-profile-after-success', () => {
                    setTimeout(() => window.location.reload(), 3000);
                });
            });
        "
    >
        <div class="card-profile">
            <div class="card-profile-header">
                <div class="card-profile-title">Informasi Data Pribadi</div>
                <button
                    type="button"
                    x-show="!editing"
                    x-cloak
                    @click="editing = true"
                    style="font-size:12px;padding:6px 14px;border-radius:12px;border:none;background:#636CCB;color:#fff;font-weight:600;cursor:pointer"
                >
                    Edit
                </button>
            </div>
            <div class="card-profile-body">
                <div class="profile-layout">
                    <div class="profile-avatar">
                        <div class="avatar-circle">
                            <span class="avatar-initials">{{ $initials !== '' ? $initials : 'W' }}</span>
                        </div>
                        <div class="avatar-helper">
                            Nama: {{ $record?->nama ?? '-' }}<br>
                            NIK: {{ $record?->warga_nik ?? '-' }}
                        </div>
                    </div>
                    <div class="profile-fields">
                        <form wire:submit.prevent="updateDataDiri" x-ref="dataForm">
                            <div class="field-grid">
                                <div>
                                    <div class="field-label">NIK</div>
                                    <input
                                        type="text"
                                        class="field-value"
                                        disabled
                                        value="{{ $record?->warga_nik ?? '' }}"
                                    >
                                </div>
                                <div>
                                    <div class="field-label">Nama</div>
                                    <input
                                        type="text"
                                        class="field-value"
                                        :disabled="!editing"
                                        wire:model.defer="formData.nama"
                                    >
                                </div>
                                <div>
                                    <div class="field-label">Jenis kelamin</div>
                                    <select
                                        class="field-select"
                                        :disabled="!editing"
                                        wire:model.defer="formData.jenis_kelamin"
                                    >
                                        <option value="">Pilih</option>
                                        <option value="L">L</option>
                                        <option value="P">P</option>
                                    </select>
                                </div>
                                <div>
                                    <div class="field-label">Agama</div>
                                    <select
                                        class="field-select"
                                        :disabled="!editing"
                                        wire:model.defer="formData.agama"
                                    >
                                        <option value="">Pilih</option>
                                        <option value="Islam">Islam</option>
                                        <option value="Kristen">Kristen</option>
                                        <option value="Katolik">Katolik</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Buddha">Buddha</option>
                                        <option value="Konghucu">Konghucu</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div>
                                    <div class="field-label">Status tinggal</div>
                                    <select
                                        class="field-select"
                                        :disabled="!editing"
                                        wire:model.defer="formData.status_tinggal"
                                    >
                                        <option value="">Pilih</option>
                                        <option value="Tetap">Tetap</option>
                                        <option value="Kontrak">Kontrak</option>
                                        <option value="Sementara">Sementara</option>
                                    </select>
                                </div>
                                <div>
                                    <div class="field-label">Alamat</div>
                                    <textarea
                                        class="field-value-textarea"
                                        rows="2"
                                        :disabled="!editing"
                                        wire:model.defer="formData.alamat"
                                    ></textarea>
                                </div>
                                <div>
                                    <div class="field-label">Nomor RT</div>
                                    <input
                                        type="text"
                                        class="field-value"
                                        disabled
                                        value="{{ optional($record?->rt)->nomor ?? $record?->no_rt_id ?? '' }}"
                                    >
                                </div>
                                <div>
                                    <div class="field-label">No hp</div>
                                    <input
                                        type="text"
                                        class="field-value"
                                        :disabled="!editing"
                                        wire:model.defer="formData.no_hp"
                                    >
                                </div>
                                <div style="grid-column: span 2;">
                                    <div class="field-label">Email</div>
                                    <input
                                        type="email"
                                        class="field-value"
                                        :disabled="!editing"
                                        wire:model.defer="formData.email"
                                    >
                                </div>
                            </div>
                            <div x-show="editing" style="margin-top:16px;display:flex;justify-content:flex-end">
                                <button
                                    type="button"
                                    @click="showConfirm = true"
                                    style="border-radius:12px;border:none;background:#636CCB;color:#fff;font-size:13px;font-weight:500;padding:8px 18px;cursor:pointer"
                                >
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="modal-overlay"
            x-show="showConfirm"
            x-cloak
        >
            <div class="modal-card">
                <div class="modal-header">
                    <div class="modal-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="11" stroke="#b91c1c" stroke-width="2"/>
                            <path d="M12 7v7" stroke="#b91c1c" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="12" cy="16" r="1.2" fill="#b91c1c"/>
                        </svg>
                    </div>
                    <div class="modal-title">Konfirmasi Perubahan Data</div>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom:6px">
                        Apakah anda yakin ingin menyimpan perubahan data pribadi?
                    </p>
                    <p>
                        Data akan diperbarui sesuai isian di formulir.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" @click="showConfirm = false">
                        Batalkan
                    </button>
                    <button
                        type="button"
                        class="btn-primary"
                        @click="$refs.dataForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })); showConfirm = false"
                    >
                        Ya, simpan perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
