<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php($appName = config('app.name', 'RentB'))
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
{{-- Favicon / Tab icon --}}
<link rel="icon" type="image/jpeg" href="{{ asset('logos/blogo.jpg') }}">
<link rel="shortcut icon" type="image/jpeg" href="{{ asset('logos/blogo.jpg') }}">
<link rel="apple-touch-icon" href="{{ asset('logos/blogo.jpg') }}">
<meta name="theme-color" content="#ffffff">

    @hasSection('title')
      <title>@yield('title') — {{ $appName }}</title>
    @else
      <title>{{ $appName }}</title>
    @endif

    {{-- Bootstrap & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    

    {{-- SweetAlert2 (for toasts) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    {{-- App-wide tweaks --}}
    <style>
      /* Center the built-in "empty" message for all DataTables */
      table.dataTable tbody td.dataTables_empty {
        text-align: center !important;
        color: #6b7280;
        padding: 2rem 0 !important;
      }
      /* Smooth anchor hover */
      a { text-decoration: none; }
      a:hover { text-decoration: underline; }

      /* Optional sticky navbar spacing (uncomment if your navbar is fixed-top)
      body { padding-top: 64px; } */

      /* Light/Dark friendly borders */
      .border-top-soft { border-top: 1px solid rgba(0,0,0,.06); }
    </style>

    @stack('head')
</head>
<body>
    {{-- Top Navigation --}}
    @include('layouts.partials.navbar')

    {{-- Main Content --}}
    <main class="container-fluid py-3">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('layouts.partials.footer')

    {{-- Core Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Flash & validation toasts --}}
    @if(session('success') || session('error') || $errors->any())
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3200,
          timerProgressBar: true
        });

        @if(session('success'))
          Toast.fire({ icon: 'success', title: @json(session('success')) });
        @endif
        @if(session('error'))
          Toast.fire({ icon: 'error', title: @json(session('error')) });
        @endif
        @if($errors->any())
          Toast.fire({ icon: 'error', title: @json($errors->first()) });
        @endif
      });
    </script>
    @endif

    @stack('scripts')
</body>
</html>
