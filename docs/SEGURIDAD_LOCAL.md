# Seguridad de la instalación local

## Alcance

- SQLite guarda todos los datos en `database/database.sqlite`.
- El archivo de base de datos y sus archivos auxiliares están excluidos de Git.
- Las contraseñas nunca se guardan de forma reversible: Laravel las almacena con un hash seguro.
- Los campos personales configurados en los modelos se cifran con Laravel `Crypt`, usando la clave local `APP_KEY`.
- El primer usuario creado desde `/install` recibe el rol de administrador.
- Después del primer registro, `/install` queda cerrado. Solo un administrador autenticado puede crear usuarios.

## Diferencia respecto de WhatsApp

El cifrado de extremo a extremo de WhatsApp protege mensajes que viajan entre dos dispositivos. Esta aplicación funciona
en un solo equipo, por lo que no existe un segundo extremo ni tráfico remoto que pueda cifrarse de la misma manera.
Aquí se aplica cifrado de datos en reposo y control de acceso local. No se debe presentar esta solución como
"cifrado de extremo a extremo".

## Protección de la clave

La confidencialidad de los campos cifrados depende de `APP_KEY`, almacenada en `.env`.

- No publique ni comparta `.env`.
- Proteja la cuenta de Windows y active BitLocker o el cifrado completo del disco.
- Para respaldar los datos, copie juntos `database/database.sqlite` y `.env` a un medio cifrado.
- Si se pierde `APP_KEY`, los campos cifrados no podrán recuperarse.
- Si un atacante obtiene al mismo tiempo la base de datos y `.env`, podrá descifrar los campos protegidos.

Para cifrar físicamente cada página del archivo SQLite sería necesario incorporar SQLCipher y distribuir un runtime PHP
compatible. Esa capa no está incluida en este cambio y debe evaluarse por separado antes de prometer cifrado integral
del archivo.
