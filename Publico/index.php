<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas Rápidas</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <header class="cabecera">
        <h1>Ventas Rápidas</h1>
        <nav class="menu">
            <button class="menu-boton activo" data-vista="vista-comerciante">Registrar Comerciante</button>
            <button class="menu-boton" data-vista="vista-local">Registrar Local</button>
            <button class="menu-boton" data-vista="vista-producto">Registrar Producto</button>
            <button class="menu-boton" data-vista="vista-cliente">Registrar Cliente</button>
            <button class="menu-boton" data-vista="vista-listado">Ver Locales</button>
        </nav>
    </header>

    <main class="contenedor">

        <div id="mensaje" class="mensaje oculto"></div>

        <!-- Vista: Registrar Comerciante -->
        <section id="vista-comerciante" class="vista">
            <h2>Registro de Comerciante</h2>
            <form id="form-comerciante" class="formulario" enctype="multipart/form-data">
                <label for="c-nombre">Nombre completo</label>
                <input type="text" id="c-nombre" required>

                <label for="c-alias">Alias</label>
                <input type="text" id="c-alias" required>

                <label for="c-tipoIdentificacion">Tipo de identificación</label>
                <select id="c-tipoIdentificacion" required>
                    <option value="Cedula">Cédula física</option>
                    <option value="DIMEX">DIMEX (residente extranjero)</option>
                    <option value="Pasaporte">Pasaporte</option>
                </select>

                <label for="c-numeroIdentificacion">Número de identificación</label>
                <input type="text" id="c-numeroIdentificacion" placeholder="Sin espacios ni guiones" required>
                <span class="ayuda" id="c-identificacion-msg"></span>

                <label for="c-correo">Correo</label>
                <input type="email" id="c-correo" placeholder="ejemplo@gmail.com" required>
                <span class="ayuda" id="c-correo-msg"></span>

                <label for="c-password">Contraseña</label>
                <input type="password" id="c-password" required>

                <label for="c-fotoPerfil">Foto de perfil</label>
                <input type="file" id="c-fotoPerfil" accept="image/png, image/jpeg, image/webp">

                <button type="submit">Registrar Comerciante</button>
            </form>
        </section>

        <!-- Vista: Registrar Producto -->
        <section id="vista-producto" class="vista oculto">
            <h2>Registro de Producto</h2>
            <form id="form-producto" class="formulario" enctype="multipart/form-data">
                <label for="p-nombreLocal">Local (nombre exacto)</label>
                <input type="text" id="p-nombreLocal" autocomplete="off" required>
                <span class="ayuda" id="p-local-info"></span>
                <input type="hidden" id="p-idLocal">

                <label for="p-tipoProducto">Tipo de Producto</label>
                <input type="text" id="p-tipoProducto" autocomplete="off" placeholder="Ej: Bebidas, Postres, Snacks..." required>
                <div class="sugerencias oculto" id="p-tipo-sugerencias"></div>

                <label for="p-nombre">Nombre del producto</label>
                <input type="text" id="p-nombre" required>

                <label for="p-descripcion">Descripción</label>
                <textarea id="p-descripcion"></textarea>

                <label for="p-precio">Precio</label>
                <input type="number" id="p-precio" min="0.01" step="0.01" required>

                <label for="p-descuento">Porcentaje de descuento (opcional)</label>
                <input type="number" id="p-descuento" min="1" max="99" step="0.01" placeholder="Ej: 15">

                <label for="p-cantidad">Cantidad disponible</label>
                <input type="number" id="p-cantidad" min="0" step="1" required>

                <label for="p-imagen">Imagen del producto</label>
                <input type="file" id="p-imagen" accept="image/png, image/jpeg, image/webp">

                <button type="submit">Registrar Producto</button>
            </form>
        </section>

        <!-- Vista: Registrar Local -->
        <section id="vista-local" class="vista oculto">
            <h2>Registro de Local</h2>
            <form id="form-local" class="formulario" enctype="multipart/form-data">
                <label for="l-numeroIdentificacion">Comerciante (ingresa tu número de identificación)</label>
                <input type="text" id="l-numeroIdentificacion" placeholder="Sin espacios ni guiones" required>
                <span class="ayuda" id="l-comerciante-info"></span>
                <input type="hidden" id="l-idComerciante">

                <label for="l-tipoLocal">Tipo de Local</label>
                <input type="text" id="l-tipoLocal" autocomplete="off" placeholder="Ej: Soda, Feria, Repostería..." required>
                <div class="sugerencias oculto" id="l-tipo-sugerencias"></div>

                <label for="l-nombreLocal">Nombre del Local</label>
                <input type="text" id="l-nombreLocal" required>
                <span class="ayuda" id="l-nombre-msg"></span>

                <label for="l-descripcion">Descripción</label>
                <textarea id="l-descripcion"></textarea>

                <label for="l-telefono">Teléfono</label>
                <input type="text" id="l-telefono" inputmode="numeric" placeholder="8888-8888" maxlength="9" required>

                <label for="l-correo">Correo</label>
                <input type="email" id="l-correo" placeholder="ejemplo@gmail.com" required>
                <span class="ayuda" id="l-correo-msg"></span>

                <label for="l-logo">Logo del local</label>
                <input type="file" id="l-logo" accept="image/png, image/jpeg, image/webp">

                <hr>

                <label for="l-provincia">Provincia</label>
                <select id="l-provincia" required>
                    <option value="">Seleccione...</option>
                </select>

                <label for="l-canton">Cantón</label>
                <select id="l-canton" required disabled>
                    <option value="">Primero elige provincia</option>
                </select>

                <label for="l-distrito">Distrito</label>
                <select id="l-distrito" required disabled>
                    <option value="">Primero elige cantón</option>
                </select>

                <label for="l-direccion">Dirección exacta</label>
                <input type="text" id="l-direccion" required>

                <label for="l-referencia">Punto de referencia</label>
                <input type="text" id="l-referencia">

                <button type="submit">Registrar Local</button>
            </form>
        </section>

        <!-- Vista: Registrar Cliente -->
        <section id="vista-cliente" class="vista oculto">
            <h2>Registro de Cliente</h2>
            <form id="form-cliente" class="formulario" enctype="multipart/form-data">
                <label for="cl-nombreCompleto">Nombre completo</label>
                <input type="text" id="cl-nombreCompleto" required>

                <label for="cl-tipoIdentificacion">Tipo de identificación</label>
                <select id="cl-tipoIdentificacion" required>
                    <option value="Cedula">Cédula física</option>
                    <option value="DIMEX">DIMEX (residente extranjero)</option>
                    <option value="Pasaporte">Pasaporte</option>
                </select>

                <label for="cl-numeroIdentificacion">Número de identificación</label>
                <input type="text" id="cl-numeroIdentificacion" placeholder="Sin espacios ni guiones" required>
                <span class="ayuda" id="cl-identificacion-msg"></span>

                <label for="cl-correo">Correo</label>
                <input type="email" id="cl-correo" placeholder="ejemplo@gmail.com" required>
                <span class="ayuda" id="cl-correo-msg"></span>

                <label for="cl-password">Contraseña</label>
                <input type="password" id="cl-password" required>

                <label for="cl-fotoPerfil">Foto de perfil</label>
                <input type="file" id="cl-fotoPerfil" accept="image/png, image/jpeg, image/webp">

                <hr>

                <label for="cl-provincia">Provincia</label>
                <select id="cl-provincia" required>
                    <option value="">Seleccione...</option>
                </select>

                <label for="cl-canton">Cantón</label>
                <select id="cl-canton" required disabled>
                    <option value="">Primero elige provincia</option>
                </select>

                <label for="cl-distrito">Distrito</label>
                <select id="cl-distrito" required disabled>
                    <option value="">Primero elige cantón</option>
                </select>

                <label for="cl-direccion">Dirección exacta</label>
                <input type="text" id="cl-direccion" required>

                <label for="cl-referencia">Punto de referencia</label>
                <input type="text" id="cl-referencia">

                <button type="submit">Registrar Cliente</button>
            </form>
        </section>

        <!-- Vista: Listado de locales -->
        <section id="vista-listado" class="vista oculto">

            <div id="panel-lista-locales">
                <h2>Locales Registrados</h2>
                <div id="lista-locales" class="tarjetas"></div>
            </div>

            <div id="panel-detalle-local" class="oculto">
                <button type="button" id="btn-volver-lista" class="boton-secundario">&larr; Volver al listado</button>
                <h2>Detalle del Local</h2>

                <div class="campo-lectura">
                    <img id="e-logo-actual" src="" alt="Logo del local" class="imagen-producto oculto">
                </div>

                <div class="campo-lectura">
                    <strong>Ubicación registrada</strong>
                    <p id="e-ubicacion-texto"></p>
                </div>

                <div class="campo-lectura">
                    <strong>Productos de este local</strong>
                    <div id="e-productos-lista" class="tarjetas"></div>
                </div>

                <form id="form-editar-local" class="formulario" enctype="multipart/form-data">
                    <input type="hidden" id="e-idLocal">

                    <label for="e-tipoLocal">Tipo de Local</label>
                    <input type="text" id="e-tipoLocal" autocomplete="off" required>
                    <div class="sugerencias oculto" id="e-tipo-sugerencias"></div>

                    <label for="e-nombreLocal">Nombre del Local</label>
                    <input type="text" id="e-nombreLocal" required>

                    <label for="e-descripcion">Descripción</label>
                    <textarea id="e-descripcion"></textarea>

                    <label for="e-telefono">Teléfono</label>
                    <input type="text" id="e-telefono" inputmode="numeric" placeholder="8888-8888" maxlength="9" required>

                    <label for="e-correo">Correo</label>
                    <input type="email" id="e-correo" placeholder="ejemplo@gmail.com" required>

                    <label for="e-logo">Nuevo logo (opcional, deja vacío para mantener el actual)</label>
                    <input type="file" id="e-logo" accept="image/png, image/jpeg, image/webp">

                    <button type="submit">Guardar Cambios</button>
                </form>
            </div>

            <div id="panel-editar-producto" class="oculto">
                <button type="button" id="btn-cerrar-editar-producto" class="boton-secundario">&larr; Volver al local</button>
                <h2>Editar Producto</h2>

                <form id="form-editar-producto" class="formulario" enctype="multipart/form-data">
                    <input type="hidden" id="ep-idProducto">

                    <label for="ep-tipoProducto">Tipo de Producto</label>
                    <input type="text" id="ep-tipoProducto" autocomplete="off" required>
                    <div class="sugerencias oculto" id="ep-tipo-sugerencias"></div>

                    <label for="ep-nombre">Nombre del producto</label>
                    <input type="text" id="ep-nombre" required>

                    <label for="ep-descripcion">Descripción</label>
                    <textarea id="ep-descripcion"></textarea>

                    <label for="ep-precio">Precio</label>
                    <input type="number" id="ep-precio" min="0.01" step="0.01" required>

                    <label for="ep-descuento">Porcentaje de descuento (opcional)</label>
                    <input type="number" id="ep-descuento" min="1" max="99" step="0.01">

                    <label for="ep-cantidad">Cantidad disponible</label>
                    <input type="number" id="ep-cantidad" min="0" step="1" required>

                    <label for="ep-imagen">Nueva imagen (opcional, deja vacío para mantener la actual)</label>
                    <input type="file" id="ep-imagen" accept="image/png, image/jpeg, image/webp">

                    <button type="submit">Guardar Cambios del Producto</button>
                </form>
            </div>

        </section>

    </main>

    <script src="js/app.js"></script>
</body>
</html>