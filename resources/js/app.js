import './bootstrap';

/**
 * Interaksi ringan situs publik PERWAMA INA (tanpa dependensi tambahan).
 */
document.addEventListener('DOMContentLoaded', () => {
    /* --- Menu mobile --- */
    const toggle = document.querySelector('[data-nav-toggle]');
    const panel = document.querySelector('[data-nav-panel]');

    if (toggle && panel) {
        toggle.addEventListener('click', () => {
            const isOpen = panel.classList.toggle('hidden') === false;
            toggle.setAttribute('aria-expanded', String(isOpen));
            document.body.style.overflow = isOpen ? 'hidden' : '';
        });

        panel.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                panel.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            });
        });
    }

    /* --- Header menyusut saat halaman digulir --- */
    const header = document.querySelector('[data-site-header]');

    if (header) {
        const onScroll = () => {
            header.classList.toggle('is-scrolled', window.scrollY > 24);
        };

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* --- Kelompok menu tarik-turun pada navigasi utama --- */
    /*
     * Kelompok menu (Profil, Program & Kegiatan, Publikasi) dibuka oleh CSS:
     * hover pada peranti bertunjuk dan fokus papan tombol. Skrip ini menangani
     * hal yang tidak dapat dilakukan CSS: menyetel `aria-expanded`, menempatkan
     * panel tepat di tepi bawah header, serta menutup kembali panel saat
     * tombolnya diklik ulang, di luar panel diklik, atau Escape ditekan.
     */
    const navGroups = Array.from(document.querySelectorAll('[data-nav-group]'));

    if (navGroups.length > 0 && header) {
        const triggerDari = (group) => group.querySelector('[data-nav-trigger]');

        const tutup = (group) => {
            group.classList.remove('is-open');
            triggerDari(group)?.setAttribute('aria-expanded', 'false');
        };

        const samakanAria = (group, terbuka) => {
            const trigger = triggerDari(group);

            if (trigger) {
                trigger.setAttribute(
                    'aria-expanded',
                    String(terbuka || group.classList.contains('is-open')),
                );
            }
        };

        /*
         * Panel digantung tepat di tepi bawah header (bukan di bawah tombolnya)
         * mengikuti desain: jarak tombol ke tepi bawah header ikut berubah bila
         * pengelola memakai logo atau nama brand yang lebih tinggi, sehingga
         * nilainya diukur ulang saat halaman dimuat dan saat ukuran berubah.
         */
        const sejajarkanPanel = () => {
            navGroups.forEach((group) => {
                const dropdown = group.querySelector('[data-nav-dropdown]');

                // Kelompok menu yang sedang tersembunyi (layar sempit) dilewati.
                if (!dropdown || group.offsetParent === null) {
                    return;
                }

                const jarak = header.getBoundingClientRect().bottom - group.getBoundingClientRect().top;

                dropdown.style.top = `${Math.round(jarak)}px`;
            });
        };

        navGroups.forEach((group) => {
            const trigger = triggerDari(group);

            if (!trigger) {
                return;
            }

            trigger.addEventListener('click', () => {
                const terbuka = !group.classList.contains('is-open');

                navGroups.forEach((lain) => {
                    if (lain !== group) {
                        tutup(lain);
                    }
                });

                group.classList.toggle('is-open', terbuka);
                trigger.setAttribute('aria-expanded', String(terbuka));
            });

            // Status ARIA disamakan dengan panel yang dibuka CSS (hover/fokus).
            group.addEventListener('pointerenter', () => samakanAria(group, true));
            group.addEventListener('pointerleave', () => samakanAria(group, false));
            group.addEventListener('focusin', () => samakanAria(group, true));
            group.addEventListener('focusout', (event) => {
                if (!group.contains(event.relatedTarget)) {
                    samakanAria(group, false);
                }
            });
        });

        document.addEventListener('click', (event) => {
            const diDalamKelompok = event.target instanceof Element
                && event.target.closest('[data-nav-group]') !== null;

            if (!diDalamKelompok) {
                navGroups.forEach(tutup);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            // Keluarkan fokus dari kelompok menu agar panel yang dibuka CSS menutup.
            if (navGroups.some((group) => group.contains(document.activeElement))) {
                document.activeElement.blur();
            }

            navGroups.forEach(tutup);
        });

        sejajarkanPanel();
        window.addEventListener('resize', sejajarkanPanel, { passive: true });
    }

    /* --- Tombol kembali ke atas --- */
    const toTop = document.querySelector('[data-back-to-top]');

    if (toTop) {
        const sync = () => toTop.classList.toggle('opacity-0', window.scrollY < 500);

        sync();
        window.addEventListener('scroll', sync, { passive: true });
        toTop.addEventListener('click', (event) => {
            event.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* --- Carousel hero beranda --- */
    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
        const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));

        if (slides.length < 2) {
            return;
        }

        const delay = parseInt(carousel.getAttribute('data-carousel-autoplay') || '6000', 10);
        let index = 0;
        let timer = null;

        const show = (next) => {
            index = (next + slides.length) % slides.length;

            slides.forEach((slide, position) => {
                slide.classList.toggle('is-active', position === index);
                slide.setAttribute('aria-hidden', String(position !== index));

                // Hanya slide yang berupa tautan/tombol yang perlu diatur tabindex.
                if (slide.matches('a, button')) {
                    slide.setAttribute('tabindex', position === index ? '0' : '-1');
                }
            });

            dots.forEach((dot, position) => {
                dot.classList.toggle('is-active', position === index);
            });
        };

        const stop = () => {
            if (timer !== null) {
                window.clearInterval(timer);
                timer = null;
            }
        };

        const start = () => {
            if (delay <= 0 || timer !== null) {
                return;
            }

            timer = window.setInterval(() => show(index + 1), delay);
        };

        const restart = () => {
            stop();
            start();
        };

        const prev = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');

        if (prev) {
            prev.addEventListener('click', () => {
                show(index - 1);
                restart();
            });
        }

        if (next) {
            next.addEventListener('click', () => {
                show(index + 1);
                restart();
            });
        }

        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                show(parseInt(dot.getAttribute('data-carousel-dot') || '0', 10));
                restart();
            });
        });

        carousel.addEventListener('mouseenter', stop);
        carousel.addEventListener('mouseleave', start);
        carousel.addEventListener('focusin', stop);
        carousel.addEventListener('focusout', start);

        show(0);
        start();
    });

    /* --- Lightbox galeri sederhana --- */
    /*
     * Foto dalam album yang sama memakai data-lightbox-group yang sama,
     * sehingga pratinjau bisa berpindah foto lewat tombol panah, tombol
     * kiri/kanan pada papan ketik, atau geser (swipe) di layar sentuh.
     * Pemicu tanpa atribut grup tetap tampil sendiri seperti sebelumnya.
     */
    const triggers = Array.from(document.querySelectorAll('[data-lightbox]'));

    if (triggers.length > 0) {
        const grup = new Map();

        triggers.forEach((trigger) => {
            const kunci = trigger.getAttribute('data-lightbox-group');

            if (!kunci) {
                return;
            }

            if (!grup.has(kunci)) {
                grup.set(kunci, []);
            }

            grup.get(kunci).push(trigger);
        });

        const overlay = document.createElement('div');
        overlay.className =
            'fixed inset-0 z-[100] hidden touch-pan-y items-center justify-center bg-navy-deep/95 p-4 md:p-10';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-label', 'Pratinjau foto');
        overlay.setAttribute('aria-hidden', 'true');
        overlay.innerHTML =
            '<button type="button" data-lightbox-close aria-label="Tutup pratinjau" class="absolute right-5 top-5 z-10 text-3xl leading-none text-white/80 hover:text-white">&times;</button>' +
            '<button type="button" data-lightbox-prev aria-label="Foto sebelumnya" class="absolute left-3 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white/10 px-3 py-3 text-3xl leading-none text-white/80 transition hover:bg-white/20 hover:text-white md:left-6 md:px-4 md:py-4">&lsaquo;</button>' +
            '<button type="button" data-lightbox-next aria-label="Foto berikutnya" class="absolute right-3 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white/10 px-3 py-3 text-3xl leading-none text-white/80 transition hover:bg-white/20 hover:text-white md:right-6 md:px-4 md:py-4">&rsaquo;</button>' +
            '<div class="flex max-h-full w-full flex-col items-center justify-center gap-3 px-10 md:px-16">' +
            '<img data-lightbox-image class="max-h-[75vh] max-w-full rounded-lg shadow-2xl" alt="">' +
            '<p data-lightbox-caption class="hidden max-w-3xl text-center text-sm leading-relaxed text-white/80"></p>' +
            '</div>' +
            '<p data-lightbox-counter aria-live="polite" class="absolute left-5 top-6 z-10 hidden rounded-full bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.08em] text-white/80"></p>';
        document.body.appendChild(overlay);

        const image = overlay.querySelector('[data-lightbox-image]');
        const tombolTutup = overlay.querySelector('[data-lightbox-close]');
        const tombolSebelumnya = overlay.querySelector('[data-lightbox-prev]');
        const tombolBerikutnya = overlay.querySelector('[data-lightbox-next]');
        const keterangan = overlay.querySelector('[data-lightbox-caption]');
        const penghitung = overlay.querySelector('[data-lightbox-counter]');
        let aktif = [];
        let indeks = 0;
        let pemicuTerakhir = null;

        const close = () => {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';

            // Kembalikan fokus ke foto yang tadi diklik agar pengguna papan
            // ketik tidak kehilangan posisi setelah pratinjau ditutup.
            if (pemicuTerakhir) {
                pemicuTerakhir.focus();
                pemicuTerakhir = null;
            }
        };

        const tampilkan = (posisi) => {
            if (aktif.length === 0) {
                return;
            }

            indeks = (posisi + aktif.length) % aktif.length;

            const trigger = aktif[indeks];
            const gambarKecil = trigger.querySelector('img');
            const teksKeterangan = (trigger.getAttribute('data-lightbox-caption') || '').trim();
            const banyak = aktif.length > 1;

            image.src = trigger.getAttribute('data-lightbox');
            image.alt = gambarKecil ? gambarKecil.getAttribute('alt') || '' : '';

            keterangan.textContent = teksKeterangan;
            keterangan.classList.toggle('hidden', teksKeterangan === '');

            tombolSebelumnya.classList.toggle('hidden', !banyak);
            tombolBerikutnya.classList.toggle('hidden', !banyak);
            penghitung.classList.toggle('hidden', !banyak);
            penghitung.textContent = `${indeks + 1} / ${aktif.length}`;

            pemicuTerakhir = trigger;

            // Muat lebih awal foto tetangga agar perpindahan terasa mulus.
            if (banyak) {
                [aktif[(indeks + 1) % aktif.length], aktif[(indeks - 1 + aktif.length) % aktif.length]].forEach(
                    (tetangga) => {
                        const pramuat = new Image();
                        pramuat.src = tetangga.getAttribute('data-lightbox');
                    },
                );
            }
        };

        const sebelumnya = () => tampilkan(indeks - 1);
        const berikutnya = () => tampilkan(indeks + 1);

        triggers.forEach((trigger) => {
            trigger.addEventListener('click', (event) => {
                event.preventDefault();

                const kunci = trigger.getAttribute('data-lightbox-group');

                aktif = kunci && grup.has(kunci) ? grup.get(kunci) : [trigger];

                if (!aktif.includes(trigger)) {
                    aktif = [trigger];
                }

                tampilkan(aktif.indexOf(trigger));

                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                overlay.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';

                tombolTutup.focus();
            });
        });

        tombolTutup.addEventListener('click', close);
        tombolSebelumnya.addEventListener('click', sebelumnya);
        tombolBerikutnya.addEventListener('click', berikutnya);

        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                close();
            }
        });

        // Geser ke kiri/kanan untuk berpindah foto pada layar sentuh.
        let awalSentuh = null;

        overlay.addEventListener(
            'touchstart',
            (event) => {
                awalSentuh = event.touches.length === 1 ? event.touches[0].clientX : null;
            },
            { passive: true },
        );

        overlay.addEventListener(
            'touchend',
            (event) => {
                if (awalSentuh === null || aktif.length < 2) {
                    awalSentuh = null;

                    return;
                }

                const jarak = event.changedTouches[0].clientX - awalSentuh;
                awalSentuh = null;

                if (Math.abs(jarak) < 40) {
                    return;
                }

                if (jarak < 0) {
                    berikutnya();
                } else {
                    sebelumnya();
                }
            },
            { passive: true },
        );

        document.addEventListener('keydown', (event) => {
            if (overlay.classList.contains('hidden')) {
                return;
            }

            if (event.key === 'Escape') {
                close();
            } else if (event.key === 'ArrowLeft') {
                event.preventDefault();
                sebelumnya();
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                berikutnya();
            }
        });
    }

    /* --- Tab bagian (mis. struktur pengurus pada halaman Tentang) --- */
    /*
     * Tanpa JavaScript seluruh panel tab tetap tampil (isi lengkap terbaca dan
     * dapat diindeks mesin pencari). Setelah skrip ini aktif, hanya panel tab
     * yang sedang dipilih yang ditampilkan. Tombol tab memakai kelas .chip
     * dengan penanda .is-active, sama seperti penyaring pada halaman daftar.
     */
    document.querySelectorAll('[data-tabs]').forEach((tabs) => {
        const tombol = Array.from(tabs.querySelectorAll('[data-tab]'));
        const panel = Array.from(tabs.querySelectorAll('[data-tab-panel]'));

        if (tombol.length === 0 || panel.length === 0) {
            return;
        }

        const tampilkan = (nilai) => {
            tombol.forEach((item) => {
                const aktif = item.getAttribute('data-tab') === nilai;

                item.classList.toggle('is-active', aktif);
                item.setAttribute('aria-selected', aktif ? 'true' : 'false');
                item.setAttribute('tabindex', aktif ? '0' : '-1');
            });

            panel.forEach((item) => {
                item.classList.toggle('hidden', item.getAttribute('data-tab-panel') !== nilai);
            });
        };

        tombol.forEach((item, urutan) => {
            item.addEventListener('click', () => tampilkan(item.getAttribute('data-tab')));

            // Tombol panah kiri/kanan memindahkan pilihan antartab.
            item.addEventListener('keydown', (event) => {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                    return;
                }

                event.preventDefault();

                const arah = event.key === 'ArrowRight' ? 1 : -1;
                const berikutnya = tombol[(urutan + arah + tombol.length) % tombol.length];

                berikutnya.focus();
                tampilkan(berikutnya.getAttribute('data-tab'));
            });
        });

        const awal = tombol.find((item) => item.classList.contains('is-active')) ?? tombol[0];

        tampilkan(awal.getAttribute('data-tab'));
    });

    /* --- Animasi muncul saat elemen masuk viewport --- */
    const reveals = document.querySelectorAll('[data-reveal]');

    if (reveals.length > 0 && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12 },
        );

        reveals.forEach((element) => observer.observe(element));
    } else {
        reveals.forEach((element) => element.classList.add('is-visible'));
    }

    /* --- Penyegar sesi & token CSRF untuk formulir --- */
    /*
     * Formulir yang lama dibiarkan terbuka dapat kehilangan sesi sehingga
     * pengiriman ditolak dengan "419 Page Expired". Token _token (dan meta
     * csrf-token) disegarkan secara berkala serta setiap kali tab kembali
     * aktif, agar formulir selalu dapat dikirim.
     */
    const tokenInputs = Array.from(document.querySelectorAll('form input[name="_token"]'));

    if (tokenInputs.length > 0) {
        const refreshUrl =
            document.querySelector('meta[name="session-refresh-url"]')?.content || '/sesi/segar';

        const syncTokens = (token) => {
            if (!token) {
                return;
            }

            tokenInputs.forEach((input) => {
                input.value = token;
            });

            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                meta.setAttribute('content', token);
            }
        };

        const refreshTokens = () => {
            if (document.visibilityState === 'hidden') {
                return;
            }

            fetch(refreshUrl, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            })
                .then((response) => (response.ok ? response.json() : null))
                .then((data) => syncTokens(data?.token))
                .catch(() => {});
        };

        // Segarkan berkala agar sesi tidak kedaluwarsa selama pengisian formulir.
        window.setInterval(refreshTokens, 5 * 60 * 1000);

        // Segarkan saat tab kembali aktif (mis. setelah dibiarkan lama).
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                refreshTokens();
            }
        });
    }

    /* --- Laporan lama kunjungan halaman (statistik dasbor panel) --- */
    /*
     * Dasbor panel menampilkan "Rata-rata Waktu Akses", yang dihitung dari lama
     * pengunjung berada di sebuah halaman. Angka itu hanya diketahui peramban,
     * sehingga dilaporkan sekali ketika halaman ditinggalkan (tab disembunyikan
     * atau ditutup) agar tidak mengganggu pemuatan halaman. Bila pengunjung
     * kembali ke tab lalu meninggalkannya lagi, laporan dikirim ulang dengan
     * waktu yang lebih lama dan server menyimpan angka terbesar.
     */
    const visitUrl = document.querySelector('meta[name="visit-duration-url"]')?.content;

    if (visitUrl) {
        const mulaiDibuka = Date.now();
        let sudahDilaporkan = 0;

        const laporkanLamaKunjungan = () => {
            const detik = Math.round((Date.now() - mulaiDibuka) / 1000);

            if (detik < 1 || detik <= sudahDilaporkan) {
                return;
            }

            sudahDilaporkan = detik;

            const isi = new FormData();
            isi.append('duration', String(detik));
            isi.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

            if (navigator.sendBeacon) {
                navigator.sendBeacon(visitUrl, isi);
            } else {
                fetch(visitUrl, {
                    method: 'POST',
                    body: isi,
                    credentials: 'same-origin',
                    keepalive: true,
                }).catch(() => {});
            }
        };

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                laporkanLamaKunjungan();
            }
        });
        window.addEventListener('pagehide', laporkanLamaKunjungan);
    }
});

