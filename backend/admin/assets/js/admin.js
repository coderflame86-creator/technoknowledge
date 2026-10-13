/**
 * Technoknowledge Admin Dashboard JavaScript
 * Handles modal view, AJAX status updates, notes saving, and actions.
 */

(function () {
  'use strict';

  // Toast notification
  function showToast(message, type) {
    var old = document.querySelector('.toast-notice');
    if (old) old.remove();

    var toast = document.createElement('div');
    toast.className = 'toast-notice';
    if (type === 'error') {
      toast.style.backgroundColor = '#dc2626';
    } else {
      toast.style.backgroundColor = '#13294B';
    }
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(function () {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.3s';
      setTimeout(function () { toast.remove(); }, 300);
    }, 3500);
  }

  // Modal controls
  var backdrop = document.getElementById('leadModalBackdrop');
  var closeBtn = document.getElementById('modalCloseBtn');
  var modalTitle = document.getElementById('modalTitle');
  var modalDetails = document.getElementById('modalDetails');
  var modalStatusSelect = document.getElementById('modalStatusSelect');
  var modalNotesTextarea = document.getElementById('modalNotesTextarea');
  var modalSaveBtn = document.getElementById('modalSaveBtn');
  var modalWhatsAppBtn = document.getElementById('modalWhatsAppBtn');
  var currentSubmissionId = null;

  function closeModal() {
    if (backdrop) backdrop.classList.remove('is-open');
    currentSubmissionId = null;
  }

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (backdrop) {
    backdrop.addEventListener('click', function (e) {
      if (e.target === backdrop) closeModal();
    });
  }

  // Mobile Navigation Drawer controls
  var menuToggle = document.getElementById('adminMenuToggle');
  var navCollapse = document.getElementById('adminNavCollapse');
  var navOverlay = document.getElementById('adminNavOverlay');
  var drawerClose = document.getElementById('adminDrawerClose');

  function openNav() {
    if (navCollapse) navCollapse.classList.add('is-open');
    if (navOverlay) navOverlay.classList.add('is-open');
    if (menuToggle) {
      menuToggle.classList.add('is-active');
      menuToggle.setAttribute('aria-expanded', 'true');
    }
    document.body.classList.add('nav-locked');
  }

  function closeNav() {
    if (navCollapse) navCollapse.classList.remove('is-open');
    if (navOverlay) navOverlay.classList.remove('is-open');
    if (menuToggle) {
      menuToggle.classList.remove('is-active');
      menuToggle.setAttribute('aria-expanded', 'false');
    }
    document.body.classList.remove('nav-locked');
  }

  function toggleNav() {
    if (navCollapse && navCollapse.classList.contains('is-open')) {
      closeNav();
    } else {
      openNav();
    }
  }

  if (menuToggle) menuToggle.addEventListener('click', toggleNav);
  if (drawerClose) drawerClose.addEventListener('click', closeNav);
  if (navOverlay) navOverlay.addEventListener('click', closeNav);

  // Close mobile drawer when window resized past breakpoint
  window.addEventListener('resize', function () {
    if (window.innerWidth > 1080 && navCollapse && navCollapse.classList.contains('is-open')) {
      closeNav();
    }
  });

  // Handle ESC key to close modal and drawer
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      if (navCollapse && navCollapse.classList.contains('is-open')) {
        closeNav();
      }
      if (backdrop && backdrop.classList.contains('is-open')) {
        closeModal();
      }
    }
  });

  // Open modal from lead data
  window.openLeadModal = function (data) {
    currentSubmissionId = data.id;
    if (!backdrop) return;

    modalTitle.textContent = (data.form_label || 'Submission') + ' #' + data.id;

    // Build details table
    var fields = [
      ['Contact Name', data.name],
      ['WhatsApp Number', data.whatsapp],
      ['Email Address', data.email],
      ['Institution / School', data.institution],
      ['Role / Designation', data.role],
      ['City', data.city],
      ['Child Name', data.child_name],
      ['Child Age', data.child_age],
      ['Selected Courses', data.courses],
      ['Class Type', data.class_type],
      ['Program', data.program],
      ['Interest / Option', data.interest],
      ['Interests', data.interests],
      ['I Am A', data.iam],
      ['Subject', data.subject],
      ['Message', data.message],
      ['Visitor Notes', data.notes],
      ['Teachers Count', data.teachers_count],
      ['Students Count', data.students_count],
      ['Heard From', data.source],
      ['Sent From Page', data.page],
      ['Consent Given', data.consent ? 'Yes' : 'No'],
      ['IP Address', data.ip_address],
      ['Submission Date', data.created_at + ' (PKT)']
    ];

    var html = '<table class="detail-table"><tbody>';
    fields.forEach(function (f) {
      if (f[1] && String(f[1]).trim() !== '') {
        html += '<tr><th>' + escapeHtml(f[0]) + '</th><td>' + escapeHtml(String(f[1])) + '</td></tr>';
      }
    });
    html += '</tbody></table>';

    modalDetails.innerHTML = html;

    // Set status and notes
    if (modalStatusSelect) modalStatusSelect.value = data.status || 'New';
    if (modalNotesTextarea) modalNotesTextarea.value = data.admin_notes || '';

    // Set WhatsApp link
    if (modalWhatsAppBtn) {
      var phone = (data.whatsapp || '').replace(/\D+/g, '');
      if (phone.startsWith('03') && phone.length === 11) {
        phone = '92' + phone.substring(1);
      } else if (phone.startsWith('3') && phone.length === 10) {
        phone = '92' + phone;
      }

      if (phone) {
        var msg = 'Hello ' + encodeURIComponent(data.name || 'there') + ', thank you for contacting Technoknowledge. How may we assist you today?';
        modalWhatsAppBtn.href = 'https://wa.me/' + phone + '?text=' + msg;
        modalWhatsAppBtn.style.display = 'inline-flex';
      } else {
        modalWhatsAppBtn.style.display = 'none';
      }
    }

    backdrop.classList.add('is-open');
  };

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  // Save status & admin notes
  if (modalSaveBtn) {
    modalSaveBtn.addEventListener('click', function () {
      if (!currentSubmissionId) return;

      var newStatus = modalStatusSelect ? modalStatusSelect.value : 'New';
      var newNotes = modalNotesTextarea ? modalNotesTextarea.value : '';

      modalSaveBtn.disabled = true;
      modalSaveBtn.textContent = 'Saving…';

      fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'update_lead',
          id: currentSubmissionId,
          status: newStatus,
          notes: newNotes,
          csrf_token: window.CSRF_TOKEN || ''
        })
      })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          if (json.ok) {
            showToast('Lead updated successfully!');
            // Update table row if visible
            var row = document.querySelector('tr[data-id="' + currentSubmissionId + '"]');
            if (row) {
              var statusBadge = row.querySelector('.badge-status');
              if (statusBadge) {
                statusBadge.textContent = newStatus;
                statusBadge.className = 'badge badge-status badge-status-' + newStatus;
              }
            }
            closeModal();
          } else {
            showToast(json.message || 'Error updating lead.', 'error');
          }
        })
        .catch(function () {
          showToast('Network error while saving.', 'error');
        })
        .finally(function () {
          modalSaveBtn.disabled = false;
          modalSaveBtn.textContent = 'Save Changes';
        });
    });
  }

  // Delete submission
  window.deleteSubmission = function (id) {
    if (!confirm('Are you sure you want to permanently delete submission #' + id + '? This action cannot be undone.')) {
      return;
    }

    fetch('api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'delete_submission',
        id: id,
        csrf_token: window.CSRF_TOKEN || ''
      })
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (json.ok) {
          showToast('Submission deleted.');
          var row = document.querySelector('tr[data-id="' + id + '"]');
          if (row) row.remove();
        } else {
          showToast(json.message || 'Error deleting.', 'error');
        }
      })
      .catch(function () {
        showToast('Network error while deleting.', 'error');
      });
  };

})();
