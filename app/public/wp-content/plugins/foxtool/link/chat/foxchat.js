document.addEventListener('DOMContentLoaded', function() {
// chat on
let chatlastScrollTop = 0;
const chatmojs = document.getElementById('chat-mojs');
if (chatmojs) {
    window.addEventListener('scroll', function() {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > chatlastScrollTop) {
            chatmojs.classList.add('chathi');
        } else {
            chatmojs.classList.remove('chathi');
        }
        chatlastScrollTop = scrollTop <= 0 ? 0 : scrollTop; 
    });
}
// svg to chaton
const chaton2 = document.getElementById('ft-chaton2');
const chatona = document.getElementById('chatona');
if (chaton2 && chatona) {
	const originalIcon = chatona.innerHTML;
	const observer = new MutationObserver(() => {
		if (chaton2.style.display === 'block') {
			chatona.innerHTML = `
				<div class="original-icon" style="display: none;">${originalIcon}</div>
				<svg class="khacus" width="100%" height="100%" viewBox="0 0 70 70" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:1.41421">
					<g id="close_1_" transform="matrix(0.0803948,0,0,0.0803948,14.4189,14.4189)">
						<path d="M48.536,508.793C35.983,509.523 23.638,505.35 14.104,497.154C-4.7,478.238 -4.7,447.688 14.104,428.773L425.842,17.033C445.4,-1.268 476.089,-0.251 494.389,19.307C510.938,36.992 511.903,64.176 496.648,82.989L82.483,497.154C73.071,505.232 60.924,509.397 48.536,508.793Z" style="fill:#fff;fill-rule:nonzero"/>
						<path d="M459.791,508.793C447.069,508.739 434.875,503.689 425.842,494.729L14.102,82.988C-3.319,62.644 -0.95,32.029 19.393,14.607C37.55,-0.942 64.328,-0.942 82.483,14.607L496.648,426.347C516.2,444.652 517.211,475.343 498.906,494.896C498.178,495.673 497.425,496.426 496.648,497.154C486.506,505.973 473.16,510.187 459.791,508.793Z" style="fill:#fff;fill-rule:nonzero"/>
					</g>
				</svg>
			`;
		} else if (chaton2.style.display === 'none') {
			chatona.innerHTML = originalIcon;
		}
	});
	observer.observe(chaton2, { attributes: true, attributeFilter: ['style'] });
}
// navi
let navilastScrollTop = 0;
const navimojs = document.getElementById('navi-mojs');
const navimojsc = document.getElementById('ft-navi-chaton');
const navimojsm = document.getElementById('ft-navi-menu');
const chatontab1 = document.getElementById('ft-chaton2');
const chatontab2 = document.getElementById('ft-chaton');
if (navimojs) {
    window.addEventListener('scroll', function() {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > navilastScrollTop) {
            navimojs.classList.add('navihi');
			if (navimojsc) {
                navimojsc.style.display = 'none';
            }
            if (navimojsm) {
                navimojsm.style.display = 'none';
            }
			if (chatontab1) {
                chatontab1.style.display = 'none';
            }
			if (chatontab2) {
                chatontab2.style.display = 'none';
            }
        } else {
            navimojs.classList.remove('navihi');
        }
        navilastScrollTop = scrollTop <= 0 ? 0 : scrollTop; 
    });
}
// navi menu
const foxnavi = document.getElementById('foxnavi');
if (foxnavi) {
	foxnavi.addEventListener('click', function(event) {
		event.preventDefault(); 
		const element = document.querySelector('.ft-navi-me');
		if (element.style.display === 'block') {
			element.style.display = 'none';
		} else {
			element.style.display = 'block';
		}
	});
}
});
