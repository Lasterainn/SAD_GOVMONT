document.addEventListener('DOMContentLoaded', () => {
    // 1. Toggle Tampilkan Password (Login & Register)
    document.querySelectorAll('.toggle-password').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            this.dataset.target.split(',').forEach(id => {
                const input = document.getElementById(id.trim());
                if (input) input.type = this.checked ? 'text' : 'password';
            });
        });
    });

    // 2. Validasi Client-side Form Register & Login
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const nikInput = document.getElementById('nik');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');

            if (nikInput && !/^[0-9]{16}$/.test(nikInput.value.trim())) {
                alert('NIK wajib berupa 16 digit angka.');
                e.preventDefault();
                return;
            }
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                alert('Konfirmasi password tidak cocok!');
                e.preventDefault();
                return;
            }
            if (typeof grecaptcha !== 'undefined' && grecaptcha.getResponse().length === 0) {
                alert('Silakan centang reCAPTCHA terlebih dahulu.');
                e.preventDefault();
            }
        });
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            if (typeof grecaptcha !== 'undefined' && grecaptcha.getResponse().length === 0) {
                alert('Silakan centang reCAPTCHA terlebih dahulu.');
                e.preventDefault();
            }
        });
    }

    // 3. Footer Newsletter & Konfirmasi Hapus Proyek
    document.getElementById('footerNewsletterForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        alert('Terima kasih! Email Anda terdaftar untuk kabar JakartaConnect.');
        this.reset();
    });

    document.querySelectorAll('.confirm-delete-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!confirm('Yakin ingin menghapus proyek ini dari database PostgreSQL?')) {
                e.preventDefault();
            }
        });
    });

    // 4. Global Quick Searchbar (Navbar & Ctrl+K)
    const gsModal = document.getElementById('globalSearchModal');
    const gsOpenBtn = document.getElementById('openGlobalSearch');
    const gsCloseBtn = document.getElementById('closeGlobalSearch');
    const gsInput = document.getElementById('globalSearchInput');

    function openGlobalSearch() {
        if (!gsModal) return;
        gsModal.classList.remove('hidden');
        setTimeout(() => gsInput?.focus(), 30);
    }

    gsOpenBtn?.addEventListener('click', openGlobalSearch);
    gsCloseBtn?.addEventListener('click', () => gsModal?.classList.add('hidden'));
    gsModal?.addEventListener('click', (e) => {
        if (e.target === gsModal) gsModal.classList.add('hidden');
    });

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            openGlobalSearch();
        } else if (e.key === 'Escape') {
            gsModal?.classList.add('hidden');
            document.getElementById('newDiscussionModal')?.classList.add('hidden');
            document.getElementById('newProjectModal')?.classList.add('hidden');
        }
    });

    gsInput?.addEventListener('input', () => {
        const q = gsInput.value.trim().toLowerCase();
        document.querySelectorAll('.gs-item').forEach(item => {
            const match = !q || (item.dataset.gsText || '').includes(q);
            item.classList.toggle('hidden', !match);
        });
    });

    // 5. Bookmark / Simpan Proyek & Diskusi (localStorage)
    const STORAGE_KEY = 'jc_saved_items';
    function getSavedIds() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }
        catch { return []; }
    }
    function setSavedIds(ids) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
    }
    function syncBookmarks() {
        const saved = getSavedIds();
        document.querySelectorAll('.bookmark-toggle').forEach(btn => {
            const id = btn.dataset.bookmarkId;
            const isSaved = saved.includes(id);
            btn.classList.toggle('is-saved', isSaved);
            const label = btn.querySelector('.bookmark-label');
            if (label) {
                label.textContent = isSaved ? 'Tersimpan' : (btn.classList.contains('btn-outline') ? 'Ikuti Proyek' : 'Simpan');
            }
        });
    }
    syncBookmarks();

    document.addEventListener('click', (e) => {
        const bmBtn = e.target.closest('.bookmark-toggle');
        if (bmBtn) {
            const id = bmBtn.dataset.bookmarkId;
            let saved = getSavedIds();
            saved = saved.includes(id) ? saved.filter(x => x !== id) : [...saved, id];
            setSavedIds(saved);
            syncBookmarks();
            filterDiscussions();
        }

        const copyBtn = e.target.closest('.copy-link-btn');
        if (copyBtn) {
            navigator.clipboard?.writeText(window.location.href);
            const orig = copyBtn.textContent;
            copyBtn.textContent = 'Tautan Tersalin!';
            setTimeout(() => { copyBtn.textContent = orig; }, 1800);
        }
    });

    // 6. Modal Tambah Proyek Admin (/proyek)
    const prjModal = document.getElementById('newProjectModal');
    document.getElementById('openNewProjectModal')?.addEventListener('click', () => prjModal?.classList.remove('hidden'));
    document.getElementById('closeNewProjectModal')?.addEventListener('click', () => prjModal?.classList.add('hidden'));
    document.getElementById('cancelNewProjectModal')?.addEventListener('click', () => prjModal?.classList.add('hidden'));
    prjModal?.addEventListener('click', (e) => {
        if (e.target === prjModal) prjModal.classList.add('hidden');
    });

    // 7. Halaman Proyek: Toggle Grid / Peta, Filter, Sorting & Peta Interaktif
    const viewButtons = document.querySelectorAll('[data-view-target]');
    const gridView = document.getElementById('projectsGridView');
    const mapView = document.getElementById('projectsMapView');

    viewButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            viewButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const target = btn.dataset.viewTarget;
            if (gridView && mapView) {
                gridView.classList.toggle('hidden', target !== 'grid');
                mapView.classList.toggle('hidden', target !== 'map');
            }
        });
    });

    const projectSearch = document.getElementById('projectSearch');
    const projectWilayah = document.getElementById('projectWilayah');
    const projectCategory = document.getElementById('projectCategory');
    const projectStatus = document.getElementById('projectStatus');
    const projectSort = document.getElementById('projectSort');
    const projectCount = document.getElementById('projectCount');
    const resetProjectBtn = document.getElementById('resetProjectFilters');
    const emptyResetProjects = document.getElementById('emptyResetProjects');
    const projectsEmptyState = document.getElementById('projectsEmptyState');

    function filterAndSortProjects() {
        const items = Array.from(document.querySelectorAll('.project-filter-item'));
        if (!items.length) return;

        const q = (projectSearch?.value || '').trim().toLowerCase();
        const wil = projectWilayah?.value || '';
        const cat = projectCategory?.value || '';
        const st = projectStatus?.value || '';
        const sortBy = projectSort?.value || 'latest';
        const hasFilter = Boolean(q || wil || cat || st);

        resetProjectBtn?.classList.toggle('hidden', !hasFilter);

        items.sort((a, b) => {
            if (sortBy === 'progress_desc') return Number(b.dataset.progress) - Number(a.dataset.progress);
            if (sortBy === 'progress_asc') return Number(a.dataset.progress) - Number(b.dataset.progress);
            if (sortBy === 'title_asc') return (a.dataset.rawTitle || '').localeCompare(b.dataset.rawTitle || '');
            return Number(a.dataset.order) - Number(b.dataset.order);
        });

        let visible = 0;
        items.forEach(item => {
            gridView?.appendChild(item);
            const show = (!q || item.dataset.title.includes(q))
                      && (!wil || item.dataset.wilayah === wil)
                      && (!cat || item.dataset.category === cat)
                      && (!st || item.dataset.status === st);
            item.classList.toggle('hidden', !show);
            if (show) visible++;
        });

        document.querySelectorAll('.jakarta-map-pin').forEach(pin => {
            const showPin = (!q || pin.dataset.title.includes(q))
                         && (!wil || pin.dataset.wilayah === wil)
                         && (!cat || pin.dataset.category === cat)
                         && (!st || pin.dataset.status === st);
            pin.classList.toggle('hidden', !showPin);
        });

        if (projectCount) projectCount.textContent = String(visible);
        projectsEmptyState?.classList.toggle('hidden', visible > 0);
    }

    function resetAllProjectFilters() {
        if (projectSearch) projectSearch.value = '';
        if (projectWilayah) projectWilayah.value = '';
        if (projectCategory) projectCategory.value = '';
        if (projectStatus) projectStatus.value = '';
        if (projectSort) projectSort.value = 'latest';
        filterAndSortProjects();
    }

    [projectSearch, projectWilayah, projectCategory, projectStatus, projectSort].forEach(el => {
        el?.addEventListener('input', filterAndSortProjects);
        el?.addEventListener('change', filterAndSortProjects);
    });
    resetProjectBtn?.addEventListener('click', resetAllProjectFilters);
    emptyResetProjects?.addEventListener('click', resetAllProjectFilters);

    // Interaksi Pin & Zoom Peta Jakarta
    document.querySelectorAll('.jakarta-map-pin').forEach(pin => {
        pin.addEventListener('click', () => {
            document.querySelectorAll('.jakarta-map-pin').forEach(p => p.classList.remove('active'));
            pin.classList.add('active');
            const id = pin.dataset.pinId;
            document.querySelectorAll('.map-inspector-item').forEach(card => {
                card.classList.toggle('hidden', card.dataset.inspectorId !== id);
            });
        });
    });

    const mapLayer = document.getElementById('jakartaMapLayer');
    let mapScale = 1;
    document.getElementById('mapZoomIn')?.addEventListener('click', () => {
        mapScale = Math.min(1.6, +(mapScale + 0.2).toFixed(1));
        if (mapLayer) mapLayer.style.transform = `scale(${mapScale})`;
    });
    document.getElementById('mapZoomOut')?.addEventListener('click', () => {
        mapScale = Math.max(0.8, +(mapScale - 0.2).toFixed(1));
        if (mapLayer) mapLayer.style.transform = `scale(${mapScale})`;
    });
    document.getElementById('mapZoomReset')?.addEventListener('click', () => {
        mapScale = 1;
        if (mapLayer) mapLayer.style.transform = 'scale(1)';
    });

    // 8. Halaman Diskusi: Filter Kategori, Chip Cepat, Searchbar, Sorting & Modal Buat Diskusi
    const catButtons = document.querySelectorAll('.cat-btn');
    const quickChips = document.querySelectorAll('.chip-btn');
    const discussionSearch = document.getElementById('discussionSearch');
    const discussionSort = document.getElementById('discussionSort');
    const discussionFeed = document.getElementById('discussionFeed');
    const discussionCount = document.getElementById('discussionCount');
    const discussionsEmptyState = document.getElementById('discussionsEmptyState');
    let activeCategory = '';
    let activeQuick = 'all';

    function filterDiscussions() {
        const items = Array.from(document.querySelectorAll('.discussion-filter-item'));
        if (!items.length) return;

        const q = (discussionSearch?.value || '').trim().toLowerCase();
        const sortBy = discussionSort?.value || 'latest';
        const saved = getSavedIds();

        items.sort((a, b) => {
            if (sortBy === 'comments') return Number(b.dataset.comments) - Number(a.dataset.comments);
            return Number(a.dataset.order) - Number(b.dataset.order);
        });

        let visible = 0;
        items.forEach(item => {
            discussionFeed?.appendChild(item);
            const matchCat = !activeCategory || item.dataset.category === activeCategory;
            const matchQ = !q || item.dataset.search.includes(q);
            let matchQuick = true;
            if (activeQuick === 'project') matchQuick = item.dataset.hasProject === '1';
            if (activeQuick === 'saved') matchQuick = saved.includes(item.dataset.discId);

            const show = matchCat && matchQ && matchQuick;
            item.classList.toggle('hidden', !show);
            if (show) visible++;
        });

        if (discussionCount) discussionCount.textContent = String(visible);
        discussionsEmptyState?.classList.toggle('hidden', visible > 0);
    }

    catButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            catButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeCategory = btn.dataset.category || '';
            filterDiscussions();
        });
    });

    quickChips.forEach(chip => {
        chip.addEventListener('click', () => {
            quickChips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            activeQuick = chip.dataset.discQuick || 'all';
            filterDiscussions();
        });
    });

    discussionSearch?.addEventListener('input', filterDiscussions);
    discussionSort?.addEventListener('change', filterDiscussions);
    document.getElementById('resetDiscussionFilters')?.addEventListener('click', () => {
        activeCategory = '';
        activeQuick = 'all';
        if (discussionSearch) discussionSearch.value = '';
        catButtons.forEach((b, i) => b.classList.toggle('active', i === 0));
        quickChips.forEach((c, i) => c.classList.toggle('active', i === 0));
        filterDiscussions();
    });

    const modal = document.getElementById('newDiscussionModal');
    document.getElementById('openNewDiscussionModal')?.addEventListener('click', () => modal?.classList.remove('hidden'));
    document.getElementById('closeNewDiscussionModal')?.addEventListener('click', () => modal?.classList.add('hidden'));
    document.getElementById('cancelNewDiscussion')?.addEventListener('click', () => modal?.classList.add('hidden'));
    modal?.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.add('hidden');
    });

    // 9. Halaman Detail Diskusi: Balas Komentar, Edit Topik, Edit Komentar & Moderasi Admin
    const replyBanner = document.getElementById('replyingToBanner');
    const replyTargetName = document.getElementById('replyingToName');
    const replyParentInput = document.getElementById('replyParentIdInput');
    const replyInput = document.getElementById('replyInputText');

    document.querySelectorAll('.reply-to-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const parentId = btn.dataset.replyId || '';
            const author = btn.dataset.replyAuthor || '';
            if (replyParentInput) replyParentInput.value = parentId;
            if (replyTargetName) replyTargetName.textContent = author;
            replyBanner?.classList.remove('hidden');
            replyInput?.focus();
        });
    });

    document.getElementById('cancelReplyTarget')?.addEventListener('click', () => {
        if (replyParentInput) replyParentInput.value = '';
        replyBanner?.classList.add('hidden');
    });

    const editTopicForm = document.getElementById('editTopicForm');
    document.getElementById('toggleEditTopicBtn')?.addEventListener('click', () => editTopicForm?.classList.toggle('hidden'));
    document.getElementById('cancelEditTopicBtn')?.addEventListener('click', () => editTopicForm?.classList.add('hidden'));

    document.querySelectorAll('.toggle-edit-chat').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById(`edit-chat-form-${btn.dataset.chatId}`)?.classList.toggle('hidden');
        });
    });

    document.querySelectorAll('.toggle-mod-chat').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById(`mod-chat-form-${btn.dataset.chatId}`)?.classList.toggle('hidden');
        });
    });
});