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