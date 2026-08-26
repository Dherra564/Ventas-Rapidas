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
        <nav class="menu" id="menu-principal">
            <button class="menu-boton activo" data-vista="vista-login">Iniciar Sesión</button>
            <button class="menu-boton" data-vista="vista-local" data-rol="Comerciante">Registrar Local</button>
            <button class="menu-boton" data-vista="vista-producto" data-rol="Comerciante">Registrar Producto</button>
            <button class="menu-boton" data-vista="vista-listado" data-rol="Cliente">Ver Locales</button>
            <button class="menu-boton" data-vista="vista-comerciantes" data-rol="Admin">Ver Comerciantes</button>
            <button class="menu-boton" data-vista="vista-clientes" data-rol="Admin">Ver Clientes</button>
            <button class="menu-boton" data-vista="vista-compras" data-rol="Cliente">Compras</button>
            <button class="menu-boton" data-vista="vista-resenas" data-rol="Cliente">Reseñas</button>
            <button class="menu-boton" data-vista="vista-historiales" data-rol="Admin">Historiales</button>
        </nav>
        <div id="sesion-indicador" class="sesion-indicador oculto">
            <span id="sesion-texto"></span>
            <button type="button" id="btn-cerrar-sesion" class="boton-secundario">Cerrar sesión</button>
        </div>
    </header>

    <main class="contenedor">

        <div id="mensaje" class="mensaje oculto"></div>

        <!-- Vista: Iniciar Sesión / Crear cuenta -->
        <section id="vista-login" class="vista">

            <div id="login-panel-entrar">
                <h2>Iniciar Sesión</h2>

                <div class="formulario" style="max-width: 320px;">
                    <label for="login-tipo">Ingresar como</label>
                    <select id="login-tipo">
                        <option value="cliente">Cliente</option>
                        <option value="comerciante">Comerciante</option>
                    </select>
                </div>

                <form id="form-login" class="formulario" novalidate>
                    <label for="login-correo">Correo</label>
                    <input type="email" id="login-correo" required>

                    <label for="login-password">Contraseña</label>
                    <input type="password" id="login-password" required>

                    <button type="submit">Ingresar</button>
                </form>

                <p class="ayuda">
                    ¿No tienes cuenta?
                    <a href="#" id="link-crear-cuenta">Crear cuenta</a>
                </p>
            </div>

            <div id="login-panel-elegir-tipo" class="oculto">
                <h2>Crear cuenta</h2>
                <p class="ayuda">¿Cómo quieres registrarte?</p>

                <div class="rejilla-dos" style="max-width: 500px;">
                    <button type="button" class="boton-eleccion" id="btn-elegir-cliente">🧍<br>Soy Cliente</button>
                    <button type="button" class="boton-eleccion" id="btn-elegir-comerciante">🏪<br>Soy Comerciante</button>
                </div>

                <p class="ayuda">
                    <a href="#" id="link-volver-login">&larr; Volver a iniciar sesión</a>
                </p>
            </div>

        </section>

        <!-- Vista: Registrar Comerciante -->
        <section id="vista-comerciante" class="vista oculto">
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
            <button type="button" id="btn-volver-login-comerciante" class="boton-secundario">&larr; Volver al login</button>
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

                <button type="button" id="btn-gps-local" class="boton-secundario">📍 Usar mi ubicación GPS</button>
                <span class="ayuda" id="l-gps-msg"></span>
                <input type="hidden" id="l-latitud">
                <input type="hidden" id="l-longitud">

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
            <button type="button" id="btn-volver-login-cliente" class="boton-secundario">&larr; Volver al login</button>
        </section>

        <!-- Vista: Selector de perfiles de local (estilo Netflix) -->
        <section id="vista-seleccionar-local" class="vista oculto">
            <h2>¿Qué local vas a administrar?</h2>
            <p class="ayuda">Si no entras al perfil de un local por 7 días, se marca como inactivo automáticamente.</p>
            <div id="grid-perfiles-local" class="rejilla-perfiles"></div>
        </section>

        <!-- Vista: Listado de locales -->
        <section id="vista-listado" class="vista oculto">

            <div id="panel-lista-locales">
                <h2>Locales Registrados</h2>

                <div class="filtros-busqueda">
                    <input type="text" id="f-nombre" placeholder="Buscar por nombre...">

                    <select id="f-provincia">
                        <option value="">Todas las provincias</option>
                    </select>

                    <select id="f-canton" disabled>
                        <option value="">Todos los cantones</option>
                    </select>

                    <select id="f-distrito" disabled>
                        <option value="">Todos los distritos</option>
                    </select>

                    <button type="button" id="btn-limpiar-filtros" class="boton-secundario">Limpiar filtros</button>
                </div>

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

                <div class="campo-lectura">
                    <strong>Este producto también se ofrece en:</strong>
                    <div id="ep-otros-locales-lista"></div>

                    <div class="filtros-busqueda">
                        <input type="text" id="ep-agregar-local-nombre" placeholder="Nombre exacto de otro local...">
                        <button type="button" id="btn-agregar-local-producto" class="boton-secundario">Agregar local</button>
                    </div>
                    <span class="ayuda" id="ep-agregar-local-msg"></span>
                </div>

            </div>

        </section>

        <!-- Vista: Listado de Comerciantes -->
        <section id="vista-comerciantes" class="vista oculto">

            <div id="panel-lista-comerciantes">
                <h2>Comerciantes Registrados</h2>
                <label class="ayuda"><input type="checkbox" id="chk-inactivos-comerciantes"> Mostrar también inactivos</label>
                <div id="lista-comerciantes" class="tarjetas"></div>
            </div>

            <div id="panel-detalle-comerciante" class="oculto">
                <button type="button" id="btn-volver-comerciantes" class="boton-secundario">&larr; Volver al listado</button>
                <h2>Detalle del Comerciante</h2>

                <div class="campo-lectura">
                    <img id="dc-foto-actual" src="" alt="Foto de perfil" class="imagen-producto oculto">
                    <p><strong>Número de identificación:</strong> <span id="dc-identificacion"></span></p>
                </div>

                <form id="form-editar-comerciante" class="formulario" enctype="multipart/form-data">
                    <input type="hidden" id="dc-idComerciante">

                    <label for="dc-nombre">Nombre completo</label>
                    <input type="text" id="dc-nombre" required>

                    <label for="dc-alias">Alias</label>
                    <input type="text" id="dc-alias" required>

                    <label for="dc-correo">Correo</label>
                    <input type="email" id="dc-correo" required>

                    <label for="dc-password">Nueva contraseña (opcional, deja vacío para no cambiarla)</label>
                    <input type="password" id="dc-password">

                    <label for="dc-fotoPerfil">Nueva foto (opcional, deja vacío para mantener la actual)</label>
                    <input type="file" id="dc-fotoPerfil" accept="image/png, image/jpeg, image/webp">

                    <button type="submit">Guardar Cambios</button>
                </form>

                <button type="button" id="btn-desactivar-comerciante" class="boton-peligro">Desactivar Comerciante</button>
                <button type="button" id="btn-activar-comerciante" class="boton-secundario oculto">Reactivar Comerciante</button>
            </div>

        </section>

        <!-- Vista: listado de Clientes -->
        <section id="vista-clientes" class="vista oculto">

            <div id="panel-lista-clientes">
                <h2>Clientes Registrados</h2>
                <label class="ayuda"><input type="checkbox" id="chk-inactivos-clientes"> Mostrar también inactivos</label>
                <div id="lista-clientes" class="tarjetas"></div>
            </div>

          <div id="panel-detalle-cliente" class="oculto">
                <button type="button" id="btn-volver-clientes" class="boton-secundario">&larr; Volver al listado</button>
                <h2>Detalle del Cliente</h2>

                <div class="campo-lectura">
                    <img id="dcl-foto-actual" src="" alt="Foto de perfil" class="imagen-producto oculto">
                    <p><strong>Número de identificación:</strong> <span id="dcl-identificacion"></span></p>
                    <p><strong>Dirección:</strong> <span id="dcl-direccion"></span></p>
                </div>

                <form id="form-editar-cliente" class="formulario" enctype="multipart/form-data">
                    <input type="hidden" id="dcl-idCliente">

                    <label for="dcl-nombreCompleto">Nombre completo</label>
                    <input type="text" id="dcl-nombreCompleto" required>

                    <label for="dcl-correo">Correo</label>
                    <input type="email" id="dcl-correo" required>

                    <label for="dcl-password">Nueva contraseña (opcional, deja vacío para no cambiarla)</label>
                    <input type="password" id="dcl-password">

                    <label for="dcl-fotoPerfil">Nueva foto (opcional, deja vacío para mantener la actual)</label>
                    <input type="file" id="dcl-fotoPerfil" accept="image/png, image/jpeg, image/webp">

                    <button type="submit">Guardar Cambios</button>
                </form>

                <button type="button" id="btn-desactivar-cliente" class="boton-peligro">Desactivar Cliente</button>
                <button type="button" id="btn-activar-cliente" class="boton-secundario oculto">Reactivar Cliente</button>

                <div class="campo-lectura">
                    <strong>Locales que sigue este cliente:</strong>
                <div id="dcl-locales-lista"></div>

                <div class="filtros-busqueda">
                    <input type="text" id="dcl-agregar-local-nombre" placeholder="Nombre exacto de un local...">
                    <button type="button" id="btn-seguir-local" class="boton-secundario">Seguir local</button>
                </div>
                    <span class="ayuda" id="dcl-seguir-local-msg"></span>
                </div>
            </div>

        </section>

        <!-- Vista: Registro e historial de compras -->
        <section id="vista-compras" class="vista oculto">
            <h2>Registro e Historial de Compras</h2>

            <div class="rejilla-dos">
                <div>
                    <h3>Registrar compra</h3>
                    <form id="form-compra" class="formulario">
                        <label for="compra-cliente">Cliente</label>
                        <select id="compra-cliente" required>
                            <option value="">Seleccione...</option>
                        </select>

                        <label for="compra-local">Local</label>
                        <select id="compra-local" required>
                            <option value="">Seleccione...</option>
                        </select>

                        <button type="submit">Registrar Compra</button>
                    </form>
                </div>

                <div>
                    <h3>Historial por cliente</h3>
                    <div class="formulario">
                        <label for="compras-historial-cliente">Cliente</label>
                        <select id="compras-historial-cliente">
                            <option value="">Seleccione...</option>
                        </select>

                        <label for="compras-fecha">Fecha (opcional)</label>
                        <input type="date" id="compras-fecha">

                        <button type="button" id="btn-buscar-compras">Consultar Compras</button>
                    </div>
                </div>
            </div>

            <div class="campo-lectura bloque-separado">
                <strong>Compras encontradas</strong>
                <div id="lista-compras" class="tarjetas"></div>
            </div>

            <div class="campo-lectura bloque-separado">
                <strong>Locales con más compras</strong>
                <div id="ranking-compras" class="tarjetas"></div>
            </div>
        </section>

        <!-- Vista: Reseñas -->
        <section id="vista-resenas" class="vista oculto">
            <h2>Reseñas de Locales</h2>

            <div class="rejilla-dos">
                <div>
                    <h3>Publicar reseña</h3>
                    <form id="form-resena" class="formulario">
                        <label for="resena-cliente">Cliente</label>
                        <select id="resena-cliente" required>
                            <option value="">Seleccione...</option>
                        </select>

                        <label for="resena-local">Local</label>
                        <select id="resena-local" required>
                            <option value="">Seleccione...</option>
                        </select>

                        <label for="resena-puntuacion">Puntuación</label>
                        <select id="resena-puntuacion" required>
                            <option value="5">5 - Excelente</option>
                            <option value="4">4 - Muy bueno</option>
                            <option value="3">3 - Bueno</option>
                            <option value="2">2 - Regular</option>
                            <option value="1">1 - Malo</option>
                        </select>

                        <label for="resena-comentario">Comentario</label>
                        <textarea id="resena-comentario" rows="4" required></textarea>

                        <button type="submit">Publicar Reseña</button>
                    </form>
                </div>

                <div>
                    <h3>Consultar reseñas</h3>
                    <div class="formulario">
                        <label for="resena-filtro-local">Local</label>
                        <select id="resena-filtro-local">
                            <option value="">Seleccione...</option>
                        </select>
                        <button type="button" id="btn-cargar-resenas">Ver Reseñas</button>
                    </div>

                    <div id="resena-resumen" class="resumen-resenas">
                        Selecciona un local para ver su calificación.
                    </div>
                </div>
            </div>

            <div id="lista-resenas" class="tarjetas bloque-separado"></div>
        </section>

        <!-- Vista: Historiales de seguridad -->
        <section id="vista-historiales" class="vista oculto">
            <h2>Historiales de Seguridad</h2>
            <p class="ayuda">Los historiales se generan automáticamente cuando un usuario cambia su foto o su contraseña.</p>

            <div class="rejilla-dos bloque-separado">
                <div>
                    <h3>Consultar historial</h3>
                    <div class="formulario">
                        <label for="historial-tipo">Tipo de usuario</label>
                        <select id="historial-tipo">
                            <option value="Cliente">Cliente</option>
                            <option value="Comerciante">Comerciante</option>
                        </select>

                        <label for="historial-usuario">Usuario</label>
                        <select id="historial-usuario">
                            <option value="">Seleccione...</option>
                        </select>

                        <button type="button" id="btn-ver-historial">Ver Historial</button>
                    </div>
                </div>

                <div>
                    <h3>Cambiar contraseña</h3>
                    <form id="form-cambiar-password" class="formulario">
                        <p class="ayuda">Usa el mismo usuario seleccionado a la izquierda.</p>

                        <label for="historial-password-actual">Contraseña actual</label>
                        <input type="password" id="historial-password-actual" required>

                        <label for="historial-password-nueva">Nueva contraseña</label>
                        <input type="password" id="historial-password-nueva" required>

                        <button type="submit">Cambiar Contraseña</button>
                    </form>
                </div>
            </div>

            <div class="rejilla-dos bloque-separado">
                <div class="campo-lectura">
                    <strong>Historial de contraseñas</strong>
                    <div id="historial-password-lista" class="tarjetas"></div>
                </div>

                <div class="campo-lectura">
                    <strong>Historial de fotos de perfil</strong>
                    <div id="historial-fotos-lista" class="tarjetas"></div>
                </div>
            </div>
        </section>

    </main>

    <script src="js/app.js"></script>
</body>
</html>