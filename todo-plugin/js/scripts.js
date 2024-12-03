jQuery(document).ready(function ($) {
    // Aufgabe hinzufügen
    $('#add-task-form').submit(function (e) {
        e.preventDefault();
        const task = $(this).find('input[name="new_task"]').val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'add_task',
                task: task,
            },
            success: function (response) {
                if (response.success) {
                    const newRow = `
                        <tr>
                            <td>${response.data.id}</td>
                            <td>${response.data.task}</td>
                            <td>
                                <form class="update-form">
                                    <input type="hidden" name="update_id" value="${response.data.id}">
                                    <input type="text" name="update_task" value="${response.data.task}" required>
                                    <button type="submit" class="button button-primary">Speichern</button>
                                </form>
                            </td>
                            <td>
                                <a href="#" class="button delete-button" data-id="${response.data.id}">Löschen</a>
                            </td>
                        </tr>`;
                    $('table tbody').append(newRow);
                }
            }
        });
    });

    // Aufgabe aktualisieren
    $(document).on('submit', '.update-form', function (e) {
        e.preventDefault();
        const form = $(this);
        const id = form.find('input[name="update_id"]').val();
        const task = form.find('input[name="update_task"]').val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_task',
                id: id,
                task: task
            },
            success: function (response) {
                if (response.success) {
                    form.closest('tr').find('td:nth-child(2)').text(response.data.task);
                } else {
                    alert('Fehler beim Aktualisieren: ' + response.data.message);
                }
            },
            error: function () {
                alert('Ein Fehler ist aufgetreten.');
            }
        });
    });

    // Aufgabe löschen
    $(document).on('click', '.delete-button', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_task',
                id: id,
            },
            success: function () {
                $(`a[data-id="${id}"]`).closest('tr').remove();
            }
        });
    });
});
