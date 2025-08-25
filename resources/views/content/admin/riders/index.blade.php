@extends('layouts/layoutMaster')

@section('title', 'Gestión de Riders')

{{-- Carga de Estilos Específicos de la Página --}}
@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

{{-- Carga de Scripts Específicos de la Página --}}
@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

{{-- SECCIÓN DE SCRIPT VERIFICADA --}}
@section('page-script')
  @vite('resources/assets/js/riders-list.js')
  @vite('resources/assets/js/schedule-picker.js')

  <script>
    @if (session('success'))
      Swal.fire({
        icon: 'success',
        title: '¡Hecho!',
        text: '{{ session('success') }}',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
    @endif
  </script>
@endsection

@section('content')
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Admin /</span> Riders
  </h4>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Listado de Riders</h5>
      <a href="{{ route('admin.riders.create') }}" class="btn btn-primary">
        <i class="ti tabler-plus me-1"></i> Crear Rider
      </a>
    </div>
    <div class="card-datatable table-responsive">
      <table class="table" id="riders-table">
        <thead class="border-top">
          <tr>
            <th>ID</th>
            <th>Nombre Completo</th>
            <th>DNI</th>
            <th>Ciudad</th>
            <th>Email</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
@endsection
