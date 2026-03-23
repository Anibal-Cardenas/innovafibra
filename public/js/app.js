// Custom JavaScript - Sistema de Gestión de Producción Napa

$(document).ready(function() {
    
    // Confirmación antes de eliminar/anular
    $('.btn-delete, .btn-anular').on('click', function(e) {
        if (!confirm('¿Está seguro de realizar esta acción?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Auto-hide flash messages después de 5 segundos
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
    
    // Formatear campos de moneda
    $('.currency-input').on('blur', function() {
        let value = parseFloat($(this).val());
        if (!isNaN(value)) {
            $(this).val(value.toFixed(2));
        }
    });
    
    // Calcular precio total en formularios de venta/compra
    $('#cantidad, #precio_unitario').on('input', function() {
        calcularPrecioTotal();
    });
    
    function calcularPrecioTotal() {
        let cantidad = parseFloat($('#cantidad').val()) || 0;
        let precioUnitario = parseFloat($('#precio_unitario').val()) || 0;
        let total = cantidad * precioUnitario;
        $('#precio_total').val(total.toFixed(2));
    }
    
    // Calcular precio por kg en compra de fibra
    $('#peso_neto, #precio_total_fibra').on('input', function() {
        calcularPrecioPorKg();
    });
    
    function calcularPrecioPorKg() {
        let pesoNeto = parseFloat($('#peso_neto').val()) || 0;
        let precioTotal = parseFloat($('#precio_total_fibra').val()) || 0;
        if (pesoNeto > 0) {
            let precioPorKg = precioTotal / pesoNeto;
            $('#precio_por_kg_display').text('S/ ' + precioPorKg.toFixed(2) + ' por kg');
        }
    }
    
    // Validar que peso neto <= peso bruto
    $('#peso_neto, #peso_bruto').on('blur', function() {
        let pesoNeto = parseFloat($('#peso_neto').val()) || 0;
        let pesoBruto = parseFloat($('#peso_bruto').val()) || 0;
        
        if (pesoNeto > pesoBruto) {
            alert('El peso neto no puede ser mayor al peso bruto');
            $('#peso_neto').val('');
            $('#peso_neto').focus();
        }
    });
    
    // Calcular consumo de bolsas en producción
    $('#cantidad_producida').on('input', function() {
        let cantidad = parseFloat($(this).val()) || 0;
        let factor = parseFloat($('#factor_conversion').val()) || 0.02;
        let consumo = cantidad * factor;
        $('#consumo_bolsas_display').text(consumo.toFixed(2) + ' kg');
    });
    
    // Mostrar información del lote seleccionado en producción
    $('#id_lote_fibra').on('change', function() {
        let loteId = $(this).val();
        if (loteId) {
            $.ajax({
                url: BASE_URL + '/api/lotes/' + loteId,
                method: 'GET',
                success: function(data) {
                    if (data.success) {
                        $('#info_lote').html(`
                            <div class="alert alert-info">
                                <strong>Lote: ${data.lote.codigo_lote}</strong><br>
                                Estimado: ${data.lote.cantidad_estimada_bolsas} bolsas<br>
                                Producido: ${data.lote.cantidad_producida_real} bolsas<br>
                                Pendiente: ${data.lote.cantidad_estimada_bolsas - data.lote.cantidad_producida_real} bolsas
                            </div>
                        `);
                    }
                }
            });
        }
    });
    
    // Validación de formularios
    $('form').on('submit', function(e) {
        let isValid = true;
        
        // Validar campos requeridos
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Por favor complete todos los campos requeridos');
            return false;
        }
    });
    
    // Limpiar validación al escribir
    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('is-invalid');
    });
    
    // DataTables para tablas grandes
    if ($.fn.DataTable) {
        $('.data-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            pageLength: 25,
            order: [[0, 'desc']]
        });
    }
    
    // Tooltip de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
});

// Función para mostrar loading
function showLoading() {
    $('body').append(`
        <div class="spinner-overlay">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `);
}

function hideLoading() {
    $('.spinner-overlay').remove();
}

// Función para formatear moneda
function formatCurrency(amount) {
    return 'S/ ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Función para formatear número
function formatNumber(number, decimals = 2) {
    return parseFloat(number).toFixed(decimals).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}
