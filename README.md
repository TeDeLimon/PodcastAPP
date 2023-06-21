<h2>PodcastAPP</h2> 

<h3>Es una aplicación MVC realizada con el framework Symfony 6, TWIG, Bootstrap y MYSQL.</h3>

Se han utilizado las siguientes librerias y editores:
<ul>
    <li>HowlerJS, un reproductor de audio con soporte para múltiples codecs e integrable en cualquier dispositivo con JS</li>
    <li>KnpPaginatorBundle, un paginador de Symfony con una interfaz sencilla y rápida.</li>
    <li>SASS como compilador de código CSS.</li>
</ul>

<h4>Descripción</h4>
Se trata de crear una aplicación sencilla que trabaje con usuarios y podcast (publicación de un
audio con texto explicativo). La idea es una aplicación donde el usuario entra con sus
credenciales, accede a la plataforma y puede subir sus propios contenidos (podcast) y
gestionarlos, sin ver los de los demás. Solo el Admin puede ver todos los usuarios y podcasts y
gestionarlos. A continuación, se indican los campos obligatorios.

<h4>Requisitos</h4>
<ul>
    <li>Usuario: nombre, apellidos, email, password</li>
    <li>Podcast: titulo, fecha subida, descripción, audio, imagen, autor (usuario)</li>
    <li>Cada Usuario tiene su propia área(solo puede ver sus podcasts) donde alojará 1 o más podcasts</li>
</ul>




