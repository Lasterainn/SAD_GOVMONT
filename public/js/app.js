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

    // 3. Footer Newsletter & Admin Flat Button (CSP-clean)
    document.getElementById('footerNewsletterForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        alert('Terima kasih! Email Anda terdaftar untuk kabar JakartaConnect.');
        this.reset();
    });

    document.getElementById('adminAddProjectBtn')?.addEventListener('click', () => {
        alert('Mode flat: Form tambah kerangka proyek admin.');
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

        const likeBtn = e.target.closest('.like-toggle');
        if (likeBtn) {
            const active = likeBtn.classList.toggle('is-active');
            const base = parseInt(likeBtn.dataset.likeCount || '0', 10);
            const numEl = likeBtn.querySelector('.like-num');
            if (numEl) numEl.textContent = String(base + (active ? 1 : 0));
        }

        const copyBtn = e.target.closest('.copy-link-btn');
        if (copyBtn) {
            navigator.clipboard?.writeText(window.location.href);
            const orig = copyBtn.textContent;
            copyBtn.textContent = 'Tautan Tersalin!';
            setTimeout(() => { copyBtn.textContent = orig; }, 1800);
        }
    });

    // 6. Halaman Proyek: Toggle Grid / Peta, Filter, Sorting & Peta Interaktif
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

    // 7. Halaman Diskusi: Filter Kategori, Chip Cepat, Searchbar, Sorting & Modal Buat Diskusi
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
        const sortBy = discussionSort?.value || 'popular';
        const saved = getSavedIds();

        items.sort((a, b) => {
            if (sortBy === 'comments') return Number(b.dataset.comments) - Number(a.dataset.comments);
            if (sortBy === 'latest') return Number(a.dataset.order) - Number(b.dataset.order);
            return Number(b.dataset.likes) - Number(a.dataset.likes);
        });

        let visible = 0;
        items.forEach(item => {
            discussionFeed?.appendChild(item);
            const matchCat = !activeCategory || item.dataset.category === activeCategory;
            const matchQ = !q || item.dataset.search.includes(q);
            let matchQuick = true;
            if (activeQuick === 'official') matchQuick = item.dataset.official === '1';
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
    const openModalBtn = document.getElementById('openNewDiscussionModal');
    const closeModalBtn = document.getElementById('closeNewDiscussionModal');
    const cancelModalBtn = document.getElementById('cancelNewDiscussion');
    const newDiscForm = document.getElementById('newDiscussionForm');

    if (modal && openModalBtn) {
        openModalBtn.addEventListener('click', () => modal.classList.remove('hidden'));
        closeModalBtn?.addEventListener('click', () => modal.classList.add('hidden'));
        cancelModalBtn?.addEventListener('click', () => modal.classList.add('hidden'));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.add('hidden');
        });
        newDiscForm?.addEventListener('submit', (e) => {
            e.preventDefault();
            const titleVal = document.getElementById('discTitle').value.trim();
            const catVal = document.getElementById('discCategory').value;
            const bodyVal = document.getElementById('discBody').value.trim();
            if (!titleVal || !bodyVal) return;

            const wrapper = document.createElement('div');
            wrapper.className = 'discussion-filter-item';
            wrapper.dataset.order = '-1';
            wrapper.dataset.discId = 'dsc-01';
            wrapper.dataset.search = `${titleVal} ${bodyVal} rani prameswari ${catVal}`.toLowerCase();
            wrapper.dataset.category = catVal;
            wrapper.dataset.official = '0';
            wrapper.dataset.hasProject = '0';
            wrapper.dataset.likes = '1';
            wrapper.dataset.comments = '0';

            wrapper.innerHTML = `
              <article class="card discussion">
                <span class="avatar av-1">RP</span>
                <div class="discussion-main">
                  <div class="meta small"><strong>Rani Prameswari</strong> <span class="muted">&middot; Baru saja</span></div>
                  <h3><a href="/diskusi/dsc-01"></a></h3>
                  <p class="muted small"></p>
                  <div class="actions tiny muted actions-ruled">
                    <span>0 tanggapan</span>
                    <span>1 suka</span>
                  </div>
                </div>
                <span class="tag"></span>
              </article>`;
            wrapper.querySelector('h3 a').textContent = titleVal;
            wrapper.querySelector('p').textContent = bodyVal;
            wrapper.querySelector('.tag').textContent = catVal;

            discussionFeed?.prepend(wrapper);
            newDiscForm.reset();
            modal.classList.add('hidden');
            filterDiscussions();
        });
    }

    // 8. Halaman Detail Diskusi: Balas Komentar, Filter Resmi & Kirim Tanggapan
    const replyBanner = document.getElementById('replyingToBanner');
    const replyTargetName = document.getElementById('replyingToName');
    const replyInput = document.getElementById('replyInputText');
    let replyPrefix = '';

    document.querySelectorAll('.reply-to-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const author = btn.dataset.replyAuthor || '';
            replyPrefix = `@${author} `;
            if (replyTargetName) replyTargetName.textContent = author;
            replyBanner?.classList.remove('hidden');
            replyInput?.focus();
        });
    });

    document.getElementById('cancelReplyTarget')?.addEventListener('click', () => {
        replyPrefix = '';
        replyBanner?.classList.add('hidden');
    });

    document.getElementById('replyFilterSelect')?.addEventListener('change', function () {
        const onlyOfficial = this.value === 'official';
        document.querySelectorAll('.comment-card').forEach(card => {
            card.classList.toggle('hidden', onlyOfficial && card.dataset.official !== '1');
        });
    });

    document.getElementById('threadReplyForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = (replyInput?.value || '').trim();
        if (!text) return;

        const list = document.getElementById('commentThreadList');
        const newCard = document.createElement('div');
        newCard.className = 'comment-card';
        newCard.dataset.official = '0';
        newCard.innerHTML = `
          <div class="comment-head">
            <span class="avatar avatar-sm av-1">RP</span>
            <div class="meta small"><strong>Rani Prameswari</strong> <span class="muted">&middot; Baru saja</span></div>
          </div>
          <p class="comment-text"></p>`;
        newCard.querySelector('.comment-text').textContent = replyPrefix + text;
        list?.prepend(newCard);

        const countEl = document.getElementById('replyTotalCount');
        if (countEl) countEl.textContent = String(Number(countEl.textContent || '0') + 1);

        replyInput.value = '';
        replyPrefix = '';
        replyBanner?.classList.add('hidden');
    });

    // 9. Halaman Dashboard: Switcher Pratinjau Peran (Masyarakat | Pemerintah | Admin)
    const roleTabs = document.querySelectorAll('[data-role-tab]');
    const rolePanels = document.querySelectorAll('.role-panel');

    roleTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const role = tab.dataset.roleTab;
            roleTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            rolePanels.forEach(panel => {
                panel.classList.toggle('hidden', panel.id !== `panel-${role}`);
            });
        });
    });
});
