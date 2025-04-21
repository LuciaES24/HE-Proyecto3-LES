# Informe Técnico

## Respuestas a las preguntas

### **Parte 1 \- SQLi**

#### **a) SQLi error inicio de sesión**

|  |  |
| :---- | :---- |
| Escribo los valores…  | “ |
| En el campo… | User |
| Del formulario de la página… | http://localhost:8080/insert\_player.php\# |
| La consulta SQL que se ejecuta es… | SELECT userId, password FROM users WHERE username \= """. Field user introduced is: " |
| Campos del formulario web utilizados en la consulta SQL… | User |
| Campos del formulario web **no** utilizados en la consulta SQL… | Password |

#### **b) Ataque inicio de sesión**

|  |  |
| :---- | :---- |
| Explicación del ataque | Para realizar este ataque se ha utilizado Burpsuit, con el que se ha configurado un ataque del tipo Cluster Bomb, definiendo variables para que iteren los diccionarios de cargas útiles hasta obtener las coincidencias |
| Campo de usuario con que el ataque ha tenido éxito | Usuario con id=2 (“ or userId=2-- \-) |
| Campo de contraseña con que el ataque ha tenido éxito | 1234 |

#### **c) Arreglo de código**

|  |  |
| :---- | :---- |
| Explicación del error… | La función “SQLite3::escapeString()” sólo tiene en cuenta las comillas simples pero no las dobles. |
| Solución: Cambiar la línea con el código… |  $query \= SQLite3::escapeString('SELECT userId, password FROM users WHERE username \= "' . $user . '"'); $result \= $db-\>query($query) or die ("Invalid query: " . $query . ". Field user introduced is: " . $user);  |
| … por la siguiente línea… | $query \= $db-\>prepare('SELECT userId, password FROM users WHERE username \= :username'); $query-\>bindValue(':username', $user, SQLITE3\_TEXT); $result \= $stmt-\>execute();  |

#### **d) Ataque comentarios**

|  |  |
| :---- | :---- |
| Vulnerabilidad detectada… | La inserción del comentario depende de la cookie que guarda el userId |
| Descripción del ataque… |  |
| ¿Cómo podemos hacer que sea seguro esta entrada? | Guardar el userId es una sesión en lugar de una cookie |

### **Parte 2 \- XSS**

#### **a) Alert con XSS**

|  |  |
| :---- | :---- |
| Introduzco el mensaje… | \<script\>alert(“XSS”)\</script\> |
| En el formulario de la página… | http://localhost:8080/add\_comment.php?id=2 |

#### **b) Caracter &**

|  |  |
| :---- | :---- |
| Explicación… | Esto puede deberse a que \&amp; representa el caracter & en HTML |

#### **c) Problema en show\_comments.php**

|  |  |
| :---- | :---- |
| ¿Cuál es el problema? | No se escapan las salidas antes de mostrarlas en el HTML |
| Sustituyo el código de la/las líneas… | echo "\<div\> \<h4\> ". $row\['username'\] ."\</h4\>  \<p\>commented: " . $row\['body'\] .    "\</p\> \</div\>";  |
| …por el siguiente código… | echo "\<div\> \<h4\>" . htmlspecialchars($row\['username'\], ENT\_QUOTES, 'UTF-8') . "\</h4\> \<p\>commented: " . htmlspecialchars($row\['body'\], ENT\_QUOTES, 'UTF-8') .  "\</p\> \</div\>"; |

#### **d) Otras páginas con XSS**

|  |  |
| :---- | :---- |
| Otras páginas afectadas… | Todas las páginas que utilizan el archivo add\_comment.php y show\_comments.php están afectadas por la vulnerabilidad XSS. |
| ¿Cómo lo he descubierto? | Ya que todas utilizan el mismo código. |

### **Parte 3 \- Control de acceso, autenticación y sesiones de usuarios**

#### **a) Implementaciones “register.php”**

Para mejorar la seguridad de este código, podrían implementarse sesiones para que el usuario, una vez se registre, tenga un id de sesión predefinido con el que realizar otras peticiones con mayor facilidad y seguridad. De esta manera también se evita utilizar cookies en las que guardar el usuario y la contraseña, lo que hace que la aplicación sea más insegura.  
Además la contraseña debería estar cifrada en la base de datos, para que sea más complicado romperla.

#### **b) Implementaciones “login”**

Para mejorar la seguridad del inicio de sesión, al igual que en el registro, podrían implementarse sesiones para evitar guardar información confidencial del usuario en una cookie como el usuario o la contraseña, y guardando únicamente el id de sesión.

#### **c) Accesibilidad “register.php”**

Para que este código deje de ser accesible al resto de usuarios, debería encontrarse en la carpeta “private”. 

#### **d) Accesibilidad carpeta “private”**

Para que esta carpeta no sea accesible al resto de usuarios, pueden configurarse restricciones desde el servidor.

#### **e) Suplantación de usuarios**

Una vez implantados los cambios mencionados anteriormente, el usuario estaría asegurado ya que depende de la cookie de sesión que es única para cada usuario.

### **Parte 4 \- Servidores web**

Para reducir el riesgo de ataques en el servidor web, pueden implementarse medidas tanto sobre el sistema operativo, como en la organización o algunos aspectos más técnicos:

1. Mantener el software actualizado.  
2. Configurar correctamente el servidor para evitar puertos innecesarios o cuentas predeterminadas.  
3. Utilizar una autenticación más segura.  
4. Configurar un firewall.  
5. Cifrar conexiones remotas utilizando protocolos como SSH o HTTPS en lugar de HTTP.  
6. Realizar monitorización de las acciones que realizan los usuarios.  
7. Realizar copias de seguridad.  
8. Cifrar datos sensibles.  
9. Mejorar la seguridad de la base de datos.

### **Parte 5 \- CSRF**

#### **a) Editar jugador**
|  |  |
| :---- | :---- |
| En el campo… | Team name |
| Introduzco… | \<br/\> \<br/\> \<br/\> \<a href="http://web.pagos/donate.php?amount=100\&receiver=attacker"\>Profile\</a\> |

#### **b) Comentario enlace**

|  |  |
| :---- | :---- |
| En el campo… | Write your comment |
| Introduzco… | \<script\>fetch(‘http://web.pagos/donate.php?amount=100\&receiver=attacker’)\</script\> |

#### **c) Visualización enlace**

Para verificar que se ejecute el enlace, habrá que comprobar que la cookie que contiene el id de sesión no está vacío, lo que significa que el usuario ha iniciado sesión.

#### **d) Página “donate.php”**

Si se modifica la página “donate.php” para que las peticiones se realicen a través de POST, mejoraría notablemente la seguridad pero no la haría invulnerable.

## Paso a paso

### **1 \- SQLi**

#### **a) SQLi error inicio de sesión**

En el formulario de inicio de sesión podemos realizar una inyección SQL en el apartado del usuario. Esto lo sabemos ya que al provocar un error, por ejemplo introduciendo comillas dobles (“), podemos ver la consulta.  
![Imagen 1](/pictures/1.png)

Error obtenido con la consulta que se realiza a la base de datos:  
![Imagen 2](/pictures/2.png)

#### **b) Ataque inicio de sesión**

Para impersonar a un usuario se va a utilizar Burpsuite, herramienta con la que se va a interceptar la petición de inicio de sesión, realizando una inyección SQL sobre el apartado del usuario.   
Gracias al error obtenido en el apartado anterior se sabe que se puede utilizar el id del usuario para descubrir los datos del mismo.  
Para realizar este ataque se va a elegir el tipo “Cluster Bomb”, con el que tras haber asignado el id y la contraseña como variables se introducirán los diccionarios necesarios para que el ataque haga las pruebas hasta obtener el correcto.

Tipo de ataque seleccionado:  
![Imagen 3](/pictures/3.png)

Inyección SQL realizada con las variables asignadas:  
![Imagen 4](/pictures/4.png)

Diccionario numérico para realizar sobre la variable del id del usuario:  
![Imagen 5](/pictures/5.png)

Diccionario de palabras para realizar sobre la variable de la contraseña:

![Imagen 6](/pictures/6.png)

Resultados obtenidos tras el ataque viendo que una de las respuestas tiene un mayor tamaño:  
![Imagen 7](/pictures/7.png)

Probando a iniciar sesión con los datos obtenidos del ataque:  
![Imagen 10](/pictures/10.png)

Una vez iniciada la sesión se prueba a poner un comentario para ver el usuario:  
![Imagen 8](/pictures/8.png)

#### **c) Arreglo de código**

Al acceder al código del archivo auth.php puede verse la función areUserAndPasswordValid(), en la que se utiliza la función SQLite3::escapeString(). Sin embargo, esta función no hace que el código sea seguro ante la inyección SQL, ya que esta, solo toma en cuenta las comillas simples pero no las dobles.  
A continuación pueden verse las líneas de código que será necesario modificar:  
![Imagen 9](/pictures/9.png)

Nuevo código que se debería utilizar:  
`$query = $db->prepare('SELECT userId, password FROM users WHERE username = :username');`

`$query->bindValue(':username', $user, SQLITE3_TEXT);`

`$result = $stmt->execute();`

De esta forma, no se introduce directamente el valor de la variable en la consulta, si no que se vincula en valor a la nueva variable asignada en la consulta utilizando el formato propio de la base de datos para convertirlo en cadena.

#### **d) Ataque comentarios**

La vulnerabilidad detectada en el código, es que al insertar el comentario, el usuario depende de la cookie que guarda el “userId”, por lo que esta se puede modificar.  
![Imagen 26](/pictures/26.png)

He probado a crear esta cookie y modificarla pero no he conseguido modificar el usuario.  
![Imagen 27](/pictures/27.png)

A pesar de haber cambiado el valor de la cookie ambos comentarios se han realizado desde el mismo usuario.  
![Imagen 28](/pictures/28.png)

### **2 \- XSS**

#### **a) Alert con XSS**

Para comprobar si la aplicación es vulnerable a XSS, hay que acceder a la página [http://localhost:8080/add\_comment.php?id=2](http://localhost:8080/add_comment.php?id=2), en la cual se pueden añadir comentarios al jugador cuyo equipo es JS. Para comprobarlo se escribirá un comentario con un script que ejecute una alerta.

![Imagen 11](/pictures/11.png)

De esta manera, al publicar el comentario con el código JavaScript y acceder a la página [http://localhost:8080/show\_comments.php?id=2](http://localhost:8080/show_comments.php?id=2) aparece el “alert” con el mensaje que ha sido asignado, lo que demuestra que es vulnerable a XSS.  
![Imagen 12](/pictures/12.png)

#### **b) Caracter &**

El caracter “\&amp;” suele utilizarse ya que en HTML representa el caracter &. Si un usuario pusiera sólo “&” podría limpiarse al ser un caracter de escape pero si utiliza “\&amp;” en la aplicación sí que aparecería “&”.

#### **c) Problema en show\_comments.php**

En el archivo “show\_comments.php” se puede observar que al mostrar los comentarios, el código no escapa la salida antes de mostrar el HTML.   
![Imagen 13](/pictures/13.png)

El código de dentro del bucle habría que cambiarlo por el siguiente:  
`echo "<div>`  
`<h4>" . htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') . "</h4>`  
`<p>commented: " . htmlspecialchars($row['body'], ENT_QUOTES, 'UTF-8') .`   
`"</p>`  
`</div>";`

#### **d) Otras páginas con XSS**

Todas las páginas que utilizan el archivo add\_comment.php y show\_comments.php están afectadas por la vulnerabilidad XSS ya que el código es vulnerable. Por lo que al insertar un comentario en cualquiera de los jugadores podremos inyectar código JavaScript, mostrándose en la pantalla de listar comentarios de cada jugador. 

### **3 \- Control de acceso, autenticación y sesiones de usuarios**

#### **a) Implementaciones “register.php”**

En primer lugar se van a implementar las sesiones. Para ello será necesario iniciar la sesión en cada una de las pantallas de la aplicación. Por otro lado, en el código de “register.php”, habrá que iniciar también la sesión y designar el usuario y la contraseña a la misma de la siguiente manera:  
![Imagen 19](/pictures/19.png)

De esta manera, si se realiza correctamente la inserción del nuevo usuario en la base de datos, se asignará el usuario y la contraseña a la sesión.  
Además, para mejorar la seguridad de la contraseña dentro de la base de datos, se hará el hash de la contraseña antes de insertarla en la base de datos utilizando la función “password\_hash()”.  
![Imagen 14](/pictures/14.png)

#### **b) Implementaciones “login.php”**

Al igual que en el “register.php”, habrá que iniciar la sesión y asignar los valores de nombre usuario y contraseña a la misma una vez esté validada la información con la base de datos.   
![Imagen 16](/pictures/16.png)
![Imagen 15](/pictures/15.png)

Además, como la contraseña estará hasheada en la base de datos, habrá que hacer el hash de la contraseña que ha introducido el usuario al iniciar sesión.  
Por otro lado, en este archivo también se encuentra el código del “logout”, en el cual hay que eliminar el valor de la cookie de sesión y eliminar los datos de la misma.  
![Imagen 17](/pictures/17.png)

#### **c) Accesibilidad “register.php”**

Para evitar que otros usuarios puedan acceder al código del “register.php”, se puede mover este archivo a la carpeta “private”.  
![Imagen 18](/pictures/18.png)

#### **d) Accesibilidad carpeta “private”**

Para configurar la accesibilidad de esta carpeta, pueden configurarse las restricciones desde el servidor apache, concretamente en el archivo .htaccess, el cual si no existe debe crearse añadiendo la ruta del archivo que contendrá el usuario y la contraseña, el cual se crea ejecutando el comando “htpasswd \-c Filename username”.   
![Imagen 24](/pictures/24.png)

#### **e) Suplantación de usuarios**

Una vez implantados los cambios mencionados anteriormente, el usuario estaría asegurado ya que depende de la cookie de sesión que es única para cada usuario.

### **4 \- Servidores web**

Para reducir el riesgo de ataques en el servidor web, pueden implementarse medidas tanto sobre el sistema operativo, como en la organización o algunos aspectos más técnicos:

1. Mantener el software actualizado.  
2. Configurar correctamente el servidor para evitar puertos innecesarios o cuentas predeterminadas.  
3. Utilizar una autenticación más segura.  
4. Configurar un firewall.  
5. Cifrar conexiones remotas utilizando protocolos como SSH o HTTPS en lugar de HTTP.  
6. Realizar monitorización de las acciones que realizan los usuarios.  
7. Realizar copias de seguridad.  
8. Cifrar datos sensibles.  
9. Mejorar la seguridad de la base de datos.

### **5 \- CSRF**

#### **a) Editar jugador**

Para añadir el botón bajo el nombre del equipo, al editar algún jugador habrá que añadir el código que se muestra en la imagen para que al mostrar el jugador aparezca el botón “Profile”, el cual lleve al enlace proporcionado.  
![Imagen 20](/pictures/20.png)  
![Imagen 21](/pictures/21.png)  
![Imagen 22](/pictures/22.png)

De esta forma, el botón “Profile” llevará directamente al enlace que realiza la transferencia.

#### **b) Comentario enlace**

Para que este código se ejecute al ver los comentarios de un jugador habrá que utilizar la función “fetch()” para que acceda al enlace.  
![Imagen 23](/pictures/23.png)

#### **c) Visualización enlace**

Para verificar que se ejecute el enlace, habrá que comprobar que la cookie que contiene el id de sesión no está vacío, lo que significa que el usuario ha iniciado sesión.

#### **d) Página “donate.php”**

Si la página recibe los parámetros por POST en lugar de GET mejoraría la seguridad de la petición, ya que para realizarla hay que conocer previamente los parámetros que contiene dicha petición. Para realizarla se puede utilizar la aplicación Insomnia, con la que se crea la petición POST y sus parámetros.  
![Imagen 25](/pictures/25.png)

