jQuery(document).ready(function($) {
    function loadNotes() {
        $.post(nm_ajax.ajax_url, { action: 'get_notes', _ajax_nonce: nm_ajax.nonce }, function(response) {
            if(response.success) {
                $('#notes-list').html('');
                response.data.forEach(note => {
                    $('#notes-list').append(
                        `<li data-id="${note.id}">${note.note} <button class="delete-note button-link-delete">Delete</button></li>`
                    );
                });
            }
        });
    }

    $('#note-form').on('submit', function(e) {
        e.preventDefault();
        const note = $('textarea[name="note"]').val();
        $.post(nm_ajax.ajax_url, {
            action: 'add_note',
            note: note,
            _ajax_nonce: nm_ajax.nonce
        }, function(response) {
            if(response.success) {
                $('textarea[name="note"]').val('');
                loadNotes();
            }
        });
    });

    $('#notes-list').on('click', '.delete-note', function() {
        const li = $(this).closest('li');
        const id = li.data('id');
        $.post(nm_ajax.ajax_url, {
            action: 'delete_note',
            id: id,
            _ajax_nonce: nm_ajax.nonce
        }, function(response) {
            if(response.success) {
                li.remove();
            }
        });
    });

    // Load on page
    loadNotes();
});
