@extends('backend.layout.main')

@section('content')
<style>
.aip-page { padding: 0; }
.aip-card { background: #fff; border: 1px solid #e4e6fc; border-radius: 5px; overflow: hidden; margin-bottom: 20px; }
.aip-card-header { padding: 15px 20px; border-bottom: 1px solid #e4e6fc; background: #f9f9f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.aip-card-title { margin: 0; font-size: 1.1rem; font-weight: 600; color: #333; }
.aip-card-body { padding: 20px; }

.aip-form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 15px; }
.aip-form-group { display: flex; flex-direction: column; }
.aip-form-label { font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 5px; }
.aip-form-label .req { color: #dc3545; }
.aip-form-control { padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.9rem; outline: none; transition: border-color 0.2s, box-shadow 0.2s; background: #fff; color: #333; }
.aip-form-control:focus { border-color: #7c5cc4; box-shadow: 0 0 0 0.2rem rgba(124, 92, 196, 0.25); }
.aip-form-control:disabled { background: #e9ecef; cursor: not-allowed; }
textarea.aip-form-control { min-height: 80px; resize: vertical; font-family: monospace; }

.aip-btn { padding: 8px 16px; border: none; border-radius: 4px; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.2s; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
.aip-btn-primary { background: #7c5cc4; color: #fff; }
.aip-btn-primary:hover:not(:disabled) { background: #5a428c; }
.aip-btn-primary:disabled { background: #adb5bd; cursor: not-allowed; }
.aip-btn-secondary { background: #e3eaef; color: #212529; }
.aip-btn-secondary:hover { background: #d1dfeb; }
.aip-btn-danger { background: #dc3545; color: #fff; }
.aip-btn-danger:hover { background: #c82333; }
.aip-btn-sm { padding: 4px 10px; font-size: 0.8rem; }

.aip-table-wrap { overflow-x: auto; border: 1px solid #e4e6fc; border-radius: 5px; }
.aip-table { width: 100%; border-collapse: collapse; }
.aip-table th, .aip-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e4e6fc; font-size: 0.9rem; color: #333; }
.aip-table th { background: #f9f9f9; font-weight: 600; color: #555; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.03em; }
.aip-table tr:last-child td { border-bottom: none; }
.aip-table tr:hover { background: #fafafe; }

.aip-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
.aip-badge-success { background: #d4edda; color: #155724; }
.aip-badge-secondary { background: #e2e3e5; color: #383d41; }

.aip-actions { display: flex; gap: 6px; flex-wrap: wrap; }
.aip-toggle-btn { background: none; border: 1px solid #ced4da; border-radius: 4px; padding: 4px 10px; cursor: pointer; font-size: 0.8rem; color: #555; transition: all 0.2s; }
.aip-toggle-btn:hover { background: #f0f0f0; }
.aip-toggle-btn.enabled { background: #d4edda; color: #155724; border-color: #c3e6cb; }
.aip-toggle-btn.disabled { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }

.aip-empty { text-align: center; padding: 40px 20px; color: #6c757d; }
.aip-empty svg { width: 40px; height: 40px; margin-bottom: 10px; opacity: 0.4; }
.aip-empty h5 { color: #333; margin-bottom: 6px; font-weight: 600; }
.aip-empty p { margin: 0; font-size: 0.9rem; }

.aip-alert { padding: 12px 15px; border-radius: 4px; margin-bottom: 15px; font-size: 0.9rem; display: flex; gap: 10px; align-items: flex-start; }
.aip-alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #bee5eb; }
.aip-alert-success { background: #d4edda; color: #155724; border-left: 4px solid #c3e6cb; }
.aip-alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #f5c6cb; }
.aip-alert code { background: rgba(0,0,0,0.07); padding: 1px 5px; border-radius: 3px; font-size: 0.85em; }

.aip-key-display { font-family: monospace; font-size: 0.85rem; color: #555; }

.aip-switch { position: relative; display: inline-block; width: 36px; height: 20px; }
.aip-switch input { opacity: 0; width: 0; height: 0; }
.aip-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #ccc; transition: 0.3s; border-radius: 20px; }
.aip-slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background: #fff; transition: 0.3s; border-radius: 50%; }
.aip-switch input:checked + .aip-slider { background: #28a745; }
.aip-switch input:checked + .aip-slider:before { transform: translateX(16px); }

#aipFormCard { border-color: #7c5cc4; box-shadow: 0 2px 8px rgba(124, 92, 196, 0.08); }
</style>

<section>
    <div class="container-fluid">

        @if(session('not_permitted'))
            <div class="aip-alert aip-alert-danger">
                <strong>Access Denied:</strong> {{ session('not_permitted') }}
            </div>
        @endif

        <div class="aip-card">
            <div class="aip-card-header">
                <h3 class="aip-card-title">AI Provider Settings</h3>
                
                
                <button type="button" class="aip-btn aip-btn-primary" id="aipAddBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Provider
                </button>
               
                
            </div>
            <div class="aip-card-body">

                <div class="aip-alert aip-alert-info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <div>
                        <strong>How this works:</strong> API keys are encrypted at rest using Laravel's <code>encrypt()</code>. Only the last 4 characters are visible in this list. Only one provider can be active at a time — enabling a new one will automatically disable the rest.
                    </div>
                </div>

                {{-- Inline Add/Edit form (toggled via JS) --}}
                
                
                <div class="aip-card" id="aipFormCard" style="display: none; margin-bottom: 20px;">
                    <div class="aip-card-header" style="background: #f8f6fd;">
                        <h4 class="aip-card-title" id="aipFormTitle" style="font-size: 1rem;">New Provider</h4>
                        <button type="button" class="aip-toggle-btn" id="aipCloseFormBtn" aria-label="Close form" style="font-size: 1.2rem; line-height: 1;">×</button>
                    </div>
                    <div class="aip-card-body">
                        <form id="aipForm">
                            <input type="hidden" id="aipEditId" value="">

                            <div class="aip-form-row">
                                <div class="aip-form-group">
                                    <label class="aip-form-label" for="aipProvider">Provider <span class="req">*</span></label>
                                    <select id="aipProvider" class="aip-form-control" required>
                                        <option value="">— Select —</option>
                                        <option value="openai">OpenAI</option>
                                        <option value="anthropic">Anthropic (Claude)</option>
                                        <option value="google">Google (Gemini)</option>
                                        <option value="groq">Groq</option>
                                        <option value="mistral">Mistral</option>
                                        <option value="deepseek">DeepSeek</option>
                                        <option value="ollama">Ollama (local)</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                </div>
                                <div class="aip-form-group">
                                    <label class="aip-form-label" for="aipApiKey">API Key <span class="req" id="aipKeyReq">*</span></label>
                                    <input type="password" id="aipApiKey" class="aip-form-control" placeholder="sk-..." required>
                                </div>
                            </div>

                            <div class="aip-form-row">
                                <div class="aip-form-group">
                                    <label class="aip-form-label" for="aipBaseUrl">Base URL</label>
                                    <input type="url" id="aipBaseUrl" class="aip-form-control" placeholder="https://api.openai.com/v1">
                                </div>
                                <div class="aip-form-group">
                                    <label class="aip-form-label" for="aipModel">Model</label>
                                    <input type="text" id="aipModel" class="aip-form-control" placeholder="gpt-4o, claude-3-opus, gemini-pro...">
                                </div>
                            </div>

                            <div class="aip-form-row">
                                <div class="aip-form-group">
                                    <label class="aip-form-label" for="aipSettings">Settings (JSON, optional)</label>
                                    <textarea id="aipSettings" class="aip-form-control" placeholder='{"temperature":0.7,"max_tokens":1000}'></textarea>
                                </div>
                                <div class="aip-form-group">
                                    <label class="aip-form-label">Active</label>
                                    <label class="aip-switch" style="margin-top: 5px;">
                                        <input type="checkbox" id="aipEnabled">
                                        <span class="aip-slider"></span>
                                    </label>
                                    <small style="font-size: 0.75rem; color: #999; margin-top: 5px; display: block;">Only one provider can be active at a time.</small>
                                </div>
                            </div>

                            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px;">
                                <button type="button" class="aip-btn aip-btn-secondary" id="aipCancelBtn">Cancel</button>
                                <button type="submit" class="aip-btn aip-btn-primary" id="aipSaveBtn">
                                    <span id="aipSaveText">Save Provider</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
               
                

                {{-- Providers table --}}
                <div class="aip-table-wrap">
                    <table class="aip-table" id="aipProvidersTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Provider</th>
                                <th>API Key</th>
                                <th>Base URL</th>
                                <th>Model</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="aipProvidersBody">
                            @forelse ($providers as $provider)
                                <tr data-id="{{ $provider->id }}"
                                    data-provider="{{ $provider->provider }}"
                                    data-base-url="{{ $provider->base_url ?? '' }}"
                                    data-model="{{ $provider->model ?? '' }}"
                                    data-settings="{{ is_array($provider->settings) ? json_encode($provider->settings) : '' }}"
                                    data-key-last4="{{ $provider->api_key ? substr($provider->api_key, -4) : '' }}"
                                    data-is-enabled="{{ $provider->is_enabled ? 1 : 0 }}">
                                    <td>{{ $provider->id }}</td>
                                    <td>
                                        <strong style="text-transform: capitalize;">{{ $provider->provider }}</strong>
                                        @if ($provider->model)
                                            <br><small style="color: #999;">{{ $provider->model }}</small>
                                        @endif
                                    </td>
                                    <td><span class="aip-key-display">{{ $provider->masked_api_key ?: '—' }}</span></td>
                                    <td>
                                        @if ($provider->base_url)
                                            <span style="font-size: 0.8rem; color: #555;">{{ $provider->base_url }}</span>
                                        @else
                                            <span style="color: #999;">Default</span>
                                        @endif
                                    </td>
                                    <td>{{ $provider->model ?: '—' }}</td>
                                    <td>
                                        @if ($provider->is_enabled)
                                            <span class="aip-badge aip-badge-success">Active</span>
                                        @else
                                            <span class="aip-badge aip-badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="aip-actions">
                                            @if(Auth::user()->can('super-admin'))
                                            <button class="aip-toggle-btn @if($provider->is_enabled) enabled @else disabled @endif" data-toggle="{{ $provider->id }}">
                                                @if ($provider->is_enabled) Disable @else Enable @endif
                                            </button>
                                            <button class="aip-btn aip-btn-secondary aip-btn-sm" data-edit="{{ $provider->id }}">Edit</button>
                                            <button class="aip-btn aip-btn-danger aip-btn-sm" data-delete="{{ $provider->id }}">Delete</button>
                                            @else
                                            <span style="color: #999; font-size: 0.85rem;">Read‑only</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="aip-empty">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                            <h5>No AI providers configured yet</h5>
                                            <p>Add your first provider to start using the AI Assistant with real LLM APIs.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const apiBase    = "{{ route('ai-assistant.providers.index') }}";

    const els = {
        addBtn:       document.getElementById('aipAddBtn'),
        formCard:     document.getElementById('aipFormCard'),
        formTitle:    document.getElementById('aipFormTitle'),
        form:         document.getElementById('aipForm'),
        editId:       document.getElementById('aipEditId'),
        provider:     document.getElementById('aipProvider'),
        apiKey:       document.getElementById('aipApiKey'),
        apiKeyReq:    document.getElementById('aipKeyReq'),
        baseUrl:      document.getElementById('aipBaseUrl'),
        model:        document.getElementById('aipModel'),
        settings:     document.getElementById('aipSettings'),
        enabled:      document.getElementById('aipEnabled'),
        cancelBtn:    document.getElementById('aipCancelBtn'),
        closeFormBtn: document.getElementById('aipCloseFormBtn'),
        saveBtn:      document.getElementById('aipSaveBtn'),
        saveText:     document.getElementById('aipSaveText'),
        tableBody:    document.getElementById('aipProvidersBody')
    };

    function openForm(isEdit = false) {
        if (!els.formCard) return;
        els.formCard.style.display = 'block';
        els.formTitle.textContent = isEdit ? 'Edit Provider' : 'New Provider';
        els.saveText.textContent = isEdit ? 'Update Provider' : 'Save Provider';
        els.formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function closeForm() {
        if (!els.formCard) return;
        els.formCard.style.display = 'none';
        els.form.reset();
        els.editId.value = '';
        els.apiKey.required = true;
        els.apiKeyReq.style.display = 'inline';
        els.apiKey.placeholder = 'sk-...';
        delete els.apiKey.dataset.last4;
    }

    function showFlash(message, type = 'success') {
        const cls = type === 'success' ? 'aip-alert-success' : 'aip-alert-danger';
        const alert = document.createElement('div');
        alert.className = 'aip-alert ' + cls;
        alert.innerHTML = '<div>' + message + '</div>';
        alert.style.marginBottom = '15px';
        els.formCard.parentNode.insertBefore(alert, els.formCard);
        setTimeout(() => alert.remove(), 4500);
    }

    async function apiCall(endpoint, method = 'GET', body = null) {
        const opts = {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        };
        if (body) opts.body = JSON.stringify(body);

        const res = await fetch(apiBase + endpoint, opts);
        const data = await res.json().catch(() => null);

        if (!res.ok) {
            let msg = 'Request failed.';
            if (data && data.errors) {
                msg = Object.values(data.errors).flat().join(' ');
            } else if (data && data.error) {
                msg = data.error;
            } else if (data && data.message) {
                msg = data.message;
            }
            throw new Error(msg);
        }
        return data;
    }

    // --- Add new ---
    if (els.addBtn) {
        els.addBtn.addEventListener('click', () => {
            closeForm();
            openForm(false);
            els.provider.focus();
        });
    }

    if (els.cancelBtn)   els.cancelBtn.addEventListener('click', closeForm);
    if (els.closeFormBtn) els.closeFormBtn.addEventListener('click', closeForm);

    // --- Submit form (create or update) ---
    if (els.form) {
        els.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            els.saveBtn.disabled = true;

            const editId = els.editId.value;
            const payload = {
                provider:   els.provider.value,
                api_key:     els.apiKey.value,
                base_url:    els.baseUrl.value || null,
                model:       els.model.value || null,
                settings:    els.settings.value || null,
                is_enabled:  els.enabled.checked ? 1 : 0
            };

            // On edit, drop the api_key if the user left it blank (don't overwrite).
            if (editId) {
                if (!payload.api_key || payload.api_key.includes('•')) {
                    delete payload.api_key;
                }
            }

            try {
                if (editId) {
                    await apiCall('/' + editId, 'PUT', payload);
                    showFlash('Provider updated successfully.', 'success');
                } else {
                    await apiCall('', 'POST', payload);
                    showFlash('Provider created successfully.', 'success');
                }
                closeForm();
                setTimeout(() => location.reload(), 800);
            } catch (err) {
                showFlash(err.message, 'danger');
            } finally {
                els.saveBtn.disabled = false;
            }
        });
    }

    // --- Edit (populate from data-* attrs on the row) ---
    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('[data-edit]');
        if (!editBtn) return;
        const id  = editBtn.dataset.edit;
        const row = document.querySelector('tr[data-id="' + id + '"]');
        if (!row) return;

        closeForm();
        openForm(true);

        els.editId.value      = id;
        els.provider.value    = row.dataset.provider    || '';
        els.baseUrl.value     = row.dataset.baseUrl     || '';
        els.model.value       = row.dataset.model       || '';
        els.settings.value    = row.dataset.settings    || '';
        els.enabled.checked   = row.dataset.isEnabled === '1';

        // Mark api_key as optional on edit — user can leave it blank to keep current.
        els.apiKey.required = false;
        els.apiKeyReq.style.display = 'none';
        els.apiKey.value = '';
        els.apiKey.placeholder = 'Leave blank to keep current (' + (row.dataset.keyLast4 ? '••••' + row.dataset.keyLast4 : 'none') + ')';
        els.apiKey.dataset.last4 = row.dataset.keyLast4 || '';

        els.provider.focus();
    });

    // --- Toggle enable/disable ---
    document.addEventListener('click', async (e) => {
        const toggleBtn = e.target.closest('[data-toggle]');
        if (!toggleBtn) return;
        const id = toggleBtn.dataset.toggle;

        try {
            await apiCall('/' + id + '/toggle', 'POST');
            location.reload();
        } catch (err) {
            showFlash(err.message, 'danger');
        }
    });

    // --- Delete ---
    document.addEventListener('click', async (e) => {
        const delBtn = e.target.closest('[data-delete]');
        if (!delBtn) return;
        const id = delBtn.dataset.delete;
        if (!confirm('Delete this provider? This cannot be undone.')) return;

        try {
            await apiCall('/' + id, 'DELETE');
            showFlash('Provider deleted.', 'success');
            setTimeout(() => location.reload(), 600);
        } catch (err) {
            showFlash(err.message, 'danger');
        }
    });
});
</script>
@endpush