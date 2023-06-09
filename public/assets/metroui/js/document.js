// Agregar filas de productos en la vista REGISTRAR ENTRADA

var $producto_movimiento_contenedor = $('#producto-movimiento-contenedor');

$producto_movimiento_contenedor.attr('data-index', $producto_movimiento_contenedor.children().length);

$producto_movimiento_contenedor.on('click', '.producto-movimiento-fila-eliminar', function (e) {
    e.preventDefault();

    var filas = document.getElementsByClassName('producto-movimiento-fila').length;
    if(filas > 1){
        $(this).closest('.producto-movimiento-fila').fadeOut().remove();
    }
});

$(document).on('click', '#producto-movimiento-fila-agregar', function (e) {
    e.preventDefault();
    var prototype = $producto_movimiento_contenedor.data('prototype');
    var index = $producto_movimiento_contenedor.data('index');
    var newForm = prototype.replace(/__name__/g, index);
    $producto_movimiento_contenedor.data('index', index + 1);
    $producto_movimiento_contenedor.append(newForm);
});

// Agregar filas de almacen-producto en la vista REGISTRAR VENTA

var $almacen_producto_movimiento_contenedor = $('#almacen-producto-movimiento-contenedor');

$almacen_producto_movimiento_contenedor.attr('data-index', $almacen_producto_movimiento_contenedor.children().length);

$almacen_producto_movimiento_contenedor.on('click', '.almacen-producto-movimiento-fila-eliminar', function (e) {
    e.preventDefault();
    var filas = document.getElementsByClassName('almacen-producto-movimiento-fila').length;
    if(filas > 1){
        $(this).closest('.almacen-producto-movimiento-fila').fadeOut().remove();
    }
});

$(document).on('click', '#almacen-producto-movimiento-fila-agregar', function (e) {
    e.preventDefault();
    var prototype = $almacen_producto_movimiento_contenedor.data('prototype');
    var index = $almacen_producto_movimiento_contenedor.data('index');
    var newForm = prototype.replace(/__name__/g, index);
    $almacen_producto_movimiento_contenedor.data('index', index + 1);
    $almacen_producto_movimiento_contenedor.append(newForm);
});

$(document).on('click', '.button-link', function (e) {
    console.log('.button-link clicked!');
    e.preventDefault();
    window.location = $(this).data('href');
});
