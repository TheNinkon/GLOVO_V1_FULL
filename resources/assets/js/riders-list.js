// Configuración global de AJAX para enviar siempre el token CSRF
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

('use strict');

$(function () {
  const dt_basic_table = $('#riders-table');
  let dataTable;

  if (dt_basic_table.length) {
    dataTable = dt_basic_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: '/admin/riders',
      columns: [
        { data: 'id', name: 'id' },
        { data: 'full_name', name: 'full_name' },
        { data: 'dni', name: 'dni' },
        { data: 'city', name: 'city' },
        { data: 'email', name: 'email' },
        { data: 'status', name: 'status' },
        { data: 'action', name: 'action', orderable: false, searchable: false }
      ]
      // SE HA ELIMINADO LA OPCIÓN "language" DE AQUÍ
    });
  }

  // La lógica para el borrado con SweetAlert2 se queda igual
  $(document).on('click', '.delete-rider-btn', function () {
    const deleteUrl = $(this).data('url');

    Swal.fire({
      title: '¿Estás seguro?',
      text: '¡No podrás revertir esta acción!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, ¡eliminar!',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.isConfirmed) {
        $.ajax({
          url: deleteUrl,
          type: 'DELETE',
          success: function (response) {
            if (dataTable) {
              dataTable.ajax.reload();
            }
            Swal.fire({
              icon: 'success',
              title: '¡Eliminado!',
              text: response.message,
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 3000,
              timerProgressBar: true
            });
          },
          error: function (xhr) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'No se pudo eliminar el rider.',
              customClass: {
                confirmButton: 'btn btn-primary'
              }
            });
          }
        });
      }
    });
  });
});
