<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas Rápidas</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body>

    <header id="topbar-publica" class="topbar-publica">
        <div class="topbar-publica-fila">
            <div class="marca-sidebar">
                <img src="imagenes/logo-rapiventas.png" alt="RapiVentas">
                <h1>Rapi<span>Ventas</span></h1>
            </div>
            <button class="btn-login-header" id="btn-ir-login">Iniciar Sesión</button>
        </div>
    </header>

    <div class="app-shell">
        <aside class="barra-lateral oculto" id="barra-lateral">
            <div class="marca-sidebar">
                <img src="imagenes/logo-rapiventas.png" alt="RapiVentas">
                <h1>Rapi<span>Ventas</span></h1>
            </div>

            <nav class="menu" id="menu-principal">
                <button class="menu-boton activo" data-vista="vista-inicio">Inicio</button>
                <button class="menu-boton" data-vista="vista-login">Iniciar Sesión</button>
                <button class="menu-boton" data-vista="vista-local" data-rol="Comerciante">Registrar Local</button>
                <button class="menu-boton" data-vista="vista-producto" data-rol="Comerciante">Registrar
                    Producto</button>
                <button class="menu-boton" data-vista="vista-seleccionar-local" data-rol="Comerciante">Mi Local</button>
                <button class="menu-boton" data-vista="vista-listado" data-rol="Cliente">Ver Locales</button>
                <button class="menu-boton" data-vista="vista-cercanos" data-rol="Cliente">Locales Cercanos</button>
                <button class="menu-boton" data-vista="vista-resenas" data-rol="Cliente">Reseñas</button>
                <button class="menu-boton" data-vista="vista-dashboard-admin" data-rol="SuperAdmin">Dashboard</button>
                <button class="menu-boton" data-vista="vista-comerciantes" data-rol="SuperAdmin">Ver
                    Comerciantes</button>
                <button class="menu-boton" data-vista="vista-clientes" data-rol="SuperAdmin">Ver Clientes</button>
                <button class="menu-boton" data-vista="vista-historiales" data-rol="SuperAdmin">Historiales</button>
            </nav>

            <div id="sesion-indicador" class="sesion-indicador oculto">
                <span id="sesion-texto"></span>
                <button type="button" id="btn-cerrar-sesion" class="boton-secundario">Cerrar sesión</button>
            </div>
        </aside>

        <div class="area-principal">
            <main class="contenedor">

                <div id="mensaje" class="mensaje oculto" role="alert">
                    <span id="mensaje-texto"></span>
                    <button type="button" id="mensaje-cerrar" class="mensaje-cerrar"
                        aria-label="Cerrar mensaje">&times;</button>
                </div>

                <!-- Vista: Inicio (catálogo público) -->
                <section id="vista-inicio" class="vista">
                    <div class="hero-inicio">
                        <h2>Encuentra los mejores locales cerca de ti</h2>
                        <p class="ayuda">Sodas, ferias, reposterías y más — todo en un solo lugar.</p>
                    </div>

                    <div class="carrusel" id="carrusel-locales">
                        <button type="button" class="carrusel-flecha carrusel-flecha-izq" id="carrusel-prev"
                            aria-label="Anterior">&#10094;</button>
                        <div class="carrusel-pista" id="carrusel-pista"></div>
                        <button type="button" class="carrusel-flecha carrusel-flecha-der" id="carrusel-next"
                            aria-label="Siguiente">&#10095;</button>
                    </div>
                    <div class="carrusel-puntos" id="carrusel-puntos"></div>

                    <h3 class="bloque-separado">Todos los locales</h3>
                    <div class="filtros-busqueda">
                        <input type="text" id="inicio-buscar" placeholder="Buscar por nombre o tipo...">
                    </div>
                    <div id="catalogo-inicio" class="tarjetas"></div>
                </section>


                <!-- Vista: Iniciar Sesión / Crear cuenta -->
                <section id="vista-login" class="vista oculto">
                    <div class="login-wrapper">
                        <!-- Lado izquierdo -->
                        <div class="login-ilustracion">
                            <div class="logo-grande">
                                <img src="imagenes/logo-rapiventas.png" alt="RapiVentas"
                                    style="width:100%;height:100%;object-fit:contain;">
                            </div>
                            <h2>Rapi<span>Ventas</span></h2>
                            <p>La plataforma que conecta comerciantes y clientes</p>
                            <div class="testimonial">
                                <blockquote>"La mejor forma de encontrar locales cerca de ti"</blockquote>
                                <cite>— Usuarios satisfechos</cite>
                            </div>
                        </div>

                        <!-- Lado derecho -->
                        <div class="login-formulario">
                            <span class="badge">Bienvenido</span>
                            <h3>Iniciar Sesión</h3>
                            <p class="subtitulo">Ingresa a tu cuenta para continuar</p>

                            <div class="login-tabs">
                                <button class="login-tab activo" data-rol="cliente">Cliente</button>
                                <button class="login-tab" data-rol="comerciante">Comerciante</button>
                                <button type="button" class="login-tab" data-rol="superadmin">Admin</button>
                            </div>

                            <form id="form-login">
                                <div class="grupo-form">
                                    <label> Correo electrónico</label>
                                    <input type="email" id="login-correo" placeholder="tucorreo@ejemplo.com" required>
                                </div>
                                <div class="grupo-form">
                                    <label>Contraseña</label>
                                    <div class="password-wrap">
                                        <input type="password" id="login-password" placeholder="••••••••" required>
                                        <button type="button" class="toggle-pwd" id="toggle-password">👁️</button>
                                    </div>
                                </div>

                                <div class="login-opciones">
                                    <label><input type="checkbox"> Recordarme</label>
                                    <a href="#">¿Olvidaste tu contraseña?</a>
                                </div>

                                <button type="submit" class="btn-ingresar">Ingresar →</button>

                                <div class="login-footer">
                                    ¿No tienes cuenta? <a id="btn-registro">Crear cuenta</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Vista: Registrar Comerciante -->
                <section id="vista-comerciante" class="vista oculto">
                    <h2>Registro de Comerciante</h2>
                    <form id="form-comerciante" class="formulario" enctype="multipart/form-data">
                        <label for="c-nombre">Nombre completo</label>
                        <input type="text" id="c-nombre" required>
                        <span class="ayuda" id="c-nombre-msg"></span>

                        <label for="c-alias">Alias</label>
                        <input type="text" id="c-alias" required>
                        <span class="ayuda" id="c-alias-msg"></span>

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
                        <span class="ayuda" id="c-password-msg">Mínimo 8 caracteres, con al menos una letra mayúscula.
                            Símbolos permitidos: ! @ # $ % ^ &amp; * ( ) _ - + = [ ] { } ; : , . &lt; &gt; ?</span>

                        <label for="c-fotoPerfil">Foto de perfil</label>
                        <input type="file" id="c-fotoPerfil" accept="image/png, image/jpeg, image/webp">

                        <button type="submit">Registrar Comerciante</button>
                    </form>
                    <button type="button" id="btn-volver-login-comerciante" class="boton-secundario">&larr; Volver al
                        login</button>
                </section>

                <!-- Vista: Registrar Producto -->
                <section id="vista-producto" class="vista oculto">
                    <h2>Registro de Producto</h2>
                    <form id="form-producto" class="formulario" enctype="multipart/form-data">
                        <label for="p-idLocal">Local</label>
                        <select id="p-idLocal" required>
                            <option value="">Selecciona un local...</option>
                        </select>
                        
                        <label for="p-tipoProducto">Tipo de Producto</label>
                        <input type="text" id="p-tipoProducto" autocomplete="off"
                            placeholder="Ej: Bebidas, Postres, Snacks..." required>
                        <div class="sugerencias oculto" id="p-tipo-sugerencias"></div>

                        <label for="p-nombre">Nombre del producto</label>
                        <input type="text" id="p-nombre" required>
                        <div id="p-similares" class="sugerencias-similares oculto"></div>

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
                        <input type="text" id="l-tipoLocal" autocomplete="off"
                            placeholder="Ej: Soda, Feria, Repostería..." required>
                        <div class="sugerencias oculto" id="l-tipo-sugerencias"></div>

                        <label for="l-nombreLocal">Nombre del Local</label>
                        <input type="text" id="l-nombreLocal" required>
                        <span class="ayuda" id="l-nombre-msg"></span>
                        <div id="l-similares" class="sugerencias-similares oculto"></div>

                        <label for="l-descripcion">Descripción</label>
                        <textarea id="l-descripcion"></textarea>

                        <label for="l-telefono">Teléfono</label>
                        <input type="text" id="l-telefono" inputmode="numeric" placeholder="8888-8888" maxlength="9"
                            required>

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

                        <button type="button" id="btn-gps-local" class="boton-secundario">📍 Usar mi ubicación
                            GPS</button>
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
                        <span class="ayuda" id="cl-nombreCompleto-msg"></span>

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
                        <span class="ayuda" id="cl-password-msg">Mínimo 8 caracteres, con al menos una letra mayúscula.
                            Símbolos permitidos: ! @ # $ % ^ &amp; * ( ) _ - + = [ ] { } ; : , . &lt; &gt; ?</span>

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
                    <button type="button" id="btn-volver-login-cliente" class="boton-secundario">&larr; Volver al
                        login</button>
                </section>



                <!-- Vista: Selector de perfiles de local (estilo Netflix) -->
                <section id="vista-seleccionar-local" class="vista oculto">
                    <h2>¿Qué local vas a administrar?</h2>
                    <p class="ayuda">Si no entras al perfil de un local por 7 días, se marca como inactivo
                        automáticamente.</p>
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

                            <button type="button" id="btn-limpiar-filtros" class="boton-secundario">Limpiar
                                filtros</button>
                        </div>

                        <div id="lista-locales" class="tarjetas"></div>
                    </div>

                    <div id="panel-detalle-local" class="oculto">
                        <button type="button" id="btn-volver-lista" class="boton-secundario">&larr; Volver al
                            listado</button>
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

                        <div class="campo-lectura oculto" id="e-panel-actividad-local">
                            <strong>Actividad de sesión de este local</strong>
                            <p class="ayuda" id="e-actividad-estado"></p>
                            <div id="e-actividad-lista" class="tarjetas"></div>
                        </div>
                        <div class="campo-lectura" id="e-info-solo-lectura">
                            <strong>Información del local</strong>
                            <p><strong>Tipo:</strong> <span id="e-solo-tipo"></span></p>
                            <p><strong>Nombre:</strong> <span id="e-solo-nombre"></span></p>
                            <p><strong>Descripción:</strong> <span id="e-solo-descripcion"></span></p>
                            <p><strong>Teléfono:</strong> <span id="e-solo-telefono"></span></p>
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
                            <input type="text" id="e-telefono" inputmode="numeric" placeholder="8888-8888" maxlength="9"
                                required>

                            <label for="e-logo">Nuevo logo (opcional, deja vacío para mantener el actual)</label>
                            <input type="file" id="e-logo" accept="image/png, image/jpeg, image/webp">

                            <button type="submit">Guardar Cambios</button>
                        </form>

                    </div>

                    <div id="panel-editar-producto" class="oculto">
                        <button type="button" id="btn-cerrar-editar-producto" class="boton-secundario">&larr; Volver al
                            local</button>
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
                                <input type="text" id="ep-agregar-local-nombre"
                                    placeholder="Nombre exacto de otro local...">
                                <button type="button" id="btn-agregar-local-producto" class="boton-secundario">Agregar
                                    local</button>
                            </div>
                            <span class="ayuda" id="ep-agregar-local-msg"></span>
                        </div>

                    </div>

                </section>

                <!-- Vista: Listado de Comerciantes -->
                <section id="vista-comerciantes" class="vista oculto">

                    <div id="panel-lista-comerciantes">
                        <h2>Comerciantes Registrados</h2>
                        <div class="filtros-busqueda">
                            <input type="text" id="admin-buscar-identificacion" inputmode="numeric"
                                placeholder="Buscar por número de identificación..." maxlength="15">
                            <button type="button" id="btn-buscar-comerciante-identificacion"
                                class="boton-secundario">Buscar</button>
                        </div>
                        <span class="ayuda" id="admin-buscar-identificacion-msg"></span>
                        <label class="ayuda"><input type="checkbox" id="chk-inactivos-comerciantes"> Mostrar también
                            inactivos</label>
                        <div id="lista-comerciantes" class="tarjetas"></div>
                    </div>

                    <div id="panel-detalle-comerciante" class="oculto">
                        <button type="button" id="btn-volver-comerciantes" class="boton-secundario">&larr; Volver al
                            listado</button>
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

                        <button type="button" id="btn-desactivar-comerciante" class="boton-peligro">Desactivar
                            Comerciante</button>
                        <button type="button" id="btn-activar-comerciante" class="boton-secundario oculto">Reactivar
                            Comerciante</button>
                    </div>

                </section>

                <!-- Vista: listado de Clientes -->
                <section id="vista-clientes" class="vista oculto">

                    <div id="panel-lista-clientes">
                        <h2>Clientes Registrados</h2>
                        <label class="ayuda"><input type="checkbox" id="chk-inactivos-clientes"> Mostrar también
                            inactivos</label>
                        <div id="lista-clientes" class="tarjetas"></div>
                    </div>

                    <div id="panel-detalle-cliente" class="oculto">
                        <button type="button" id="btn-volver-clientes" class="boton-secundario">&larr; Volver al
                            listado</button>
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

                            <label for="dcl-fotoPerfil">Nueva foto (opcional, deja vacío para mantener la
                                actual)</label>
                            <input type="file" id="dcl-fotoPerfil" accept="image/png, image/jpeg, image/webp">

                            <button type="submit">Guardar Cambios</button>
                        </form>

                        <button type="button" id="btn-desactivar-cliente" class="boton-peligro">Desactivar
                            Cliente</button>
                        <button type="button" id="btn-activar-cliente" class="boton-secundario oculto">Reactivar
                            Cliente</button>

                        <div class="campo-lectura">
                            <strong>Locales que sigue este cliente:</strong>
                            <div id="dcl-locales-lista"></div>

                            <div class="filtros-busqueda">
                                <input type="text" id="dcl-agregar-local-nombre"
                                    placeholder="Nombre exacto de un local...">
                                <button type="button" id="btn-seguir-local" class="boton-secundario">Seguir
                                    local</button>
                            </div>
                            <span class="ayuda" id="dcl-seguir-local-msg"></span>
                        </div>
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

                    <div class="bloque-separado">
                        <h3 id="resena-cliente-titulo">Reseñas escritas por un cliente</h3>
                        <div class="filtros-busqueda" id="resena-cliente-controles">
                            <select id="resena-filtro-cliente">
                                <option value="">Seleccione un cliente...</option>
                            </select>
                            <button type="button" id="btn-ver-resenas-cliente" class="boton-secundario">Ver sus
                                Reseñas</button>
                        </div>
                        <div id="lista-resenas-cliente" class="tarjetas"></div>
                    </div>
                </section>

                <!-- Vista: Historiales de seguridad -->
                <section id="vista-historiales" class="vista oculto">
                    <h2>Historiales de Seguridad</h2>
                    <p class="ayuda">Los historiales se generan automáticamente cuando un usuario cambia su foto o su
                        contraseña.</p>

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
                <!-- Vista: Dashboard de Administrador -->
                <section id="vista-dashboard-admin" class="vista oculto">
                    <h2>Panel de Administración</h2>
                    <p class="ayuda">Resumen general de la plataforma.</p>

                    <div class="rejilla-stats bloque-separado">
                        <div class="stat-card">
                            <div class="icon-container"><i data-lucide="store"></i></div>
                            <p class="stat-numero" id="stat-locales">—</p>
                            <p class="stat-etiqueta">Locales activos</p>
                        </div>
                        <div class="stat-card">
                            <div class="icon-container"><i data-lucide="users"></i></div>
                            <p class="stat-numero" id="stat-clientes">—</p>
                            <p class="stat-etiqueta">Clientes activos</p>
                        </div>
                        <div class="stat-card">
                            <div class="icon-container"><i data-lucide="briefcase"></i></div>
                            <p class="stat-numero" id="stat-comerciantes">—</p>
                            <p class="stat-etiqueta">Comerciantes activos</p>
                        </div>
                        <div class="stat-card">
                            <div class="icon-container"><i data-lucide="user-x"></i></div>
                            <p class="stat-numero" id="stat-comerciantes-inactivos">—</p>
                            <p class="stat-etiqueta">Comerciantes inactivos</p>
                        </div>
                    </div>

                    <h3 class="bloque-separado">Accesos rápidos</h3>
                    <div class="accesos-dashboard">
                        <button type="button" class="acceso-dashboard-boton" data-vista="vista-comerciantes">
                            <i data-lucide="briefcase" class="icon-sm"></i> Ver Comerciantes
                        </button>
                        <button type="button" class="acceso-dashboard-boton" data-vista="vista-clientes">
                            <i data-lucide="users" class="icon-sm"></i> Ver Clientes
                        </button>
                        <button type="button" class="acceso-dashboard-boton" data-vista="vista-historiales">
                            <i data-lucide="history" class="icon-sm"></i> Historiales de Seguridad
                        </button>
                    </div>
                </section>
                <!-- Vista: Locales Cercanos -->
                <section id="vista-cercanos" class="vista oculto">
                    <h2>Locales Cercanos</h2>
                    <p class="ayuda">Busca un producto o tipo de local y te mostramos los más cercanos a tu ubicación
                        actual.</p>

                    <form id="form-cercanos" class="formulario">
                        <label for="cerc-termino">¿Qué estás buscando?</label>
                        <input type="text" id="cerc-termino" placeholder="Ej: Café, Empanadas, Bebidas..." required>

                        <label for="cerc-radio">Radio de búsqueda</label>
                        <select id="cerc-radio">
                            <option value="1">1 km</option>
                            <option value="2">2 km</option>
                            <option value="5" selected>5 km</option>
                            <option value="10">10 km</option>
                            <option value="20">20 km</option>
                        </select>

                        <button type="button" id="btn-cerc-ubicacion" class="boton-secundario"
                            style="margin-top: 0.9rem;">📍 Usar mi ubicación GPS</button>
                        <span class="ayuda" id="cerc-ubicacion-msg"></span>
                        <input type="hidden" id="cerc-latitud">
                        <input type="hidden" id="cerc-longitud">

                        <button type="submit">Buscar Locales Cercanos</button>
                    </form>

                    <div id="lista-cercanos" class="tarjetas bloque-separado"></div>
                </section>

            </main>

            <footer class="pie-pagina">
                <div class="pie-contenido">
                    <div class="pie-marca">
                        <strong>RapiVentas</strong>
                        <p>Conectamos clientes con locales cercanos, de forma rápida, simple y eficiente.</p>
                    </div>
                    <div class="pie-enlaces">
                        <strong>Enlaces rápidos</strong>
                        <a href="#" data-vista-footer="vista-listado">Ver Locales</a>
                        <a href="#" data-vista-footer="vista-cercanos">Locales Cercanos</a>
                        <a href="#" data-vista-footer="vista-login">Iniciar Sesión</a>
                    </div>
                    <div class="pie-info">
                        <strong>RapiVentas</strong>
                        <p>Proyecto académico de comercio local.</p>
                        <p>&copy; <span id="pie-anio"></span> Todos los derechos reservados.</p>
                    </div>
                </div>
            </footer>
        </div> <!-- cierra .area-principal -->
    </div> <!-- cierra .app-shell -->

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/app.js"></script>
    <script>lucide.createIcons();</script>
</body>

</html>