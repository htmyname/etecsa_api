### Login
* Usuario: root
* Contraseña: 12345

        Cambiar el usuario y la contraseña en el archivo libs/configs.php
### Listado de URL
* autApi
* outApi
* connect
* disconnect
* getTime

        Si no se especifica una URL carga por defecto autApi
        Las URL no son sensibles a mayúsculas y minúsculas
##
### Parámetros
* ##### autApi
        Requiere: POST[user] User y password de la api
                  POST[password]
        
        Devuelve: login = true (Si te logueas con éxito en la Api)
* ##### outApi
        Devuelve: logout = true (Si sales con éxito de la Api)
* ##### connect
        Requiere: POST[user] User y password de etecsa
                  POST[password] Valores admitidos [a-zA-Z0-9@*]
        
        Devuelve: msg = false (Si no estás logueado en la Api,
                               Si no recive los parametros)
                  dataLogout  (String con la información de desconexión)
                  dataUpdate  (String con la información para actualizar)
                  con_ini     (Valor Unix en el que inició la conexión)
* ##### disconnect
        Requiere: POST[dataLogout]
                  POST[con_ini]
                  POST[saldo]
                  POST[tiempo]
                  POST[tiempo_real]
                  POST[internet_price] (en centavos Ejemlo: 70)
        
        Devuelve: msg = false (Si no estás logueado en la Api, 
                               Si no recive los parametros,cuando 
                               con_ini = 0 o cuando estas sin conexión)
                  saldo       (Saldo disponible)
                  tiempo      (Tiempo disponible)
* ##### getTime
        Requiere: POST[internet_price] (en centavos Ejemlo: 70)
                  POST[dataUpdate]
        
        Devuelve: msg = false   (Si no estás logueado en la Api,
                                Si no recive los parametros o cuando 
                                estas sin conexión)
                  saldo         (Saldo disponible)
                  tiempo        (Tiempo disponible)
                  tiempo_real   (Tiempo real disponible al descontar 
                                 1 minuto despues de un segundo conectado)
                  tiempo_en_seg (Tiempo disponible en segundos)