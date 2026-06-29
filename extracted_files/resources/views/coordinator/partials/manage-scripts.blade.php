<script>
    // Shared manage-table JS
    function initManageTable(formId) {
        const selectAll        = document.getElementById('selectAll');
        const checkboxes       = document.querySelectorAll('.checkbox-item');
        const floatingBar      = document.getElementById('floatingBar');
        const selectedCount    = document.getElementById('selectedCount');
        const headerDeleteBtn  = document.getElementById('headerDeleteBtn');
        const headerExportBtn  = document.getElementById('headerExportBtn');
        const floatingDeleteBtn= document.getElementById('floatingDeleteBtn');
        const mainDeleteForm   = document.getElementById(formId || 'mainDeleteForm');

        function updateUI() {
            const checked = document.querySelectorAll('.checkbox-item:checked');
            const count   = checked.length;
            floatingBar.classList.toggle('active', count > 0);
            if (headerDeleteBtn) headerDeleteBtn.classList.toggle('active', count > 0);
            if (headerExportBtn) headerExportBtn.classList.toggle('active', count > 0);
            if (selectedCount) selectedCount.textContent = count + ' selected';
            if (selectAll) selectAll.checked = count === checkboxes.length && checkboxes.length > 0;
        }

        if (selectAll) selectAll.addEventListener('change', () => { checkboxes.forEach(cb => cb.checked = selectAll.checked); updateUI(); });
        checkboxes.forEach(cb => cb.addEventListener('change', updateUI));

        if (floatingDeleteBtn) floatingDeleteBtn.addEventListener('click', deleteSelected);
        if (headerDeleteBtn) headerDeleteBtn.addEventListener('click', deleteSelected);

        function deleteSelected() {
            const checked = document.querySelectorAll('.checkbox-item:checked');
            if (checked.length > 0 && confirm('Delete ' + checked.length + ' selected? Cannot be undone.')) mainDeleteForm.submit();
        }

        window.deleteSingleRow = function(id) {
            if (confirm('Delete this entry? Cannot be undone.')) {
                checkboxes.forEach(cb => { cb.checked = false; cb.disabled = true; });
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
                mainDeleteForm.appendChild(inp);
                mainDeleteForm.submit();
            }
        };
    }

    // Modal helpers
    function openModal(id)  { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    window.addEventListener('click', e => {
        document.querySelectorAll('.modal-overlay').forEach(m => { if (e.target === m) m.classList.remove('active'); });
    });

    // Profile modal helpers
    let _currentUser = null;
    window.showProfile = function(user, fields) {
        _currentUser = user;
        fields.forEach(({id, val}) => { const el = document.getElementById(id); if(el) el.textContent = val || '-'; });
        document.getElementById('profileViewMode').style.display = 'block';
        document.getElementById('profileEditMode').style.display = 'none';
        openModal('profilePopupModal');
    };
    window.enterEditMode = function() {
        if (!_currentUser) return;
        document.getElementById('profileViewMode').style.display = 'none';
        document.getElementById('profileEditMode').style.display = 'block';
    };
    window.cancelEditMode = function() {
        document.getElementById('profileViewMode').style.display = 'block';
        document.getElementById('profileEditMode').style.display = 'none';
    };
    window.closeProfile = function() { cancelEditMode(); closeModal('profilePopupModal'); };
    window.getCurrentUser = function() { return _currentUser; };
</script>
