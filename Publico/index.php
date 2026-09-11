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
                <h1 class="marca-sidebar-texto">Rapi<span>Ventas</span></h1>
                <button type="button" id="btn-colapsar-sidebar" class="btn-colapsar-sidebar" aria-label="Colapsar menú" title="Colapsar menú">
                    <i data-lucide="chevrons-left"></i>
                </button>
            </div>

            <nav class="menu" id="menu-principal">
                <button class="menu-boton activo" data-vista="vista-inicio"><i data-lucide="home"></i><span class="menu-boton-texto">Inicio</span></button>
                <button class="menu-boton" data-vista="vista-login"><i data-lucide="log-in"></i><span class="menu-boton-texto">Iniciar Sesión</span></button>
                <button class="menu-boton" data-vista="vista-dashboard-admin" data-rol="SuperAdmin"><i data-lucide="layout-dashboard"></i><span class="menu-boton-texto">Dashboard</span></button>
                <button class="menu-boton" data-vista="vista-local" data-rol="Comerciante"><i data-lucide="plus-circle"></i><span class="menu-boton-texto">Registrar Local</span></button>
                <button class="menu-boton" data-vista="vista-producto" data-rol="Comerciante"><i data-lucide="package-plus"></i><span class="menu-boton-texto">Registrar Producto</span></button>
                <button class="menu-boton" data-vista="vista-seleccionar-local" data-rol="Comerciante"><i data-lucide="store"></i><span class="menu-boton-texto">Mi Local</span></button>
                <button class="menu-boton" data-vista="vista-listado" data-rol="Cliente,SuperAdmin"><i data-lucide="store"></i><span class="menu-boton-texto">Ver Locales</span></button>
                <button class="menu-boton" data-vista="vista-cercanos" data-rol="Cliente"><i data-lucide="map-pin"></i><span class="menu-boton-texto">Locales Cercanos</span></button>
                <button class="menu-boton" data-vista="vista-resenas" data-rol="Cliente"><i data-lucide="star"></i><span class="menu-boton-texto">Reseñas</span></button>
                <button class="menu-boton" data-vista="vista-comerciantes" data-rol="SuperAdmin"><i data-lucide="briefcase"></i><span class="menu-boton-texto">Ver Comerciantes</span></button>
                <button class="menu-boton" data-vista="vista-clientes" data-rol="SuperAdmin"><i data-lucide="users"></i><span class="menu-boton-texto">Ver Clientes</span></button>
                <button class="menu-boton" data-vista="vista-productos-admin" data-rol="SuperAdmin"><i data-lucide="package"></i><span class="menu-boton-texto">Ver Productos</span></button>
                <button class="menu-boton" data-vista="vista-historiales" data-rol="SuperAdmin"><i data-lucide="history"></i><span class="menu-boton-texto">Historiales</span></button>
            </nav>

            <div id="sesion-indicador" class="sesion-indicador oculto">
                <button type="button" id="btn-mi-perfil" class="btn-mi-perfil oculto" aria-label="Mi perfil" title="Mi perfil">
                    <i data-lucide="user-circle"></i>
                </button>
                <span id="sesion-texto" class="sesion-texto-completo"></span>
                <button type="button" id="btn-cerrar-sesion" class="boton-secundario">
                    <i data-lucide="log-out" class="icon-sm"></i>
                    <span class="menu-boton-texto">Cerrar sesión</span>
                </button>
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
                        <div class="hero-inicio-texto">
                            <h2>Encuentra los mejores locales cerca de ti</h2>
                            <p class="ayuda">Sodas, ferias, reposterías y más — todo en un solo lugar.</p>

                            <div class="hero-buscador-ubicacion">
                                <div class="hero-buscador-caja">
                                    <input type="text" id="hero-ubicacion-texto" list="hero-ubicacion-sugerencias"
                                        placeholder="Buscar por cantón o provincia...">
                                    <datalist id="hero-ubicacion-sugerencias"></datalist>
                                    <button type="button" id="hero-ubicacion-buscar" aria-label="Buscar">
    <i data-lucide="search" class="icon-sm"></i>
</button>
                                </div>
                                <button type="button" id="hero-ubicacion-actual" class="hero-ubicacion-actual">
                                    <i data-lucide="map-pin" class="icon-sm"></i> Usar mi ubicación actual
                                </button>
                                <div id="hero-ubicacion-activa" class="hero-ubicacion-activa oculto">
                                    <span id="hero-ubicacion-activa-texto"></span>
                                    <button type="button" id="hero-ubicacion-limpiar" aria-label="Quitar filtro de ubicación">&times;</button>
                                </div>
                            </div>
                        </div>

                        <div class="hero-inicio-imagen">
                            <img src="imagenes/hero-ilustracion.svg" alt="Ilustración de una bolsa de compras con productos de RapiVentas">
                        </div>
                    </div>

                    <div class="carrusel" id="carrusel-locales">
                        <button type="button" class="carrusel-flecha carrusel-flecha-izq" id="carrusel-prev"
                            aria-label="Anterior">&#10094;</button>
                        <div class="carrusel-pista" id="carrusel-pista"></div>
                        <button type="button" class="carrusel-flecha carrusel-flecha-der" id="carrusel-next"
                            aria-label="Siguiente">&#10095;</button>
                    </div>
                    <div class="carrusel-puntos" id="carrusel-puntos"></div>

                    <h3 class="bloque-separado">Productos disponibles ahora</h3>
                    <div class="filtros-busqueda">
                        <input type="text" id="inicio-buscar" placeholder="Buscar producto, local o categoría...">
                    </div>
                    <div id="secciones-productos-inicio" class="secciones-productos"></div>
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
                            <div class="campo-lectura-encabezado">
                                <strong>Información del local</strong>
                                <button type="button" id="btn-editar-local" class="btn-editar-icono oculto" aria-label="Editar local">
                                    <i data-lucide="pencil"></i>
                                </button>
                            </div>
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
                                <!-- Vista: Listado de Comerciantes -->
                <section id="vista-comerciantes" class="vista oculto">

                    <div id="panel-lista-comerciantes">
                        <h2>Comerciantes Registrados</h2>
                        <div class="filtros-busqueda">
                            <input type="text" id="com-admin-buscar" placeholder="Buscar por nombre, alias, correo o identificación...">
                            <select id="com-admin-filtro-estado">
                                <option value="activos">Activos</option>
                                <option value="todos">Todos</option>
                                <option value="inactivos">Inactivos</option>
                            </select>
                        </div>
                        <div id="lista-comerciantes" class="tarjetas"></div>
                    </div>

                    <div id="panel-detalle-comerciante" class="oculto">
                        <button type="button" id="btn-volver-comerciantes" class="boton-secundario">&larr; Volver al
                            listado</button>
                        <h2>Detalle del Comerciante</h2>

                        <input type="hidden" id="dc-idComerciante">

                        <div class="campo-lectura">
                            <img id="dc-foto-actual" src="" alt="Foto de perfil" class="imagen-producto oculto">
                            <p><strong>Nombre:</strong> <span id="dc-solo-nombre"></span></p>
                            <p><strong>Alias:</strong> <span id="dc-solo-alias"></span></p>
                            <p><strong>Correo:</strong> <span id="dc-solo-correo"></span></p>
                            <p><strong>Número de identificación:</strong> <span id="dc-identificacion"></span></p>
                            <p><strong>Estado:</strong> <span id="dc-solo-estado"></span></p>
                        </div>

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
                        <div class="filtros-busqueda">
                            <input type="text" id="cl-admin-buscar" placeholder="Buscar por nombre o correo...">
                            <select id="cl-admin-filtro-estado">
                                <option value="activos">Activos</option>
                                <option value="todos">Todos</option>
                                <option value="inactivos">Inactivos</option>
                            </select>
                        </div>
                        <div id="lista-clientes" class="tarjetas"></div>
                    </div>

                    <div id="panel-detalle-cliente" class="oculto">
                        <button type="button" id="btn-volver-clientes" class="boton-secundario">&larr; Volver al
                            listado</button>
                        <h2>Detalle del Cliente</h2>

                        <input type="hidden" id="dcl-idCliente">

                        <div class="campo-lectura">
                            <img id="dcl-foto-actual" src="" alt="Foto de perfil" class="imagen-producto oculto">
                            <p><strong>Nombre:</strong> <span id="dcl-solo-nombre"></span></p>
                            <p><strong>Correo:</strong> <span id="dcl-solo-correo"></span></p>
                            <p><strong>Número de identificación:</strong> <span id="dcl-identificacion"></span></p>
                            <p><strong>Dirección:</strong> <span id="dcl-direccion"></span></p>
                            <p><strong>Estado:</strong> <span id="dcl-solo-estado"></span></p>
                        </div>

                        <button type="button" id="btn-desactivar-cliente" class="boton-peligro">Desactivar
                            Cliente</button>
                        <button type="button" id="btn-activar-cliente" class="boton-secundario oculto">Reactivar
                            Cliente</button>

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

                 <!-- Vista: Ver Productos (Admin) -->
                <section id="vista-productos-admin" class="vista oculto">
                    <h2>Productos Registrados</h2>
                    <p class="ayuda">Todos los productos de todos los locales, para moderación.</p>

                    <div class="filtros-busqueda">
                        <input type="text" id="pa-buscar" placeholder="Buscar por nombre...">
                        <label class="ayuda"><input type="checkbox" id="pa-inactivos"> Mostrar también inactivos</label>
                    </div>

                    <div id="lista-productos-admin" class="tarjetas"></div>
                </section>

                              <section id="vista-historiales" class="vista oculto">
                    <div class="historial-header">
                        <h2>Historiales</h2>
                        <p class="ayuda">Consulta todos los cambios realizados en el sistema.</p>
                    </div>

                    <div class="historial-tools">
                        <select id="hist-filtro-entidad">
                            <option value="todos">Todos los clientes / comerciantes</option>
                            <option value="Cliente">Solo clientes</option>
                            <option value="Comerciante">Solo comerciantes</option>
                            <option value="Local">Solo locales</option>
                            <option value="Producto">Solo productos</option>
                        </select>
                        <input type="text" id="hist-buscar" class="historial-search" placeholder="Buscar en historiales...">
                    </div>

                    <div class="historial-tabs" id="hist-tabs"></div>

                    <div class="historial-table" id="hist-tabla">
                        <p class="ayuda" style="padding: 1.2rem;">Cargando...</p>
                    </div>

                    <div id="hist-panel-overlay" class="modal-overlay historial-panel-overlay oculto">
                        <div class="historial-panel">
                            <div class="historial-panel-header">
                                <h3>Detalle del historial</h3>
                                <button type="button" id="hist-panel-cerrar" class="modal-cerrar" aria-label="Cerrar">&times;</button>
                            </div>

                            <div class="historial-panel-tipo">
                                <span class="historial-icon" id="hist-panel-icono"></span>
                                <div>
                                    <strong id="hist-panel-tipo-nombre"></strong>
                                    <span class="etiqueta-tipo" id="hist-panel-entidad-badge"></span>
                                </div>
                            </div>

                            <div class="historial-panel-usuario">
                                <strong id="hist-panel-usuario"></strong>
                                <span class="ayuda" id="hist-panel-entidad-tipo"></span>
                            </div>

                            <div class="historial-panel-campo">
                                <span class="ayuda">Acción</span>
                                <p id="hist-panel-accion"></p>
                            </div>

                            <div class="historial-panel-campo">
                                <span class="ayuda">Fecha</span>
                                <p id="hist-panel-fecha"></p>
                            </div>

                            <div class="historial-panel-campo" id="hist-panel-cambio-wrap">
                                <span class="ayuda">Cambio registrado</span>
                                <p id="hist-panel-cambio"></p>
                            </div>
                        </div>
                    </div>
                </section>
                                <!-- Vista: Mi Perfil (Admin) -->
                <section id="vista-mi-perfil" class="vista oculto">
                    <h2>Mi Perfil</h2>
                    <p class="ayuda">Aquí puedes actualizar tus propios datos de acceso.</p>

                    <div class="campo-lectura" id="mp-info-solo-lectura">
                        <div class="campo-lectura-encabezado">
                            <strong>Información de la cuenta</strong>
                            <button type="button" id="btn-editar-mi-perfil" class="btn-editar-icono" aria-label="Editar mi perfil">
                                <i data-lucide="pencil"></i>
                            </button>
                        </div>
                        <p><strong>Nombre:</strong> <span id="mp-solo-nombre"></span></p>
                        <p><strong>Correo:</strong> <span id="mp-solo-correo"></span></p>
                    </div>

                    <form id="form-editar-mi-perfil" class="formulario oculto">
                        <label for="mp-nombreCompleto">Nombre completo</label>
                        <input type="text" id="mp-nombreCompleto" required>

                        <label for="mp-correo">Correo</label>
                        <input type="email" id="mp-correo" required>

                        <button type="submit">Guardar Cambios</button>
                    </form>

                    <h3 class="bloque-separado">Cambiar contraseña</h3>
                    <form id="form-cambiar-password-superadmin" class="formulario">
                        <label for="mp-password-actual">Contraseña actual</label>
                        <input type="password" id="mp-password-actual" required>

                        <label for="mp-password-nueva">Nueva contraseña</label>
                        <input type="password" id="mp-password-nueva" required>
                        <span class="ayuda" id="mp-password-msg">Mínimo 8 caracteres, con al menos una letra mayúscula.</span>

                        <button type="submit">Cambiar Contraseña</button>
                    </form>
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
                            <div class="icon-container icono-azul"><i data-lucide="users"></i></div>
                            <p class="stat-numero" id="stat-clientes">—</p>
                            <p class="stat-etiqueta">Clientes activos</p>
                        </div>
                        <div class="stat-card">
                            <div class="icon-container icono-verde"><i data-lucide="briefcase"></i></div>
                            <p class="stat-numero" id="stat-comerciantes">—</p>
                            <p class="stat-etiqueta">Comerciantes activos</p>
                        </div>
                        <div class="stat-card">
                            <div class="icon-container icono-ambar"><i data-lucide="user-x"></i></div>
                            <p class="stat-numero" id="stat-comerciantes-inactivos">—</p>
                            <p class="stat-etiqueta">Comerciantes inactivos</p>
                        </div>
                    </div>

                    <div class="dashboard-columnas bloque-separado">
                        <div class="dashboard-actividad">
                            <div class="dashboard-seccion-encabezado">
                                <h3>Actividad reciente</h3>
                                <button type="button" class="acceso-dashboard-boton dashboard-ver-todo" data-vista="vista-historiales">Ver todo &rarr;</button>
                            </div>
                            <div id="dashboard-actividad-lista"></div>
                        </div>

                        <div class="dashboard-accesos">
                            <h3>Accesos rápidos</h3>
                            <div class="accesos-dashboard-grid">
                                <button type="button" class="acceso-dashboard-boton acceso-dashboard-tarjeta" data-vista="vista-comerciantes">
                                    <i data-lucide="briefcase"></i>
                                    <span>Ver Comerciantes</span>
                                </button>
                                <button type="button" class="acceso-dashboard-boton acceso-dashboard-tarjeta" data-vista="vista-clientes">
                                    <i data-lucide="users"></i>
                                    <span>Ver Clientes</span>
                                </button>
                                <button type="button" class="acceso-dashboard-boton acceso-dashboard-tarjeta" data-vista="vista-listado">
                                    <i data-lucide="store"></i>
                                    <span>Ver Locales</span>
                                </button>
                                <button type="button" class="acceso-dashboard-boton acceso-dashboard-tarjeta" data-vista="vista-productos-admin">
                                    <i data-lucide="package"></i>
                                    <span>Ver Productos</span>
                                </button>
                                <button type="button" class="acceso-dashboard-boton acceso-dashboard-tarjeta" data-vista="vista-historiales">
                                    <i data-lucide="history"></i>
                                    <span>Historiales</span>
                                </button>
                            </div>
                        </div>
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

                       <footer class="pie-pagina" id="pie-pagina">
                <div class="pie-contenido">
                    <div class="pie-marca">
                        <strong>RapiVentas</strong>
                        <p>Conectamos clientes con locales cercanos, de forma rápida, simple y eficiente.</p>
                    </div>
                    <div class="pie-info">
                        <p>Proyecto académico de comercio local.</p>
                        <p>&copy; <span id="pie-anio"></span> RapiVentas. Todos los derechos reservados.</p>
                    </div>
                </div>
            </footer>
        </div> <!-- cierra .area-principal -->
    </div> <!-- cierra .app-shell -->
        <div id="modal-permiso-ubicacion" class="modal-overlay oculto">
        <div class="modal-contenido modal-permiso-contenido">
            <button type="button" id="permiso-ubicacion-cerrar" class="modal-cerrar" aria-label="Cerrar">&times;</button>

            <div class="modal-permiso-icono">
                <i data-lucide="map-pin"></i>
            </div>

            <h3>Vamos a pedirte tu ubicación</h3>
            <p class="modal-permiso-texto">
                Para mostrarte los locales y productos más cercanos a ti, tu navegador te va a pedir permiso de
                ubicación en un momento. Solo la usamos para calcular distancias nunca se guarda ni se comparte.
            </p>

            <div class="modal-permiso-botones">
                <button type="button" id="permiso-ubicacion-cancelar" class="boton-secundario">Ahora no</button>
                <button type="button" id="permiso-ubicacion-continuar" class="btn-comprar-producto">Continuar</button>
            </div>
        </div>
    </div>
        <div id="modal-local" class="modal-overlay oculto">
        <div class="modal-contenido">
            <button type="button" id="modal-local-cerrar" class="modal-cerrar" aria-label="Cerrar">&times;</button>

            <div class="modal-producto-imagen">
                <img id="modal-local-imagen" src="" alt="">
                <div id="modal-local-sin-imagen" class="modal-producto-sin-imagen oculto">
                    <i data-lucide="store"></i>
                </div>
            </div>

            <div class="modal-producto-info">
                <span id="modal-local-categoria" class="etiqueta-tipo"></span>
                <h3 id="modal-local-nombre"></h3>

                             <div class="modal-local-tabs">
                    <button type="button" id="modal-local-tab-detalles" class="modal-local-tab activo">Ver Detalles</button>
                    <button type="button" id="modal-local-tab-productos" class="modal-local-tab">Ver Productos</button>
                </div>

                <div id="modal-local-detalles-contenido">
                    <p id="modal-local-descripcion" class="modal-producto-descripcion"></p>

                    <p class="modal-local-dato">
                        <i data-lucide="phone" class="icon-sm"></i> <span id="modal-local-telefono"></span>
                    </p>
                    <p class="modal-local-dato">
                        <i data-lucide="map-pin" class="icon-sm"></i> <span id="modal-local-ubicacion"></span>
                    </p>
                </div>

                <div id="modal-local-productos-contenido" class="oculto">
                    <div id="modal-local-productos-lista"></div>
                </div>
            </div>
        </div>
    </div>

        <div id="modal-producto" class="modal-overlay oculto">
        <div class="modal-contenido">
            <button type="button" id="modal-producto-cerrar" class="modal-cerrar" aria-label="Cerrar">&times;</button>

                        <div class="modal-producto-imagen">
                <img id="modal-producto-imagen" src="" alt="">
                <div id="modal-producto-sin-imagen" class="modal-producto-sin-imagen oculto">
                    <i data-lucide="image-off"></i>
                </div>
                <span id="modal-producto-badge" class="tarjeta-producto-badge oculto"></span>
            </div>

            <div class="modal-producto-info">
                <div class="modal-producto-local">
                    <img id="modal-producto-logo-local" src="" alt="" class="oculto">
                    <span id="modal-producto-nombre-local"></span>
                </div>

                <h3 id="modal-producto-nombre"></h3>
                <span id="modal-producto-categoria" class="etiqueta-tipo"></span>

                <p id="modal-producto-descripcion" class="modal-producto-descripcion"></p>

                <p id="modal-producto-disponibles" class="ayuda"></p>

                <p id="modal-producto-cronometro" class="modal-producto-cronometro oculto" data-vence="">
                    <i data-lucide="timer" class="icon-sm"></i> <span class="cronometro-texto">--:--:--</span>
                </p>

                <div class="modal-producto-precio">
                    <s id="modal-producto-precio-original" class="oculto"></s>
                    <strong id="modal-producto-precio-final"></strong>
                </div>

                <button type="button" id="modal-producto-comprar" class="btn-comprar-producto">Comprar</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/app.js"></script>
    <script>lucide.createIcons();</script>
</body>

</html>