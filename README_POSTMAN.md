# API Documentation

This README documents the main API endpoints for **Users** and **File Management** in the dashboard.  


It covers **Users, Roles, Authentication
(Login, Logout, Register), and Profile**.

It includes details for ***CRUD operations***, bulk actions, soft deletes, restores, and file handling.
AND **File Management**
---


## Authentication

### Login (User)

**Endpoint:** `POST /api/login`\
**Body:**

``` json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**

``` json
{
  "status": true,
  "token": "eyJhbGciOi..."
}
```

### Login (Admin)

**Endpoint:** `POST /api/dashboard/login`\
**Body:**

``` json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

### 🚪 Logout

**Endpoint:** `POST /api/logout`\
**Headers:** `Authorization: Bearer {token}`

### 📝 Register

**Endpoint:** `POST /api/register`\
**Body:**

``` json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

------------------------------------------------------------------------

## 👤 Profile

### Get Profile

**Endpoint:** `GET /api/profile`\
**Headers:** `Authorization: Bearer {token}`

### Update Profile

**Endpoint:** `PUT /api/profile`\
**Body:**

``` json
{
  "full_name": "John Updated",
  "phone_no": "1234567890",
  "gender": "male"
}
```

------------------------------------------------------------------------

## Users (Dashboard)

### List Users  
**Endpoint:** `GET /api/dashboard/users`  
**Params (optional):** `page`, `query` ,`search`, `export`, `report`, `report_types[]`,`report_types[]`, `is_active`, `only_trashed`
**Params Example:**  
```json
  page:1
  search:alaa
  export:1
  report:true
  report_types[]:by_role
  report_types[]:by_active
  is_active:1
  only_trashed:true
```
### Show User  
**Endpoint:** `GET /api/dashboard/users/{id}`  

### Create User  
**Endpoint:** `POST /api/dashboard/users`  
**Body Example:**  
```json
{
  "username": "newuser",
  "email": "new@example.com",
  "phone_no": "123456789",
  "country_id": 1,
  "roles": [2],
  "translations": [
    {"lang": "en", "full_name": "New User"}
  ]
}
```

### Update User  
**Endpoint:** `PUT /api/dashboard/users/{id}`  

### Delete User  
**Endpoint:** `DELETE /api/dashboard/users/{id}`  

### Bulk Delete Users  
**Endpoint:** `POST /api/dashboard/users/actions/delete`  
**Body Example:**  
```json
{ "ids": [5, 6, 7] }
```  
or delete all:  
```json
{ "ids": "all" }
```

### Force Delete User  
**Endpoint:** `DELETE /api/dashboard/users/actions/{id}/force`  
Permanently delete a user from the database.  

### Bulk Force Delete Users  
**Endpoint:** `POST /api/dashboard/users/actions/force-delete`  
**Body Example:**  
```json
{ "ids": [3, 4] }
```

### Restore User  
**Endpoint:** `POST /api/dashboard/users/actions/{id}/restore`  
```json
{ 
  "strategy": "modify"//default
}
```
Possible values (strategy): `"modify"`, `"replace"`, `"prevent"`  

---

### Bulk Restore Users  
**Endpoint:** `POST /api/dashboard/users/actions/restore`  
**Body Example:**  
```json
{ 
  "ids": [2, 3] ,
  "strategy": "modify"//default
}
```
Possible values (strategy): `"modify"`, `"replace"`, `"prevent"`  

---

### Activate/Deactivate  User  
**Endpoint:** `POST /api/dashboard/users/actions/{id}/activite`  
```json
{ 
  "strategy": "modify"//default
}
```
Possible values (strategy): `"modify"`, `"replace"`, `"prevent"`  

---

### Bulk Activate/Deactivate Users  
**Endpoint:** `POST /api/dashboard/users/actions/activation`  
**Body Example:**  
```json
{
  "ids": [2, 3, 4],
  "action_activation": "activate",
  "strategy": "modify"//default

}
```
Possible values (action_activation): `"activate"`, `"deactivate"`, `"toggle"`  
Possible values (strategy): `"modify"`, `"replace"`, `"prevent"`  

---

## File Management  

### Upload Files  
**Endpoint:** `POST api/dashboard/users/{id}/files`  
**Body (multipart/form-data):**  
- `files[]`: array of files  

**Response Example:**  
```json
{
  "status": true,
  "data": [
    {
      "id": 10,
      "name": "document.pdf",
     "files": [
            {
                "id": 187,
                "url": "uploads/users/WhatsApp Image 2025-08-01 at 07.50.35_0d48f9a6_1757458044.jpg",
                "fileable_id": 14,
                "fileable_type": "App\\Models\\User",
                "type": "file",
            }
     ]

      }
  ]
}
```

### Upload File  
**Endpoint:** `POST api/dashboard/users/{id}/file`  
**Body (multipart/form-data):**  
- `image`: image  

**Response Example:**  
```json
{
  "status": true,
  "data": [
    {
      "id": 10,
      "name": "document.pdf",
      "image": {
            "id": 215,
            "url": "uploads/banners/alaa_1757606558.jpg",
            "fileable_id": 40,
            "fileable_type": "App\\Models\\Banner",
            "type": "image",

      }
    }
  ]
}
```


### Delete File  
**Endpoint:** `DELETE /api/dashboard/users/{id}/file`  
Soft delete a file.  
**Response Example:**  
```json
{
  "status": true,
  "data": {
          "id": 15,
          "image": null
          }
}
```

### Bulk Delete Files  
**Endpoint:** `POST /api/dashboard/users/{id}/files`  
**Body Example:**  
```json
{ 
  "_method": "delete",
  "ids": [10, 12, 13]
}
```
**Response Example:**  
```json
{
  "status": true,
  "data": {
          "id": 15,
          "files": null
          }
}
```

