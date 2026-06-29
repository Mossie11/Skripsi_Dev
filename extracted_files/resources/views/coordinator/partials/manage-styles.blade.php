{{-- Shared manage-table CSS partial --}}
<style>
    select option { background-color: #fff !important; color: #1a2a3a !important; }
    .container-manage { width: 100%; padding: 0; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding: 1.5rem; background: linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%); border-radius: 8px; box-shadow: 0 4px 15px rgba(12, 68, 124, 0.15); }
    .header h2 { font-size: 1.4rem; color: #fff; margin: 0; }
    .actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn { 
        padding: .7rem 1.2rem; 
        border: none; 
        border-radius: 6px; 
        cursor: pointer; 
        font-weight: 600; 
        text-decoration: none; 
        display: inline-block; 
        font-size: .9rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,.1);
    }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.15); }
    .btn-create  { background: linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%); color:#fff; }
    .btn-delete-header, .btn-export-header { display: none; }
    .btn-delete-header.active, .btn-export-header.active { display: inline-block; }
    .btn-export-header { background:linear-gradient(135deg, #378ADD 0%, #0C447C 100%); color:#fff; }
    .btn-delete-header { background:linear-gradient(135deg, #e03535 0%, #c82333 100%); color:#fff; }
    .btn-back { background:linear-gradient(135deg, #378ADD 0%, #0C447C 100%); color:#fff; }
    .filters { display: flex; gap: 12px; margin-bottom: 1.5rem; align-items: center; flex-wrap: wrap; }
    .search-box { flex:1; display:flex; gap:10px; }
    .search-box input, .search-box select { 
        flex:1; 
        padding:.75rem 1rem; 
        background:#fff; 
        border:2px solid #e0e8f0; 
        color:#0C447C; 
        border-radius:6px;
        font-size:.9rem;
        transition:all 0.3s ease;
    }
    .search-box input:focus, .search-box select:focus { 
        border-color:#378ADD;
        box-shadow:0 0 0 3px rgba(55, 138, 221, 0.1);
    }
    .search-box button { 
        padding:.75rem 1.2rem; 
        background:linear-gradient(135deg, #378ADD 0%, #0C447C 100%); 
        border:none; 
        color:#fff; 
        cursor:pointer; 
        border-radius:6px;
        font-weight:600;
        transition:all 0.3s ease;
        box-shadow:0 3px 10px rgba(55, 138, 221, 0.2);
    }
    .search-box button:hover {
        transform:translateY(-2px);
        box-shadow:0 5px 15px rgba(55, 138, 221, 0.3);
    }
    .table-wrapper { 
        background:#fff; 
        border-radius:10px; 
        overflow-x:auto; 
        box-shadow:0 4px 20px rgba(12,68,124,.12);
        border:1px solid #e0e8f0;
    }
    table { width:100%; border-collapse:collapse; }
    thead { background:linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%); position:sticky; top:0; }
    th { 
        padding:1.1rem 1rem; 
        text-align:left; 
        font-weight:700; 
        color:#fff !important; 
        white-space:nowrap; 
        border-bottom:2px solid #1a7ba5;
        font-size:1.05rem;
        letter-spacing:0.3px;
    }
    td { 
        padding:1rem; 
        border-bottom:1px solid #e8f0f8; 
        color:#1a2a3a;
        font-size:1rem;
        word-break:break-word; 
    }
    tbody tr:hover { 
        background:#f5f9fd;
        box-shadow:inset 0 0 0 1px rgba(55, 138, 221, 0.05);
    }
    tbody tr:nth-child(even) { background:#fafbfd; }
    th:first-child, td:first-child { padding-left:60px !important; width:100px; }
    .checkbox {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        min-width: 18px;
        min-height: 18px;
        border: 2px solid rgba(255,255,255,0.35);
        border-radius: 4px;
        background: transparent;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
        vertical-align: middle;
    }
    .checkbox:hover {
        border-color: #378ADD;
        background: rgba(55,138,221,0.1);
    }
    .checkbox:checked {
        background: #378ADD;
        border-color: #378ADD;
    }
    .checkbox:checked::after {
        content: '';
        position: absolute;
        top: 1px;
        left: 5px;
        width: 5px;
        height: 10px;
        border: solid #fff;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }
    /* In light-background tables, darken the border */
    .table-wrapper .checkbox {
        border-color: #b0c4de;
    }
    .table-wrapper .checkbox:hover {
        border-color: #378ADD;
    }
    .table-wrapper .checkbox:checked {
        background: #378ADD;
        border-color: #378ADD;
    }
    .table-wrapper .checkbox:checked::after {
        border-color: #fff;
    }
    /* Header checkbox in dark thead */
    thead .checkbox {
        border-color: rgba(255,255,255,0.5);
    }
    thead .checkbox:hover {
        border-color: #fff;
        background: rgba(255,255,255,0.15);
    }
    thead .checkbox:checked {
        background: #fff;
        border-color: #fff;
    }
    thead .checkbox:checked::after {
        border-color: #0C447C;
    }
    .clickable-name { 
        color:#0C447C; 
        font-weight:700; 
        cursor:pointer; 
        text-decoration:none;
        padding:0.2rem 0;
        border-radius:4px;
        transition:all 0.2s ease;
        display: block;
        text-align: left;
    }
    .clickable-name:hover { 
        color:#fff;
        background:#378ADD;
        text-decoration:none;
    }
    .pagination { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; color:#666; font-size:.9rem; padding: 1rem 0; }
    .page-nav { display:flex; gap:8px; }
    .page-nav a { 
        background:#fff; 
        border:2px solid #e0e8f0; 
        color:#0C447C; 
        padding:.5rem .8rem; 
        border-radius:6px; 
        text-decoration:none;
        font-weight:600;
        transition:all 0.3s ease;
    }
    .page-nav a:hover:not(.disabled) { 
        background:#0C447C;
        color:#fff;
        border-color:#0C447C;
    }
    .page-nav a.disabled { opacity:.3; pointer-events:none; }
    .floating-bar { 
        position:fixed; 
        bottom:2rem; 
        left:50%; 
        transform:translateX(-50%) translateY(120px); 
        background:#fff; 
        padding:1rem 1.8rem; 
        border-radius:50px; 
        box-shadow:0 8px 25px rgba(0,0,0,.2); 
        display:flex; 
        align-items:center; 
        gap:25px; 
        z-index:1000; 
        transition:transform .3s ease;
        border:2px solid #e0e8f0;
    }
    .floating-bar.active { transform:translateX(-50%) translateY(0); }
    .floating-bar span {
        font-weight:600;
        color:#0C447C;
    }
    .bin-icon { 
        cursor:pointer; 
        font-size:1.4rem; 
        color:#e03535; 
        background:none; 
        border:none;
        transition:transform 0.2s ease;
    }
    .bin-icon:hover { 
        transform:scale(1.15);
    }
    .checkbox-cell { position:relative; }
    .row-delete-pill { position:absolute; left:10px; top:50%; transform:translateY(-50%); background:#fff; border-radius:50px; padding:5px 10px; display:none; align-items:center; box-shadow:0 2px 8px rgba(0,0,0,.2); cursor:pointer; border:none; font-size:1rem; z-index:10; }
    tr:hover .row-delete-pill { display:flex; }

    /* Modals */
    .modal-overlay { display:none; position:fixed; z-index:10000; inset:0; background:rgba(0,0,0,.5); align-items:center; justify-content:center; backdrop-filter:blur(2px); }
    .modal-overlay.active { display:flex; }
    .profile-modal-content { 
        background:#fff; 
        border-radius:12px; 
        padding:2.5rem; 
        max-width:600px; 
        width:90%; 
        max-height:90vh; 
        overflow-y:auto; 
        position:relative; 
        box-shadow:0 25px 50px rgba(0,0,0,.3);
        animation:modalSlideIn 0.3s ease-out;
    }
    .create-modal-content  { 
        background:#fff; 
        border-radius:12px; 
        padding:2.5rem; 
        max-width:800px; 
        width:90%; 
        max-height:90vh; 
        overflow-y:auto; 
        position:relative; 
        box-shadow:0 25px 50px rgba(0,0,0,.3);
        animation:modalSlideIn 0.3s ease-out;
    }
    @keyframes modalSlideIn {
        from {
            opacity:0;
            transform:translateY(-20px);
        }
        to {
            opacity:1;
            transform:translateY(0);
        }
    }
    .modal-close { 
        position:absolute; 
        top:15px; 
        right:20px; 
        background:none; 
        border:none; 
        font-size:2.5rem; 
        color:#aaa; 
        cursor:pointer;
        width:40px;
        height:40px;
        display:flex;
        align-items:center;
        justify-content:center;
        border-radius:50%;
        transition:all 0.3s ease;
        line-height:1;
    }
    .modal-close:hover { 
        color:#0C447C;
        background:#E6F1FB;
    }
    .profile-header { 
        font-size:1.5rem; 
        font-weight:bold; 
        border-bottom:3px solid #0C447C; 
        padding-bottom:1rem; 
        margin-bottom:1.5rem; 
        color:#0C447C; 
        display:flex; 
        justify-content:space-between; 
        align-items:center;
    }
    .profile-header span { color:#378ADD; font-weight:500; }
    .profile-section-title { 
        font-size:1.2rem; 
        font-weight:700; 
        margin-bottom:1.2rem; 
        margin-top:1.5rem;
        color:#0C447C;
        padding-bottom:0.5rem;
        border-bottom:2px solid #E6F1FB;
    }
    .profile-data-table { width:100%; border-collapse:collapse; }
    .profile-data-table td { 
        padding:14px 0; 
        vertical-align:top; 
        color:#1a2a3a; 
        border-bottom:1px solid #f0f4f8; 
    }
    .profile-label { 
        width:40%; 
        font-weight:700; 
        color:#0C447C;
        font-size:.95rem;
        white-space:nowrap;
    }
    .profile-colon { width:20px; color:#888; }
    .profile-value { 
        color:#1a2a3a;
        font-size:.95rem;
        line-height:1.5;
    }
    .btn-edit  { 
        padding:.5rem 1.2rem; 
        background:linear-gradient(135deg, #378ADD 0%, #0C447C 100%); 
        color:#fff; 
        border:none; 
        border-radius:6px; 
        cursor:pointer; 
        font-weight:600; 
        font-size:.9rem;
        transition:all 0.3s ease;
        box-shadow:0 3px 10px rgba(55, 138, 221, 0.2);
    }
    .btn-edit:hover {
        transform:translateY(-2px);
        box-shadow:0 5px 15px rgba(55, 138, 221, 0.3);
    }
    .edit-input  { 
        width:100%; 
        padding:.75rem 1rem; 
        border:2px solid #e0e8f0; 
        border-radius:6px; 
        font-size:.95rem; 
        background:#fff;
        transition:all 0.3s ease;
        color:#0C447C;
    }
    .edit-input:focus {
        outline:none;
        border-color:#378ADD;
        box-shadow:0 0 0 3px rgba(55, 138, 221, 0.1);
    }
    .edit-select { 
        width:100%; 
        padding:.75rem 1rem; 
        border:2px solid #e0e8f0; 
        border-radius:6px; 
        font-size:.95rem; 
        background:#fff;
        transition:all 0.3s ease;
        color:#0C447C;
    }
    .edit-select:focus {
        outline:none;
        border-color:#378ADD;
        box-shadow:0 0 0 3px rgba(55, 138, 221, 0.1);
    }
    .profile-actions { display:flex; gap:12px; margin-top:2rem; }
    .btn-save   { 
        flex:1;
        padding:.75rem 1.5rem; 
        background:linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%); 
        color:#fff; 
        border:none; 
        border-radius:6px; 
        cursor:pointer; 
        font-weight:600;
        transition:all 0.3s ease;
        box-shadow:0 4px 12px rgba(12, 68, 124, 0.2);
    }
    .btn-save:hover {
        transform:translateY(-2px);
        box-shadow:0 6px 18px rgba(12, 68, 124, 0.3);
    }
    .btn-cancel { 
        flex:1;
        padding:.75rem 1.5rem; 
        background:#e03535; 
        color:#fff; 
        border:none; 
        border-radius:6px; 
        cursor:pointer; 
        font-weight:600;
        transition:all 0.3s ease;
        box-shadow:0 4px 12px rgba(224, 53, 53, 0.2);
    }
    .btn-cancel:hover {
        transform:translateY(-2px);
        box-shadow:0 6px 18px rgba(224, 53, 53, 0.3);
    }
    .create-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:25px; }
    .form-group { display:flex; flex-direction:column; gap:8px; }
    .form-group.full-width { grid-column:span 2; }
    .form-group label { font-size:.95rem; color:#0C447C; font-weight:700; letter-spacing:0.3px; }
    .form-group input, .form-group select, .form-group textarea { 
        padding:.75rem 1rem; 
        background:#fff; 
        border:2px solid #e0e8f0; 
        color:#0C447C; 
        border-radius:6px; 
        font-family:inherit;
        font-size:.95rem;
        transition:all 0.3s ease;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { 
        outline:none; 
        border-color:#378ADD; 
        box-shadow:0 0 0 3px rgba(55, 138, 221, 0.1);
        background:#f8fbff;
    }
    .form-group input::placeholder { color:#b0c4de; }
    .submit-btn { 
        margin-top:25px; 
        width:100%; 
        padding:1rem; 
        background:linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%); 
        color:#fff; 
        border:none; 
        border-radius:6px; 
        font-weight:bold; 
        cursor:pointer;
        font-size:1rem;
        transition:all 0.3s ease;
        box-shadow:0 4px 12px rgba(12, 68, 124, 0.2);
    }
    .submit-btn:hover { 
        transform:translateY(-2px);
        box-shadow:0 6px 20px rgba(12, 68, 124, 0.3);
    }
    .submit-btn:active { transform:translateY(0); }
    .profile-pic-placeholder { 
        width:120px; 
        height:120px; 
        background:linear-gradient(135deg, #E6F1FB 0%, #d4e8f7 100%); 
        border:2px solid #aac5e0; 
        border-radius:12px; 
        margin-bottom:1.5rem; 
        position:relative;
        box-shadow:0 4px 12px rgba(12, 68, 124, 0.1);
    }
    .profile-pic-placeholder::before,.profile-pic-placeholder::after{content:'';position:absolute;top:50%;left:50%;width:140%;height:2px;background:#7baada;}
    .profile-pic-placeholder::before{transform:translate(-50%,-50%) rotate(45deg);}
    .profile-pic-placeholder::after{transform:translate(-50%,-50%) rotate(-45deg);}
    
    /* Modal H2 */
    .create-modal-content h2, .profile-modal-content h2 {
        color:#0C447C;
        font-size:1.4rem;
        margin-bottom:1.5rem;
        font-weight:700;
        border-bottom: 2px solid #E6F1FB;
        padding-bottom:1rem;
    }
    
    .checkbox-cell { position:relative; vertical-align:middle; }
    .checkbox-cell .checkbox { margin-right:10px; vertical-align:middle; }

    /* Kelas badges */
    .kelas-badges { display:flex; flex-wrap:wrap; gap:5px; }
    .kelas-badge {
        display:inline-block;
        padding:3px 10px;
        background: #0C447C;
        color:#fff;
        border-radius:6px;
        font-size:.78rem;
        font-weight:600;
        border:1px solid transparent;
        white-space:nowrap;
        transition: all 0.2s ease;
    }
    .kelas-badge:hover {
        opacity:0.85;
        transform:translateY(-1px);
        box-shadow:0 2px 6px rgba(0,0,0,.25);
    }
</style>
