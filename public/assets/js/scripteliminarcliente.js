function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Eliminar cliente?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Crear formulario dinámico para enviar DELETE
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/clientes/${id}`;
            form.style.display = 'none';
            
            // Agregar CSRF y método spoofing
            const csrf = document.createElement('input');
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            
            const method = document.createElement('input');
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(method);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Inicializar Feather Icons
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
});