/* Imágenes de ejemplo, son imagenes sacadas de unplash que son sin copyright */
const IMAGENES = [
  "https://plus.unsplash.com/premium_photo-1682096504254-4b6400241e91?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1170",
  "https://images.unsplash.com/photo-1563000215-e31a8ddcb2d0?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1156",
  "https://plus.unsplash.com/premium_photo-1723507238503-6b2af04ae4e6?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1170",
  "https://images.unsplash.com/photo-1649134296132-56606326c566?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1332",
  "https://images.unsplash.com/photo-1640461470346-c8b56497850a?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1074",
  "https://images.unsplash.com/photo-1754248332986-60fa90d7d958?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1170"
];


/* Contenedor donde van aparecer las imagenes flotando */
const contenedorImagenes = document.querySelector('.bg-stack');
const vw = () => Math.max(document.documentElement.clientWidth, window.innerWidth || 0); /* Una funcion que te dice el ancho de la pantalla en pixeles*/
const vh = () => Math.max(document.documentElement.clientHeight, window.innerHeight || 0); /* alto de la pantalla en pixeles */

/* Son las zonas de la pantalla donde van aparecer las imagenes para no tapar el titulo */
const zonas = [
  {x:[0.02,0.30], y:[0.10,0.45]}, // izquierda
  {x:[0.68,0.96], y:[0.15,0.55]}, // derecha
  {x:[0.15,0.40], y:[0.60,0.88]}, // abajo-izq
  {x:[0.60,0.90], y:[0.62,0.90]}  // abajo-der
];

/* Funciones para calcular numeros aleatorios */
function rand(a,b){ return a + Math.random()*(b-a); } 

/* Elige un elemento aleatorio de un array, por ejemplo una foto */
function elementoArray(arr){ 
  return arr[Math.floor(Math.random() * arr.length)]; 
}

/* Crea una imagen animada */
function nuevaImagenFlotante(){
  const url = elementoArray(IMAGENES);
  const el = document.createElement('div');
  el.className = 'float-img';

  const spot = elementoArray(zonas);
  const W = vw(), H = vh();

  // tamaño inicial grande (no sobre todo el título)
  const size = rand(240, 380);        // px
  const posX = rand(spot.x[0]*W, spot.x[1]*W) - size/2;
  const posY = rand(spot.y[0]*H, spot.y[1]*H) - size/2;

  // desplazamiento leve durante la animación
  const dx = rand(-40, 40);           // px
  const dy = rand(20, 80);            // px
  const dur = rand(12, 18);           // s

  el.style.width = size+'px';
  el.style.height = (size*0.62)+'px'; // relación aprox. panorámica
  el.style.left = posX+'px';
  el.style.top  = posY+'px';
  el.style.setProperty('--tx', '0px');
  el.style.setProperty('--ty', '0px');
  el.style.setProperty('--dx', dx+'px');
  el.style.setProperty('--dy', dy+'px');
  el.style.setProperty('--dur', dur+'s');
  el.style.backgroundImage = `url("${url}")`;

  el.addEventListener('animationend', ()=> el.remove());
  contenedorImagenes.appendChild(el);
}

/* Ritmo: 1 imagen nueva cada 900ms; límite de 12 simultáneas */
let timer = null;
function loop(){
  if (document.hidden) return; // pausa si pestaña inactiva
  if (contenedorImagenes.childElementCount < 12) nuevaImagenFlotante();
}
timer = setInterval(loop, 900);

/* Opcional: precarga ligera para evitar parpadeos */
IMAGENES.forEach(src => { const i=new Image(); i.src=src; });
