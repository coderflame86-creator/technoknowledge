    </main>

    <!-- Lead Detail Modal -->
    <div id="leadModalBackdrop" class="modal-backdrop">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle" class="card-title">Submission Details</h3>
                <button type="button" id="modalCloseBtn" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="modalDetails"></div>

                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--tk-border);">
                    <h4 style="font-size: 15px; margin-bottom: 12px; color: var(--tk-navy);">Lead Status & Action</h4>
                    
                    <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                        <a id="modalWhatsAppBtn" href="#" target="_blank" class="btn btn-whatsapp" style="display: none;">
                            💬 Reply on WhatsApp
                        </a>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label for="modalStatusSelect" style="display: block; font-weight: 700; font-size: 13px; color: var(--tk-text-muted); margin-bottom: 6px;">Update Status:</label>
                        <select id="modalStatusSelect" class="form-select" style="width: 100%;">
                            <option value="New">New (Pending Action)</option>
                            <option value="Contacted">Contacted</option>
                            <option value="Demo / meeting booked">Demo / meeting booked</option>
                            <option value="Proposal sent">Proposal sent</option>
                            <option value="Enrolled / signed">Enrolled / signed</option>
                            <option value="Not interested">Not interested</option>
                        </select>
                    </div>

                    <div>
                        <label for="modalNotesTextarea" style="display: block; font-weight: 700; font-size: 13px; color: var(--tk-text-muted); margin-bottom: 6px;">Internal Admin Follow-up Notes:</label>
                        <textarea id="modalNotesTextarea" class="form-input" style="width: 100%; min-height: 80px; resize: vertical;" placeholder="Add internal notes, phone call logs, follow-up schedule..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('leadModalBackdrop').classList.remove('is-open')" class="btn btn-outline">Close</button>
                <button type="button" id="modalSaveBtn" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>
