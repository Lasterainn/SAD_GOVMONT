document.addEventListener('DOMContentLoaded', () => {
    // 1. Fitur Tampilkan Password (Toggle Visibility)
    document.querySelectorAll('.toggle-password').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const targetIds = this.dataset.target.split(',');
            targetIds.forEach(id => {
                const input = document.getElementById(id.trim());
                if (input) {
                    input.type = this.checked ? 'text' : 'password';
                }
            });
        });
    });

    // 2. Validasi Client-side Form Register
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
                alert('Konfirmasi password tidak cocok dengan password yang dimasukkan!');
                e.preventDefault();
                return;
            }

            if (typeof grecaptcha !== 'undefined' && grecaptcha.getResponse().length === 0) {
                alert('Silakan centang reCAPTCHA terlebih dahulu.');
                e.preventDefault();
                return;
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

    // 3. Halaman Proyek: Toggle Grid / Peta & Filter Live
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
    const projectCount = document.getElementById('projectCount');

    function filterProjects() {
        const items = document.querySelectorAll('.project-filter-item');
        if (!items.length) return;

        const q = (projectSearch?.value || '').trim().toLowerCase();
        const wil = projectWilayah?.value || '';
        const cat = projectCategory?.value || '';
        const st = projectStatus?.value || '';
        let visible = 0;

        items.forEach(item => {
            const matchQ = !q || item.dataset.title.includes(q);
            const matchWil = !wil || item.dataset.wilayah === wil;
            const matchCat = !cat || item.dataset.category === cat;
            const matchSt = !st || item.dataset.status === st;
            const show = matchQ && matchWil && matchCat && matchSt;
            item.classList.toggle('hidden', !show);
            if (show) visible++;
        });

        if (projectCount) projectCount.textContent = String(visible);
    }

    [projectSearch, projectWilayah, projectCategory, projectStatus].forEach(el => {
        if (el) {
            el.addEventListener('input', filterProjects);
            el.addEventListener('change', filterProjects);
        }
    });

    // 4. Halaman Diskusi: Filter Kategori, Pencarian & Modal Buat Diskusi
    const catButtons = document.querySelectorAll('.cat-btn');
    const discussionSearch = document.getElementById('discussionSearch');
    let activeCategory = '';

    function filterDiscussions() {
        const items = document.querySelectorAll('.discussion-filter-item');
        if (!items.length) return;
        const q = (discussionSearch?.value || '').trim().toLowerCase();

        items.forEach(item => {
            const matchCat = !activeCategory || item.dataset.category === activeCategory;
            const matchQ = !q || item.dataset.search.includes(q);
            item.classList.toggle('hidden', !(matchCat && matchQ));
        });
    }

    catButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            catButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeCategory = btn.dataset.category || '';
            filterDiscussions();
        });
    });

    if (discussionSearch) {
        discussionSearch.addEventListener('input', filterDiscussions);
    }

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
            alert('Diskusi berhasil ditambahkan ke pratinjau ruang diskusi (mode flat).');
            newDiscForm.reset();
            modal.classList.add('hidden');
        });
    }

    // 5. Halaman Dashboard: Switcher Pratinjau Peran (Masyarakat | Pemerintah | Admin)
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
