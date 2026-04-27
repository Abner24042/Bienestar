# 🏥 BIENIESTAR — Sistema Integral de Bienestar

Plataforma web para la gestión de salud, alimentación y ejercicio para estudiantes del IEST Anáhuac.  
**Versión 2.2.0** · PHP MVC · MySQL · Vanilla JS

---

## ¿Qué hace la plataforma?

| Módulo | Descripción |
|---|---|
| 🍎 Alimentación | Catálogo de recetas con filtros y plan asignado por nutriólogo |
| 💪 Ejercicio | Rutinas por nivel/tipo y plan asignado por coach |
| 🧠 Salud Mental | Tests de bienestar, respiración guiada y recursos |
| 📅 Citas | Agendado de consultas con especialistas |
| 💬 Chat | Mensajería directa usuario ↔ especialista |
| 📋 Mi Plan | Plan personalizado asignado por el especialista |
| ⭐ Favoritos | Recetas y ejercicios guardados |
| 📰 Noticias | Blog de salud y bienestar |

---

## Tecnologías

- **Backend**: PHP 8.0+, MySQL 8.0, PDO, Composer
- **Frontend**: HTML5, CSS3, JavaScript ES6+ (Vanilla)
- **Librerías**: PHPDotenv, PHPMailer, Google OAuth 2.0, EmailJS
- **Arquitectura**: MVC con front controller y router propio

---

## Estructura del proyecto

```
Bienestar/
├── public/                  ← carpeta pública (apunta Apache aquí)
│   ├── index.php            ← todas las rutas pasan por aquí
│   ├── assets/css, js, img
│   └── pages/               ← vistas de cada sección
├── app/
│   ├── config/              ← config.php, database.php
│   ├── controllers/
│   ├── models/
│   ├── views/layouts/       ← header.php, footer.php
│   └── Router.php
├── controllers/             ← endpoints JSON
├── .env.example             ← plantilla de configuración (cópiala como .env)
├── .env                     ← este archivo no está en el repo, lo tienes que crear tú
├── install.php              ← crea los usuarios de prueba en la BD
├── test_password.php        ← diagnóstico si el login no funciona
├── sistema_usuarios.sql     ← estructura completa de la base de datos
└── composer.json
```

---

## Instalación (para el profe Yustre 👋)

### Lo que necesitas antes de empezar
- XAMPP con PHP 8.0+, Apache y MySQL corriendo
- Composer → [getcomposer.org](https://getcomposer.org)

---

### 1. Coloca el proyecto en htdocs

Descarga o clona el repo y ponlo en:
```
C:\xampp\htdocs\Bienestar\
```

---

### 2. Instala las dependencias

Abre una terminal dentro de la carpeta del proyecto y corre:
```bash
composer install
```
Esto crea la carpeta `vendor/` con todo lo que necesita PHP.

---

### 3. Crea el archivo `.env`

El archivo `.env` no está en el repo porque tiene credenciales. Tienes que crearlo tú:

```bash
# En cmd de Windows
copy .env.example .env
```

Abre el `.env` que se creó y cambia solo estas líneas:

```env
BASE_URL=http://localhost/Bienestar/public

DB_HOST=localhost
DB_NAME=sistema_usuarios
DB_USER=root
DB_PASS=
```

> Si pusiste el proyecto en otra carpeta (por ejemplo `htdocs\proyectos\Bienestar`), ajusta `BASE_URL` en consecuencia.

---

### 4. Importa la base de datos

1. Abre **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Clic en **Nueva** → nombre: `sistema_usuarios` → **Crear**
3. Selecciona esa base de datos en el panel izquierdo
4. Clic en **Importar** → **Seleccionar archivo** → elige `sistema_usuarios.sql` de la raíz del proyecto
5. Clic en **Continuar**

---

### 5. Corre `install.php` para crear los usuarios ⚠️

> Aunque hayas importado la base de datos, **corre este paso de todas formas**. La primera vez que lo monté tampoco me funcionó el login porque el hash de la contraseña quedó mal al importar el SQL. `install.php` borra y recrea los usuarios con el hash correcto generado por tu propia instalación de PHP, así que no hay forma de que falle.

Con Apache corriendo, abre en el navegador:
```
http://localhost/Bienestar/install.php
```

Deberías ver algo así:
```
🔄 Usuario admin@bieniestar.com ya existía — eliminado para recrearlo con hash correcto.
✅ admin@bieniestar.com creado correctamente.
   Contraseña: admin123

🔄 Usuario usuario@test.com ya existía — eliminado para recrearlo con hash correcto.
✅ usuario@test.com creado correctamente.
   Contraseña: usuario123

✅ Listo. Ya puedes iniciar sesión.
```

Si los usuarios no existían todavía, no muestra el mensaje de "ya existía" y los crea directo. De cualquier forma el resultado es el mismo.

---

### 6. Entra a la aplicación

```
http://localhost/Bienestar/public/
```

| Rol | Email | Contraseña |
|---|---|---|
| Administrador | admin@bieniestar.com | admin123 |
| Usuario | usuario@test.com | usuario123 |

---

## Rutas disponibles

Todas las páginas usan URLs limpias, no accedas directo a los `.php`.

| Ruta | Descripción |
|---|---|
| `/dashboard` | Panel principal |
| `/login` | Inicio de sesión |
| `/alimentacion` | Catálogo de recetas |
| `/ejercicio` | Catálogo de ejercicios |
| `/salud-mental` | Tests y recursos |
| `/noticias` | Blog |
| `/citas` | Gestión de citas |
| `/chat` | Mensajería |
| `/mi-plan` | Plan personalizado |
| `/favoritos` | Guardados |
| `/perfil` | Perfil del usuario |
| `/profesional` | Panel del especialista |
| `/admin` | Panel de administración |

---

## Si el login sigue sin funcionar

Abre:
```
http://localhost/Bienestar/test_password.php
```

- **✅ La contraseña coincide** → el hash está bien. Revisa que `BASE_URL` en `.env` coincida exactamente con la URL del navegador.
- **❌ La contraseña NO coincide** → corre `install.php` otra vez, eso lo arregla.

---

## Roles del sistema

| Rol | Descripción |
|---|---|
| `usuario` | Acceso a todas las secciones del estudiante |
| `coach` | Panel profesional + planes de ejercicio |
| `nutriologo` | Panel profesional + planes de recetas |
| `psicologo` | Panel profesional + recomendaciones |
| `admin` | Acceso total + panel de administración |

Para crear un especialista: regístrate normalmente y cambia el campo `rol` en phpMyAdmin → tabla `usuarios`.

---

## Google OAuth (opcional)

Para que funcione "Iniciar sesión con Google" necesitas crear credenciales en [Google Cloud Console](https://console.cloud.google.com) y agregarlas al `.env`. Sin eso el botón de Google no aparece, pero el login normal funciona sin problema.

---

## Notas técnicas

- Las tablas se **auto-crean** si no existen (los modelos tienen `CREATE TABLE IF NOT EXISTS`)
- Las columnas nuevas se **auto-agregan** sin necesidad de correr migraciones
- `vendor/` y `.env` están en `.gitignore` y nunca se suben al repo

---

## Créditos

**Desarrollado por**: Abner Borrego, Hannia Perez, Frida Azuara y Ana Paula  
**Institución**: IEST Anáhuac Tampico  
**Año**: 2024-2026
