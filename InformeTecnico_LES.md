
# **Parte 1 \- SQLi**

## **a) SQLi error inicio de sesión**

|  |  |
| :---- | :---- |
| Escribo los valores…  | “ |
| En el campo… | User |
| Del formulario de la página… | http://localhost:8080/insert\_player.php\# |
| La consulta SQL que se ejecuta es… | SELECT userId, password FROM users WHERE username \= """. Field user introduced is: " |
| Campos del formulario web utilizados en la consulta SQL… | User |
| Campos del formulario web **no** utilizados en la consulta SQL… | Password |

## **b) Ataque inicio de sesión**

|  |  |
| :---- | :---- |
| Explicación del ataque | Para realizar este ataque se ha utilizado Burpsuit, con el que se ha configurado un ataque del tipo Cluster Bomb, definiendo variables para que iteren los diccionarios de cargas útiles hasta obtener las coincidencias |
| Campo de usuario con que el ataque ha tenido éxito | Usuario con id=2 (“ or userId=2-- \-) |
| Campo de contraseña con que el ataque ha tenido éxito | 1234 |

## **c) Arreglo de código**

|  |  |
| :---- | :---- |
| Explicación del error… | La función “SQLite3::escapeString()” sólo tiene en cuenta las comillas simples pero no las dobles. |
| Solución: Cambiar la línea con el código… |  $query \= SQLite3::escapeString('SELECT userId, password FROM users WHERE username \= "' . $user . '"'); $result \= $db-\>query($query) or die ("Invalid query: " . $query . ". Field user introduced is: " . $user);  |
| … por la siguiente línea… | $query \= $db-\>prepare('SELECT userId, password FROM users WHERE username \= :username'); $query-\>bindValue(':username', $user, SQLITE3\_TEXT); $result \= $stmt-\>execute();  |

## **d) Ataque comentarios**

|  |  |
| :---- | :---- |
| Vulnerabilidad detectada… | La inserción del comentario depende de la cookie que guarda el userId |
| Descripción del ataque… |  |
| ¿Cómo podemos hacer que sea seguro esta entrada? | Guardar el userId es una sesión en lugar de una cookie |

# **Parte 2 \- XSS**

## **a) Alert con XSS**

|  |  |
| :---- | :---- |
| Introduzco el mensaje… | \<script\>alert(“XSS”)\</script\> |
| En el formulario de la página… | http://localhost:8080/add\_comment.php?id=2 |

## **b) Caracter &**

|  |  |
| :---- | :---- |
| Explicación… | Esto puede deberse a que \&amp; representa el caracter & en HTML |

## **c) Problema en show\_comments.php**

|  |  |
| :---- | :---- |
| ¿Cuál es el problema? | No se escapan las salidas antes de mostrarlas en el HTML |
| Sustituyo el código de la/las líneas… | echo "\<div\> \<h4\> ". $row\['username'\] ."\</h4\>  \<p\>commented: " . $row\['body'\] .    "\</p\> \</div\>";  |
| …por el siguiente código… | echo "\<div\> \<h4\>" . htmlspecialchars($row\['username'\], ENT\_QUOTES, 'UTF-8') . "\</h4\> \<p\>commented: " . htmlspecialchars($row\['body'\], ENT\_QUOTES, 'UTF-8') .  "\</p\> \</div\>"; |

## **d) Otras páginas con XSS**

|  |  |
| :---- | :---- |
| Otras páginas afectadas… | Todas las páginas que utilizan el archivo add\_comment.php y show\_comments.php están afectadas por la vulnerabilidad XSS. |
| ¿Cómo lo he descubierto? | Ya que todas utilizan el mismo código. |

# **Parte 3 \- Control de acceso, autenticación y sesiones de usuarios**

## **a) Implementaciones “register.php”**

Para mejorar la seguridad de este código, podrían implementarse sesiones para que el usuario, una vez se registre, tenga un id de sesión predefinido con el que realizar otras peticiones con mayor facilidad y seguridad. De esta manera también se evita utilizar cookies en las que guardar el usuario y la contraseña, lo que hace que la aplicación sea más insegura.  
Además la contraseña debería estar cifrada en la base de datos, para que sea más complicado romperla.

## **b) Implementaciones “login”**

Para mejorar la seguridad del inicio de sesión, al igual que en el registro, podrían implementarse sesiones para evitar guardar información confidencial del usuario en una cookie como el usuario o la contraseña, y guardando únicamente el id de sesión.

## **c) Accesibilidad “register.php”**

Para que este código deje de ser accesible al resto de usuarios, debería encontrarse en la carpeta “private”. 

## **d) Accesibilidad carpeta “private”**

Para que esta carpeta no sea accesible al resto de usuarios, pueden configurarse restricciones desde el servidor.

## **e) Suplantación de usuarios**

Una vez implantados los cambios mencionados anteriormente, el usuario estaría asegurado ya que depende de la cookie de sesión que es única para cada usuario.

# **Parte 4 \- Servidores web**

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

# **Parte 5 \- CSRF**

## **a) Editar jugador**
|  |  |
| :---- | :---- |
| En el campo… | Team name |
| Introduzco… | \<br/\> \<br/\> \<br/\> \<a href="http://web.pagos/donate.php?amount=100\&receiver=attacker"\>Profile\</a\> |

## **b) Comentario enlace**

|  |  |
| :---- | :---- |
| En el campo… | Write your comment |
| Introduzco… | \<script\>fetch(‘http://web.pagos/donate.php?amount=100\&receiver=attacker’)\</script\> |

## **c) Visualización enlace**

Para verificar que se ejecute el enlace, habrá que comprobar que la cookie que contiene el id de sesión no está vacío, lo que significa que el usuario ha iniciado sesión.

## **d) Página “donate.php”**

Si se modifica la página “donate.php” para que las peticiones se realicen a través de POST, mejoraría notablemente la seguridad pero no la haría invulnerable.