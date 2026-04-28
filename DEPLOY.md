# Deploy ERP Tareo Latam - Laragon

## Objetivo

Publicar la app Laravel en red local usando **Apache** (puerto 8082) para acceso desde otras PCs.

---

## 1. Estructura de Archivos

```
C:\laragon\
├── etc\
│   └── apache2\
│       ├── mod_php.conf           # Config PHP
│       ├── alias\
│       │   └── tareo-latam.conf  # Alias para tu app
│       └── custom-erp-tareo-latam.conf  # VirtualHost puerto 8082
├── www\
│   └── erp-tareo-latam\          # Tu proyecto Laravel
│       ├── public\
│       ├── storage\
│       └── ...
```
composer install
php artisan key:generate
-- php artisan migrate
-- php artisan db:seed
npm install
npm run build
npm run dev
php artisan serve
---

## 2. Configurar PHP

**Archivo:** `C:\laragon\etc\apache2\mod_php.conf`

```apache
# This file is auto-generated, so please keep it intact.
LoadModule php_module "C:/laragon/bin/php/php-8.5.4-Win32-vs17-x64/php8apache2_4.dll"
PHPIniDir "C:/laragon/bin/php/php-8.5.4-Win32-vs17-x64"
```

**Versión:** PHP 8.5.4 (la que funciona con Laravel 12)

---

## 3. Crear Alias (URL limpia)

**Archivo:** `C:\laragon\etc\apache2\alias\tareo-latam.conf`

```apache
Alias /tareo-latam "C:/laragon/www/erp-tareo-latam/public"

<Directory "C:/laragon/www/erp-tareo-latam/public">
    Options Indexes FollowSymLinks Includes ExecCGI MultiViews
    AllowOverride All
    Require all granted
    DirectoryIndex index.php index.html

    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [L]
    </IfModule>
</Directory>
```

---

## 4. Crear VirtualHost (Puerto 8082)

**Archivo:** `C:\laragon\etc\apache2\custom-erp-tareo-latam.conf`

```apache
Listen 8082

<VirtualHost *:8082>
    DocumentRoot "C:/laragon/www/erp-tareo-latam/public"

    <Directory "C:/laragon/www/erp-tareo-latam/public">
        Options Indexes FollowSymLinks Includes ExecCGI MultiViews
        AllowOverride All
        Require all granted

        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^ index.php [L]
        </IfModule>
    </Directory>

    ErrorLog "C:/laragon/www/erp-tareo-latam/storage/logs/apache_error.log"
    CustomLog "C:/laragon/www/erp-tareo-latam/storage/logs/apache_access.log" combined
</VirtualHost>
```

---

## 5. Configurar Apache Principal

**Archivo:** `C:\laragon\bin\apache\httpd-2.4.54-win64-VS16\conf\httpd.conf`

```apache
# Carpeta raíz (www general)
DocumentRoot "C:/laragon/www"
<Directory "C:/laragon/www">
    Options Indexes FollowSymLinks Includes ExecCGI MultiViews
    AllowOverride All
    Require all granted
</Directory>

# Include archivos de configuración
IncludeOptional "C:/laragon/etc/apache2/alias/*.conf"
IncludeOptional "C:/laragon/etc/apache2/sites-enabled/*.conf"
IncludeOptional "C:/laragon/etc/apache2/custom-*.conf"
Include "C:/laragon/etc/apache2/httpd-ssl.conf"
Include "C:/laragon/etc/apache2/mod_php.conf"
```

---

## 6. Reiniciar Laragon

### Desde UI (Recomendado)

1. Abrir **Laragon**
2. Click **Stop All** → esperar 3 segundos
3. Click **Start All** (debe estar verde 🟢)

### Verificar que está corriendo

```powershell
netstat -ano | findstr ":8082.*LISTENING"
```

Debería mostrar:

```
TCP    0.0.0.0:8082    0.0.0.0:0    LISTENING
```

---

## 7. Construir Frontend (Vite)

**Necesario para que funcione el diseño:**

```powershell
cd C:\laragon\www\erp-tareo-latam
npm run build
```

**Verificar:**

```powershell
ls C:\laragon\www\erp-tareo-latam\public\build
```

Debería mostrar archivos en `assets/`.

---

## 8. Abrir Firewall (Puerto 8082)

### Desde PowerShell (como Administrador)

```powershell
New-NetFirewallRule -DisplayName "ERP Tareo - TCP 8082" `
    -Direction Inbound `
    -Protocol TCP `
    -LocalPort 8082 `
    -Action Allow `
    -Profile Any
```

### Verificar regla

```powershell
Get-NetFirewallRule | Where-Object {$_.DisplayName -like "*8082*"}
```

---

## 9. Hosts (opcional)

Si quieres usar dominio en lugar de IP:

**Archivo:** `C:\Windows\System32\drivers\etc\hosts` (como Administrador)

```hosts
192.168.3.93 erp-tareo.test
```

Después acceso: `http://erp-tareo.test:8082/`

---

## 10. URLs de Acceso

| Tipo                | URL                                |
| ------------------- | ---------------------------------- |
| **Local**           | `http://localhost:8082/`           |
| **Local (Alias)**   | `http://localhost/tareo-latam/`    |
| **Otra PC**         | `http://192.168.3.93:8082/`        |
| **Otra PC (Alias)** | `http://192.168.3.93/tareo-latam/` |

---

## 11. Solución de Problemas

### Error: "ERR_CONNECTION_REFUSED"

- **Causa:** Apache no está corriendo
- **Solución:** Iniciar Laragon → Start All

### Error: "404 Not Found"

- **Causa:** Alias no se cargó o ruta incorrecta
- **Solución:**
    ```powershell
    C:\laragon\bin\apache\httpd-2.4.54-win64-VS16\bin\httpd.exe -t
    ```
    Debe mostrar "Syntax OK"

### Error: "500 Internal Server Error"

- **Causa:** Error en PHP
- **Ver logs:**
    ```powershell
    Get-Content C:\laragon\www\erp-tareo-latam\storage\logs\laravel.log -Tail 20
    ```

### Error: No carga estilos/CSS

- **Causa:** Falta compilar frontend
- **Solución:**
    ```powershell
    npm run build
    ```

### Cambios no se reflejan

```powershell
cd C:\laragon\www\erp-tareo-latam
php artisan config:clear
php artisan cache:clear
npm run build
```

---

## 12. Múltiples Apps

### Puerto 80 (raíz www)

```
http://192.168.3.93/app1/
http://192.168.3.93/app2/
```

### Puerto 8082

```
http://192.168.3.93:8082/
```

### Agregar otra app en puerto diferente:

1. Crear `C:\laragon\etc\apache2\custom-otraproyecto.conf` con puerto 8083
2. Reiniciar Laragon

---

## 13. Comandos Útiles

```powershell
# Ver configuración Apache
C:\laragon\bin\apache\httpd-2.4.54-win64-VS16\bin\httpd.exe -t

# Ver puertos activos
netstat -ano | findstr "LISTENING" | findstr ":80\|:808"

# Ver procesos Apache
tasklist | findstr httpd

# Reiniciar Apache desde consola
taskkill /F /IM httpd.exe
C:\laragon\bin\apache\httpd-2.4.54-win64-VS16\bin\httpd.exe

# IP del servidor
ipconfig

# Ver logs de Apache
Get-Content C:\laragon\bin\apache\httpd-2.4.54-win64-VS16\logs\error.log -Tail 10

# Ver logs de Laravel
Get-Content C:\laragon\www\erp-tareo-latam\storage\logs\laravel.log -Tail 10
```

---

## Resumen de Pasos

| Paso | Acción                    | Comando                           |
| ---- | ------------------------- | --------------------------------- |
| 1    | Configurar PHP 8.5.4      | Editar `mod_php.conf`             |
| 2    | Crear Alias o VirtualHost | Crear archivos en `etc/apache2/`  |
| 3    | Reiniciar Laragon         | Stop All → Start All              |
| 4    | Build frontend            | `npm run build`                   |
| 5    | Abrir firewall            | PowerShell (Admin)                |
| 6    | Verificar                 | `netstat -ano \| findstr ":8082"` |
| 7    | Acceder                   | `http://192.168.3.93:8082/`       |

---

## URLs de Prueba

```
http://localhost:8082/
http://localhost/tareo-latam/
http://192.168.3.93:8082/
http://192.168.3.93/tareo-latam/
```
