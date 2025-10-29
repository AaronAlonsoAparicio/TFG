/* Imágenes de ejemplo. Sustituye por rutas locales: "assets/img/..." */
const IMAGES = [
  "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1600&q=80",
  "https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1600&q=80",
  "https://images.unsplash.com/photo-1518602164577-1a4a7f1a1a2e?w=1600&q=80",
  "https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?w=1600&q=80",
  "https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?w=1600&q=80",
  "https://images.unsplash.com/photo-1520975922284-9d06a20b81e8?w=1600&q=80"
];

/* Contenedor */
const stack = document.querySelector('.bg-stack');
const vw = () => Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
const vh = () => Math.max(document.documentElement.clientHeight, window.innerHeight || 0);

/* Áreas preferidas para no tapar el centro del título */
const SPOTS = [
  {x:[0.02,0.30], y:[0.10,0.45]}, // izquierda
  {x:[0.68,0.96], y:[0.15,0.55]}, // derecha
  {x:[0.15,0.40], y:[0.60,0.88]}, // abajo-izq
  {x:[0.60,0.90], y:[0.62,0.90]}  // abajo-der
];

function rand(a,b){ return a + Math.random()*(b-a); }
function pick(arr){ return arr[Math.floor(Math.random()*arr.length)]; }

/* Crea una imagen animada */
function spawn(){
  const url = pick(IMAGES);
  const el = document.createElement('div');
  el.className = 'float-img';

  const spot = pick(SPOTS);
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
  stack.appendChild(el);
}

/* Ritmo: 1 imagen nueva cada 900ms; límite de 12 simultáneas */
let timer = null;
function loop(){
  if (document.hidden) return; // pausa si pestaña inactiva
  if (stack.childElementCount < 12) spawn();
}
timer = setInterval(loop, 900);

/* Opcional: precarga ligera para evitar parpadeos */
IMAGES.forEach(src => { const i=new Image(); i.src=src; });
