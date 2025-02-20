# API Backend - Sistema de Empleados

Proyecto backend. Utiliza Symfony 6, JWT para la autenticación, y se conecta a MySQL. 

## Tecnologías Utilizadas

- **Symfony 6**: Framework PHP para el desarrollo del backend.
- **MySQL**: Base de datos.
- **JWT**: Autenticación y autorización mediante tokens JWT.
- **MailerService**: Envío de correos electrónicos.
- **API Externa**: https://ibillboard.com/api/positions

## Endpoints de la API

### 1. Registrar un Usuario - habilitado solo para ["ROLE_ADMIN"]
**POST** `/api/register`

Crea un nuevo usuario ROLE_ADMIN.

**Body (JSON)**:
{
    "email": "usuario@ejemplo.com",
    "password": "123456"
}

**Respuesta**:
- **200 OK**: { "message": "Usuario registrado exitosamente" }
- **400 Bad Request**: { "message": "Datos inválidos" }

---

### 2. Iniciar Sesión
**POST** `/api/login`

Genera un JWT para un usuario registrado.

**Body (JSON)**:
{
    "email": "usuario@ejemplo.com",
    "password": "123456"
}

**Respuesta**:
- **200 OK**: { "token": "jwt_token" }
- **401 Unauthorized**: { "message": "Credenciales inválidas" }

---

### 3. Obtener Perfil de Usuario
**GET** `/api/profile`

Obtiene la información del usuario autenticado.

**Respuesta**:
- **200 OK**: 
  {
      "email": "usuario@ejemplo.com",
      "roles": ["ROLE_ADMIN"] o ["ROLE_EMPLOYEE"]
  }

---

### 4. Listar Empleados
**GET** `/api/employees/`

Obtiene todos los empleados.

**Respuesta**:
- **200 OK**: 
  [
      {
          "id": 1,
          "firstName": "John",
          "lastName": "Doe",
          "email": "empleado@empresa.com",
          "position": "full-stack developer",
          "birthDate": "1990-01-01"
      },
      ...
  ]

---

### 5. Crear un Nuevo Empleado
**POST** `/api/employees/`

Crea un nuevo empleado.

**Body (JSON)**:
{
    "firstName": "John",
    "lastName": "Doe",
    "position": "full-stack developer",
    "birthDate": "1990-01-01",
    "email": "empleado@empresa.com",
    "password": "123456"
}

**Respuesta**:
- **201 Created**: { "message": "Empleado creado exitosamente" }
- **400 Bad Request**: { "error": "Datos inválidos" }

---

### 6. Actualizar Trabajo de un Empleado
**PUT** `/api/employees/{id}`

Permite a un empleado actualizar su puesto de trabajo.

**Body (JSON)**:
{
    "position": "full-stack developer"
}

**Respuesta**:
- **200 OK**: { "message": "Empleado actualizado exitosamente" }
- **403 Forbidden**: { "error": "No tienes permiso para editar este empleado." }

---

### 7. Eliminar un Empleado
**DELETE** `/api/employees/{id}`

Permite a un empleado eliminar su propio registro.

**Respuesta**:
- **200 OK**: { "message": "Empleado eliminado exitosamente" }
- **403 Forbidden**: { "error": "No tienes permiso para eliminar este empleado." }

---

### 8. Obtener Posiciones de la API Externa
**GET** `/api/employees/positions`

Obtiene un listado de posiciones desde una API externa.

**Respuesta**:
- **200 OK**: 
{
	"positions": [
		"full-stack developer",
		"front-end developer",
		"sw admin",
		"help desk",
		"scrum master",
		"product manager"
	]
}

## Instalación (Linux)

### Requisitos Previos

- PHP 8.1 o superior
- Composer
- Symfony 6.x
- Base de datos MySQL

### 1. Clonar el Repositorio
Clona el repositorio en tu máquina local usando Git:

git clone https://github.com/fedegon2k/empleados-backend
cd empleados-backend

### 2. Instalar Dependencias

composer install

### 3. Configurar el Archivo `.env`
Configura las variables de entorno en el archivo .env:

DATABASE_URL="mysql://usuario:password@127.0.0.1:3306/empleados"

MAILER_DSN=smtp://usuario:password@smtp.servidor.com:puerto

### 4. Generar Claves JWT
Ejecuta el siguiente comando para generar las claves de JWT:

php bin/console lexik:jwt:generate-keypair

Una vez generados los archivos `private.pem` y `public.pem`, debes colocarlos en /config/jwt/
Luego, actualiza tu archivo `.env`:

JWT_SECRET_KEY=tu_clave_secreta

JWT_PUBLIC_KEY_PATH=%kernel.project_dir%/config/jwt/public.pem

JWT_PRIVATE_KEY_PATH=%kernel.project_dir%/config/jwt/private.pem

### 5. Crear y Configurar la Base de Datos
Crea la base de datos y ejecuta las migraciones:

php bin/console doctrine:database:create

php bin/console doctrine:migrations:migrate

### 7. Ejecutar el Servidor Local
Finalmente, puedes ejecutar el servidor local de Symfony:

symfony serve

### 8. Acceder a la Aplicación
Accede a la aplicación en tu navegador visitando la URL `http://127.0.0.1:8000`


### 9. Importar Endpoints en Insomnia

Para facilitar las pruebas de la API, puedes importar los endpoints en Insomnia.

1. Descarga el archivo de exportación:
   [Insomnia Empleados](docs/insomnia_empleados.json)

2. Abre Insomnia y ve a **Application > Import/Export > Import Data > From File**.

3. Selecciona el archivo JSON

Esto configurará los endpoints para probar la API.

