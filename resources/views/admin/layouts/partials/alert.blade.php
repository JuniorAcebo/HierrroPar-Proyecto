    @if (session('success'))
    <script>
        Swal.fire({
    icon: 'success',
    title: '¡Éxito!',
    text: `{!! session('success') !!}`,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    </script>
    @endif

    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ session('error') }}",
            confirmButtonColor: '#3085d6'
        });
    </script>
    @endif

    @if ($errors->any() && !old('_form'))
<script>
Swal.fire({
    icon: 'warning',
    title: 'Validación',
    html: `{!! implode('', $errors->all('<li>:message</li>')) !!}`
});
</script>
@endif