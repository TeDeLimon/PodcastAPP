import { Howl, Howler } from 'howler';

/**
* La clase Player contiene el estado de una playlist donde se almacenan todos los valores
* Esta clase incluye los métodos y eventos personalizados
* @param {Array} podcast Es un Array de objetos con los detalles de las canciones ({titulo, extensión, duración})
*/

/*Una vez haya terminado la carga de la ventana agregamos a window(self) las diferentes variables para un scope global*/
window.addEventListener('load', function () {

  // Cache references to DOM elements.
  var elms = ['track', 'timer', 'duration', 'playBtn', 'pauseBtn', 'prevBtn', 'nextBtn', 'playlistBtn', 'volumeBtn', 'progress', 'bar', 'wave', 'loading', 'playlist', 'list', 'volume', 'barEmpty', 'barFull', 'sliderBtn'];
  elms.forEach(function (elm) {
    window[elm] = document.getElementById(elm);
  });

  const playbtn = document.getElementById('playBtn');
  const pausebtn = document.getElementById('pauseBtn');
  let timer = document.getElementById('timer');
  let duration = document.getElementById('duration');
  let progress = document.getElementById('progress');
  let volumechanger = document.getElementById('volume');
  let silence = document.getElementById('silence');
  let previous = document.getElementById('previous');
  let next = document.getElementById('next');
  let track = document.getElementById('track');

  /*Asignación de escuchadores de eventos*/
  playbtn.addEventListener('click', reproducirPodcast);
  pausebtn.addEventListener('click', pausarPodcast);
  progress.addEventListener('mouseup', actualizarDuracion);
  volumechanger.addEventListener('mouseup', cambiarVolumen);
  silence.addEventListener('click', silenciarVolumen);
  previous.addEventListener('click', previousPodcast);
  next.addEventListener('click', nextPodcast);

  /*En cada página donde se ha incluido el player se realiza una carga masiva elementos que tiene como clase cambiarPodcast, esto nos permite identificar los atributos necesarios*/
  let elementsToPlay = document.querySelectorAll('.cambiarPodcast');
  elementsToPlay.forEach(element => element.addEventListener('click', cambiarPodcast));




  var sound = new Howl({
    src: ['root'],
    html5: true,
    onend: function () {
      detenerTimer();
      reestablecerTimer();
    },
    onplay: function () {
      generarDuracion(Math.round(sound.duration()));

    },
    onseek: function () {
      actualizarProgress(Math.round(sound.seek()))
    },
    onpause: function () {

    },
    onstop: function () {
      console.log('Se ha detenido la canción');
    }
  });


  progress.value = 0; /*Establecemos el progreso de la canción a 0 al iniciar*/
  let seconds = 0; /*Declaramos un contador de segundos a 0*/
  let interval = null; /*Declaramos lo que será un setInterval*/
  let isSilenced = false; /*Declaramos un booleano que contendrá el estado del volumen*/

  /*Función que reproduce un Podcast, solo se ejecuta si el reproductor no está reproduciendo*/
  function reproducirPodcast() {
    if (sound.playing()) return;
    sound.play();
    iniciarTimer(Math.round(sound.seek()));
  }

  /*Una función sencilla que pausa el reproductor*/
  function pausarPodcast() {
    sound.pause();
    detenerTimer();
  }

  /*Esta función para totalmente el reproductor*/
  function pararPodcast() {
    sound.stop();
  }

  /*Esta función recibe una duración en segundos y la devuelve formateado a TIME además de cambiar el contenido de progreso*/
  function generarDuracion(duracion) {
    duration.innerHTML = minutesFormat(duracion);
    progress.max = duracion;
  }

  /*Esta función recibe un event, indicando el número del segundo al que deseamos acceder de la pista, posteriormente actualizamos en el reproductor el segundo deseado*/
  function actualizarDuracion(e) {
    /*Sound devuelve duración igual a 0 cuando no hay ninguna pista*/
    if (!sound.duration) reestablecerTimer();
    sound.seek(e.target.value);
    seconds = Number(e.target.value);
  }

  /*Función que inicializa el interval modificando cada segundo el contador de segundos además del conteo actual de segundos en pantalla*/
  function iniciarTimer() {
    interval = window.setInterval(function () {
      seconds++;
      actualizarProgress(seconds);
      timer.innerHTML = minutesFormat(seconds);
    }, 1000);
  }

  /*Esta función limpia el setInterval*/
  function detenerTimer() {
    clearInterval(interval);
  }

  /*Reinicia el contador de segundos a 0, el progreso de la pista y el número de segundos mostrados*/
  function reestablecerTimer() {
    seconds = 0;
    progress.value = 0;
    timer.innerHTML = "00:00:00";
    return;
  }

  /*Actualiza el progreso de segundo en pantalla*/
  function actualizarProgress(seconds) {
    progress.value = seconds;
  }

  /*Función que recibe un evento del input rango y actualiza en la clase el volumen de la pista*/
  function cambiarVolumen(e) {
    if (e.target.value == 0) imagenSilencio();
    Howler.volume(e.target.value / 10);
    imagenVolumen();
  }

  /*Función que establece el volumen máximo y el volumen mínimo de acuerdo al controlador de estado de sonido*/
  function silenciarVolumen() {
    if (isSilenced) {
      imagenVolumen();
      Howler.volume(1);
      volumechanger.value = 10;
      isSilenced = false;
      return;
    }

    imagenSilencio();
    Howler.volume(0);
    volume.value = 0;
    isSilenced = true;
  }

  /*Función que modifica el innerHTMl y establece un SVG de silencio*/
  function imagenSilencio() {
    silence.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-volume-mute-fill" viewBox="0 0 16 16">
          <path d="M6.717 3.55A.5.5 0 0 1 7 4v8a.5.5 0 0 1-.812.39L3.825 10.5H1.5A.5.5 0 0 1 1 10V6a.5.5 0 0 1 .5-.5h2.325l2.363-1.89a.5.5 0 0 1 .529-.06zm7.137 2.096a.5.5 0 0 1 0 .708L12.207 8l1.647 1.646a.5.5 0 0 1-.708.708L11.5 8.707l-1.646 1.647a.5.5 0 0 1-.708-.708L10.793 8 9.146 6.354a.5.5 0 1 1 .708-.708L11.5 7.293l1.646-1.647a.5.5 0 0 1 .708 0z"/>
        </svg>
      `;
  }

  /*Función que modifica el innerHTMl y establece un SVG de máximo volumen*/
  function imagenVolumen() {
    silence.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-volume-up-fill" viewbox="0 0 16 16">
          <path d="M11.536 14.01A8.473 8.473 0 0 0 14.026 8a8.473 8.473 0 0 0-2.49-6.01l-.708.707A7.476 7.476 0 0 1 13.025 8c0 2.071-.84 3.946-2.197 5.303l.708.707z"/>
          <path d="M10.121 12.596A6.48 6.48 0 0 0 12.025 8a6.48 6.48 0 0 0-1.904-4.596l-.707.707A5.483 5.483 0 0 1 11.025 8a5.483 5.483 0 0 1-1.61 3.89l.706.706z"/>
          <path d="M8.707 11.182A4.486 4.486 0 0 0 10.025 8a4.486 4.486 0 0 0-1.318-3.182L8 5.525A3.489 3.489 0 0 1 9.025 8 3.49 3.49 0 0 1 8 10.475l.707.707zM6.717 3.55A.5.5 0 0 1 7 4v8a.5.5 0 0 1-.812.39L3.825 10.5H1.5A.5.5 0 0 1 1 10V6a.5.5 0 0 1 .5-.5h2.325l2.363-1.89a.5.5 0 0 1 .529-.06z"/>
        </svg>
      `;
  }

  /*Modifica en el html el nombre de la pista*/
  function cambiarTrack(nombreTrack) {
    track.innerHTML = nombreTrack;
  }

  /*
    Esta función es la que se llaman mediante un evento onclick. Recibe un evento con los datos de la pista (url, titulo). 
    Detiene el Timer, lo reestablece y para cualquier podcast anterior. Elimina la instancia del reproductor y vuelve a crear un nuevo reproductor.
    Cambia el título de la pista en la web e inicia el nuevo Podcast
  */

  function cambiarPodcast(e) {

    detenerTimer();
    reestablecerTimer();
    pararPodcast();

    sound.unload();

    sound = new Howl({
      src: [`/uploads/files/${e.target.dataset.audio}`],
      html5: true,
      onend: function () {
        detenerTimer();
        reestablecerTimer();
      },
      onplay: function () {
        generarDuracion(Math.round(sound.duration()));

      },
      onseek: function () {
        actualizarProgress(Math.round(sound.seek()))
      },
      onpause: function () {

      },
      onstop: function () {
        console.log('Se ha detenido la canción');
      }
    });

    cambiarTrack(e.target.dataset.titulo);
    sound.load();
    reproducirPodcast();
  }

  /*Esta función no está asociada, a la espera de terminar el resto de funcionalidades*/
  function previousPodcast() {
    console.log('Desde previous');
  }
  /*Esta función no está asociada, a la espera de terminar el resto de funcionalidades*/
  function nextPodcast() {
    console.log('Desde next');
  }

  /*Función helper para convertir los minutos a un formato 00:03:41 */
  function minutesFormat(minutes) {
    const date = new Date(minutes * 1000 - 600);
    const timeStr = date.toUTCString().split(" ")[4];
    return timeStr;
  }
});

