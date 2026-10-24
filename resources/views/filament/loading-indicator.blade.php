{{--
    Indikator proses panel admin (lihat AdminPanelProvider, render hook BODY_START).

    Panel ini memakai navigasi halaman penuh (tanpa SPA) dan formulir Livewire,
    sehingga pengguna tidak mendapat umpan balik apa pun setelah menekan
    "Simpan", "Hapus", atau menu sidebar. Akibatnya tombol sering ditekan dua
    kali karena dikira tidak berfungsi, dan data bisa tersimpan ganda.

    Tiga hal yang ditangani berkas ini:

    1. Formulir (submit) dan perpindahan halaman (klik tautan) langsung
       dikunci: lapisan transparan menutup seluruh layar sehingga klik
       berikutnya tidak sampai ke tombol. Spinner baru ditampilkan setelah
       150 ms supaya proses yang cepat tidak berkedip.
    2. Proses di antarmuka (aksi tabel, tombol hapus, dsb.) dipantau lewat
       Livewire.hook('request'). Aksi yang memanggil metode komponen memakai
       overlay penuh; permintaan latar seperti pencarian/filter hanya memakai
       bilah progres tipis di atas layar agar pengguna tetap bisa mengetik.
    3. Permintaan latar belakang (polling widget) tidak menampilkan apa pun.
--}}

<div
    id="panel-loading"
    class="panel-loading"
    data-variant="progress"
    data-busy="false"
    data-visible="false"
    aria-hidden="true"
>
    {{-- Bilah progres tipis: permintaan latar yang tidak menghalangi pekerjaan. --}}
    <div class="panel-loading__progress" aria-hidden="true">
        <span class="panel-loading__progress-bar"></span>
    </div>

    {{-- Overlay penuh: proses simpan/hapus/perpindahan halaman. --}}
    <div class="panel-loading__backdrop">
        <div class="panel-loading__card" role="status" aria-live="polite">
            <span class="panel-loading__spinner" aria-hidden="true"></span>

            <span class="panel-loading__text">
                <span class="panel-loading__label" data-panel-loading-label>Memproses…</span>
                <span class="panel-loading__hint">Mohon tunggu, jangan menutup halaman ini.</span>
            </span>
        </div>
    </div>
</div>

<style>
    /*
     * Lapisan induk selalu ada di dalam dokumen (bukan display: none) supaya
     * dapat langsung memblokir klik tanpa menunggu animasi tampil. Z-index
     * diletakkan di atas modal, dropdown, dan notifikasi Filament agar tidak
     * ada tombol yang masih bisa ditekan ketika proses berjalan.
     */
    .panel-loading {
        position: fixed;
        inset: 0;
        z-index: 9999;
        pointer-events: none;
        /*
         * Klik kedua pada tombol simpan sering jatuh tepat di atas kartu
         * indikator. Tanpa ini, teks kartu ikut tersorot dan terlihat seperti
         * formulir yang rusak.
         */
        -webkit-user-select: none;
        user-select: none;
    }

    .panel-loading[data-busy='true'] {
        pointer-events: auto;
        cursor: progress;
    }

    /* ---------- Bilah progres (permintaan latar) ---------- */

    .panel-loading__progress {
        position: fixed;
        inset-block-start: 0;
        inset-inline: 0;
        height: 3px;
        overflow: hidden;
        opacity: 0;
        transition: opacity 0.15s ease;
    }

    .panel-loading[data-visible='true'][data-variant='progress'] .panel-loading__progress {
        opacity: 1;
    }

    .panel-loading__progress-bar {
        display: block;
        height: 100%;
        width: 100%;
        background-image: linear-gradient(90deg, #1f2a44, #d9b44a, #1f2a44);
        animation: panel-loading-progress 1.4s ease-in-out infinite;
    }

    @keyframes panel-loading-progress {
        0% {
            transform: translateX(-100%) scaleX(0.4);
        }

        50% {
            transform: translateX(20%) scaleX(0.6);
        }

        100% {
            transform: translateX(100%) scaleX(0.4);
        }
    }

    /* ---------- Overlay penuh ---------- */

    .panel-loading__backdrop {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        background-color: rgba(21, 32, 56, 0.55);
        -webkit-backdrop-filter: blur(2px);
        backdrop-filter: blur(2px);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.18s ease, visibility 0.18s ease;
    }

    .panel-loading[data-visible='true'][data-variant='overlay'] .panel-loading__backdrop {
        opacity: 1;
        visibility: visible;
    }

    .panel-loading__card {
        display: flex;
        align-items: center;
        gap: 1rem;
        max-width: 24rem;
        padding: 1.25rem 1.5rem;
        border-radius: 0.9rem;
        background-color: #ffffff;
        box-shadow: 0 18px 45px -12px rgba(15, 23, 42, 0.5);
        color: #1f2a44;
    }

    .dark .panel-loading__card {
        background-color: #1f2a44;
        color: #f8fafc;
    }

    /*
     * Cincin berputar memakai dua warna brand: emas untuk bagian yang bergerak
     * dan navy untuk sisanya, dengan mask agar bagian tengahnya berlubang.
     */
    .panel-loading__spinner {
        flex: none;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        background: conic-gradient(
            from 0deg,
            rgba(31, 42, 68, 0.15) 0deg,
            rgba(31, 42, 68, 0.15) 90deg,
            #d9b44a 270deg,
            #1f2a44 360deg
        );
        -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 3px));
        mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 3px));
        animation: panel-loading-spin 0.9s linear infinite;
    }

    .dark .panel-loading__spinner {
        background: conic-gradient(
            from 0deg,
            rgba(248, 250, 252, 0.18) 0deg,
            rgba(248, 250, 252, 0.18) 90deg,
            #d9b44a 270deg,
            #f8fafc 360deg
        );
    }

    @keyframes panel-loading-spin {
        to {
            transform: rotate(1turn);
        }
    }

    .panel-loading__text {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        min-width: 0;
    }

    .panel-loading__label {
        font-size: var(--text-sm, 1rem);
        font-weight: 600;
        line-height: 1.3;
    }

    .panel-loading__hint {
        font-size: var(--text-xs, 0.875rem);
        color: #64748b;
    }

    .dark .panel-loading__hint {
        color: #cbd5e1;
    }

    /*
     * Pengguna yang memilih "kurangi animasi" tetap mendapat indikator, tetapi
     * putarannya diperlambat dan overlay muncul tanpa transisi.
     */
    @media (prefers-reduced-motion: reduce) {
        .panel-loading__spinner {
            animation-duration: 2.4s;
        }

        .panel-loading__progress-bar {
            animation-duration: 2.4s;
        }

        .panel-loading__backdrop {
            transition: none;
        }
    }
</style>

<script>
    (() => {
        'use strict';

        const root = document.getElementById('panel-loading');

        if (!root) {
            return;
        }

        const labelElement = root.querySelector('[data-panel-loading-label]');
        const html = document.documentElement;

        /*
         * Jeda sebelum animasi tampil: proses yang selesai di bawah 150 ms
         * (mis. buka modal, pencarian cepat) tidak perlu menampilkan apa pun,
         * tetapi klik tetap sudah dikunci sejak awal agar tidak dobel.
         */
        const REVEAL_DELAY = 150;

        // Batas aman bila respons tidak pernah datang (koneksi terputus).
        const SAFETY_TIMEOUT = 20000;

        // Batas bila perpindahan halaman dibatalkan (mis. dialog "belum disimpan").
        const NAVIGATION_TIMEOUT = 5000;

        // Jarak maksimal antara interaksi pengguna dan permintaan Livewire.
        const INTERACTION_WINDOW = 2000;

        // Kiriman formulir kedua dalam rentang ini dianggap klik ganda.
        const DOUBLE_SUBMIT_WINDOW = 1000;

        const defaultLabel = labelElement.textContent;

        let overlayCount = 0;
        let progressCount = 0;
        let overlayLabel = defaultLabel;
        let revealTimer = null;
        let safetyTimer = null;
        let navigationTimer = null;
        let lastInteractionAt = 0;
        let lastSubmitAt = 0;
        let formRelease = null;
        let livewireHooked = false;

        const now = () => Date.now();

        const recentlyInteracted = () => now() - lastInteractionAt < INTERACTION_WINDOW;

        const clearTimers = () => {
            window.clearTimeout(revealTimer);
            window.clearTimeout(safetyTimer);
            window.clearTimeout(navigationTimer);

            revealTimer = null;
            safetyTimer = null;
            navigationTimer = null;
        };

        /*
         * Kembali ke keadaan diam. Penghitung direset karena respons yang
         * hilang (mis. halaman ditinggalkan saat proses berjalan) tidak akan
         * pernah memanggil pelepasan.
         */
        const hide = () => {
            clearTimers();

            overlayCount = 0;
            progressCount = 0;
            overlayLabel = defaultLabel;
            lastSubmitAt = 0;
            formRelease = null;

            root.dataset.visible = 'false';
            root.dataset.busy = 'false';
            root.dataset.variant = 'progress';
            root.setAttribute('aria-hidden', 'true');

            html.removeAttribute('data-panel-loading');
        };

        const sync = () => {
            if (overlayCount + progressCount === 0) {
                hide();

                return;
            }

            const isOverlay = overlayCount > 0;

            root.dataset.variant = isOverlay ? 'overlay' : 'progress';
            root.dataset.busy = isOverlay ? 'true' : 'false';
            root.removeAttribute('aria-hidden');

            html.dataset.panelLoading = 'true';

            if (isOverlay) {
                labelElement.textContent = overlayLabel;
            }

            if (revealTimer === null && root.dataset.visible !== 'true') {
                revealTimer = window.setTimeout(() => {
                    revealTimer = null;
                    root.dataset.visible = 'true';
                }, REVEAL_DELAY);
            }

            window.clearTimeout(navigationTimer);
            navigationTimer = null;

            window.clearTimeout(safetyTimer);
            safetyTimer = window.setTimeout(hide, SAFETY_TIMEOUT);
        };

        /*
         * Setiap proses mengembalikan fungsi pelepas. Dengan cara ini beberapa
         * permintaan yang berjalan bersamaan (mis. unggah berkas + simpan)
         * tidak saling menutup indikator terlalu cepat.
         */
        const beginOverlay = (message) => {
            if (message) {
                overlayLabel = message;
            }

            overlayCount += 1;
            sync();

            return () => {
                overlayCount = Math.max(0, overlayCount - 1);
                sync();
            };
        };

        const beginProgress = () => {
            progressCount += 1;
            sync();

            return () => {
                progressCount = Math.max(0, progressCount - 1);
                sync();
            };
        };

        /* ---------- 1. Formulir: kunci & cegah kiriman ganda ---------- */

        document.addEventListener(
            'submit',
            (event) => {
                if (!(event.target instanceof HTMLFormElement)) {
                    return;
                }

                /*
                 * Klik kedua pada tombol yang sama (atau tombol lain di
                 * formulir) dibatalkan sebelum Livewire sempat mengirim
                 * permintaan. Jendela waktunya pendek agar pengguna tetap bisa
                 * mengirim ulang segera setelah pesan validasi muncul.
                 */
                if (now() - lastSubmitAt < DOUBLE_SUBMIT_WINDOW) {
                    event.preventDefault();
                    event.stopImmediatePropagation();

                    return;
                }

                lastSubmitAt = now();

                /*
                 * Formulir login dan formulir di dalam modal aksi (mis. konfirmasi
                 * hapus) tidak menyimpan apa pun, jadi label "Menyimpan…" akan
                 * menyesatkan. Keduanya memakai label umum.
                 */
                const isLogin = event.target.querySelector('input[type="password"]') !== null;

                const submitMethod =
                    event.target.getAttribute('wire:submit.prevent') ??
                    event.target.getAttribute('wire:submit') ??
                    '';

                const isActionModal = submitMethod.includes('callMountedAction');

                /*
                 * Pelepasan disimpan agar indikator ikut hilang ketika
                 * permintaan Livewire untuk formulir ini selesai (termasuk
                 * saat validasi gagal).
                 */
                formRelease = beginOverlay(isLogin || isActionModal ? 'Memproses…' : 'Menyimpan…');
            },
            true,
        );

        /* ---------- 2. Perpindahan halaman: klik tautan internal ---------- */

        document.addEventListener(
            'click',
            (event) => {
                lastInteractionAt = now();

                if (event.defaultPrevented || event.button !== 0) {
                    return;
                }

                // Klik dengan tombol pengubah membuka tab/jendela baru: jangan dikunci.
                if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                const link = event.target instanceof Element ? event.target.closest('a[href]') : null;

                if (!link || link.hasAttribute('download') || link.dataset.noPanelLoading !== undefined) {
                    return;
                }

                if (link.target && link.target !== '' && link.target !== '_self') {
                    return;
                }

                const href = link.getAttribute('href');

                if (!href || href.startsWith('#')) {
                    return;
                }

                let url;

                try {
                    url = new URL(link.href, window.location.href);
                } catch (error) {
                    return;
                }

                // Tautan ke situs lain, surel, atau telepon bukan perpindahan panel.
                if (url.origin !== window.location.origin || !['http:', 'https:'].includes(url.protocol)) {
                    return;
                }

                // Tautan jangkar pada halaman yang sama tidak memuat ulang dokumen.
                if (
                    url.pathname === window.location.pathname &&
                    url.search === window.location.search &&
                    url.hash !== ''
                ) {
                    return;
                }

                beginOverlay('Memuat halaman…');

                /*
                 * Perpindahan halaman penuh akan mengganti dokumen ini, jadi
                 * tidak ada respons yang perlu ditunggu. Bila perpindahan
                 * dibatalkan (mis. dialog "perubahan belum disimpan"),
                 * indikator dilepas kembali setelah beberapa detik.
                 */
                navigationTimer = window.setTimeout(hide, NAVIGATION_TIMEOUT);
            },
            true,
        );

        ['pointerdown', 'keydown'].forEach((type) => {
            document.addEventListener(
                type,
                () => {
                    lastInteractionAt = now();
                },
                true,
            );
        });



        /* ---------- 3. Proses Livewire (simpan, hapus, aksi tabel) ---------- */

        const readPayload = (payload) => {
            if (typeof payload !== 'string') {
                return payload ?? null;
            }

            try {
                return JSON.parse(payload);
            } catch (error) {
                return null;
            }
        };

        /*
         * Permintaan yang memanggil metode komponen berasal dari tindakan
         * pengguna (simpan, hapus, aksi massal, buka modal). Perubahan properti
         * (pencarian, filter) dikirim Livewire sebagai `$commit`, dan polling
         * sebagai `$refresh` — keduanya cukup memakai bilah progres agar
         * pengguna tetap bisa mengetik sambil menunggu.
         */
        const callsComponentMethod = (body) => {
            const components = Array.isArray(body?.components) ? body.components : [];

            return components.some((component) => {
                const calls = Array.isArray(component?.calls) ? component.calls : [];

                return calls.some((call) => {
                    const method = Array.isArray(call) ? call[0] : call?.method;

                    // Metode internal Livewire selalu diawali `$` (`$commit`, `$refresh`, `$set`).
                    return typeof method === 'string' && !method.startsWith('$');
                });
            });
        };

        const trackRequest = (payload) => {
            const body = readPayload(payload);

            if (!body) {
                return recentlyInteracted() ? beginProgress() : null;
            }

            if (callsComponentMethod(body)) {
                return beginOverlay();
            }

            // Polling latar belakang tidak berhubungan dengan tindakan pengguna.
            return recentlyInteracted() ? beginProgress() : null;
        };

        const registerLivewireHooks = () => {
            if (livewireHooked || !window.Livewire || typeof window.Livewire.hook !== 'function') {
                return;
            }

            livewireHooked = true;

            window.Livewire.hook('request', ({ payload, succeed, fail }) => {
                const release = trackRequest(payload);

                const releaseForm = () => {
                    if (!formRelease) {
                        return;
                    }

                    formRelease();
                    formRelease = null;
                };

                if (!release) {
                    /*
                     * Permintaan tanpa indikator (polling) tetap menutup
                     * indikator formulir bila ada, agar overlay tidak
                     * tertinggal ketika tanggapan formulir tidak memanggil
                     * hook ini.
                     */
                    succeed(releaseForm);
                    fail(releaseForm);

                    return;
                }

                succeed(() => {
                    release();
                    releaseForm();
                });

                fail(() => {
                    release();
                    releaseForm();
                });
            });
        };

        document.addEventListener('livewire:init', registerLivewireHooks);

        // Bila skrip ini dimuat setelah Livewire siap (mis. halaman dipulihkan).
        registerLivewireHooks();

        /*
         * SPA Livewire (bila diaktifkan kelak) memakai navigasi tanpa memuat
         * ulang dokumen, sehingga indikator harus dilepas saat halaman baru
         * selesai dipasang.
         */
        document.addEventListener('livewire:navigating', () => {
            beginOverlay('Memuat halaman…');
        });

        document.addEventListener('livewire:navigated', hide);

        // Halaman dipulihkan dari cache riwayat (tombol Back): bersihkan sisa indikator.
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                hide();
            }
        });
    })();
</script>
