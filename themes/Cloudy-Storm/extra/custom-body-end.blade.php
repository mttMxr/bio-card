<script src='{{themeAsset("three.min.js")}}'></script>
<script>
  let scene,
	camera,
	renderer,
	cloudParticles = [],
	lightningBolts = [],
	flash;

// Lightning bolt generator with different types
function createLightningBolt(startX, startY, startZ, endX, endY, endZ, type = 'normal') {
	const allBolts = [];
	let segments, zigzag, branches, colors, linewidths;

	// Define lightning types
	if (type === 'thin') {
		// Тонкая молния
		segments = 25;
		zigzag = 20;
		branches = 1 + Math.floor(Math.random() * 2);
		colors = { main: 0x6688ff, branch: 0x5577dd, glow: 0x4466cc };
		linewidths = { main: 1, branch: 1 };
	} else if (type === 'powerful') {
		// Мощный разряд
		segments = 40;
		zigzag = 40;
		branches = 4 + Math.floor(Math.random() * 3);
		colors = { main: 0xaaccff, branch: 0x88aaff, glow: 0xffffff };
		linewidths = { main: 3, branch: 2 };
	} else {
		// Обычная молния
		segments = 30;
		zigzag = 30;
		branches = 2 + Math.floor(Math.random() * 2);
		colors = { main: 0x8899ff, branch: 0x6677dd, glow: 0x5588ff };
		linewidths = { main: 2, branch: 1 };
	}

	// Create main bolt path
	const points = [];
	for (let i = 0; i <= segments; i++) {
		const t = i / segments;
		const x = startX + (endX - startX) * t + (Math.random() - 0.5) * zigzag;
		const y = startY + (endY - startY) * t + (Math.random() - 0.5) * zigzag;
		const z = startZ + (endZ - startZ) * t + (Math.random() - 0.5) * (zigzag * 0.6);
		points.push(new THREE.Vector3(x, y, z));
	}

	// Main bolt
	const geometry = new THREE.BufferGeometry().setFromPoints(points);
	const material = new THREE.LineBasicMaterial({
		color: colors.main,
		linewidth: linewidths.main,
		transparent: true,
		opacity: 0
	});

	const bolt = new THREE.Line(geometry, material);
	bolt.userData = {
		lifetime: 0,
		maxLifetime: type === 'powerful' ? 0.4 + Math.random() * 0.3 : 0.3 + Math.random() * 0.2,
		fadeIn: true,
		type: type
	};
	allBolts.push(bolt);

	// Add glow effect for powerful lightning (duplicate lines with transparency)
	if (type === 'powerful') {
		for (let g = 0; g < 3; g++) {
			const glowGeometry = new THREE.BufferGeometry().setFromPoints(points);
			const glowMaterial = new THREE.LineBasicMaterial({
				color: colors.glow,
				linewidth: linewidths.main + g,
				transparent: true,
				opacity: 0
			});
			const glowBolt = new THREE.Line(glowGeometry, glowMaterial);
			glowBolt.userData = {
				lifetime: 0,
				maxLifetime: bolt.userData.maxLifetime,
				fadeIn: true,
				type: 'glow',
				glowIndex: g
			};
			allBolts.push(glowBolt);
		}
	}

	// Add branches
	for (let b = 0; b < branches; b++) {
		const branchStart = Math.floor(segments * (0.3 + Math.random() * 0.4));
		const branchPoint = points[branchStart];

		const branchPoints = [];
		const branchSegments = type === 'powerful' ? 15 : 10;
		const branchEndX = branchPoint.x + (Math.random() - 0.5) * (zigzag * 3);
		const branchEndY = branchPoint.y - Math.random() * (zigzag * 3);
		const branchEndZ = branchPoint.z + (Math.random() - 0.5) * (zigzag * 2);

		for (let i = 0; i <= branchSegments; i++) {
			const t = i / branchSegments;
			const x = branchPoint.x + (branchEndX - branchPoint.x) * t + (Math.random() - 0.5) * (zigzag * 0.5);
			const y = branchPoint.y + (branchEndY - branchPoint.y) * t + (Math.random() - 0.5) * (zigzag * 0.5);
			const z = branchPoint.z + (branchEndZ - branchPoint.z) * t + (Math.random() - 0.5) * (zigzag * 0.3);
			branchPoints.push(new THREE.Vector3(x, y, z));
		}

		const branchGeometry = new THREE.BufferGeometry().setFromPoints(branchPoints);
		const branchMaterial = new THREE.LineBasicMaterial({
			color: colors.branch,
			linewidth: linewidths.branch,
			transparent: true,
			opacity: 0
		});

		const branchBolt = new THREE.Line(branchGeometry, branchMaterial);
		branchBolt.userData = {
			lifetime: 0,
			maxLifetime: bolt.userData.maxLifetime * 0.8,
			fadeIn: true,
			type: 'branch'
		};
		allBolts.push(branchBolt);
	}

	return allBolts;
}

function triggerLightning() {
	// Random position in sky
	const startX = (Math.random() - 0.5) * 600;
	const startY = 400 + Math.random() * 100;
	const startZ = (Math.random() - 0.5) * 400;

	const endX = startX + (Math.random() - 0.5) * 100;
	const endY = -200;
	const endZ = startZ + (Math.random() - 0.5) * 50;

	// Randomly choose lightning type
	// 50% normal, 30% thin, 20% powerful
	const rand = Math.random();
	let type;
	if (rand < 0.3) {
		type = 'thin';
	} else if (rand < 0.8) {
		type = 'normal';
	} else {
		type = 'powerful';
	}

	const bolts = createLightningBolt(startX, startY, startZ, endX, endY, endZ, type);

	// Add all bolts to scene
	bolts.forEach(bolt => {
		scene.add(bolt);
		lightningBolts.push(bolt);
	});

	// Flash light - more intense for powerful lightning
	flash.position.set(startX, startY, startZ);
	if (type === 'powerful') {
		flash.power = 500 + Math.random() * 300;
	} else if (type === 'normal') {
		flash.power = 300 + Math.random() * 200;
	} else {
		flash.power = 200 + Math.random() * 150;
	}
}

function init() {
	scene = new THREE.Scene();
	camera = new THREE.PerspectiveCamera(
		60,
		window.innerWidth / window.innerHeight,
		1,
		1000
	);
	camera.position.z = 1;
	camera.rotation.x = 1.16;
	camera.rotation.y = -0.12;
	camera.rotation.z = 0.27;

	ambient = new THREE.AmbientLight(0x555555);
	scene.add(ambient);

	directionalLight = new THREE.DirectionalLight(0xffeedd);
	directionalLight.position.set(0, 0, 1);
	scene.add(directionalLight);

	flash = new THREE.PointLight(0x4488ff, 0, 500, 1.7);
	flash.position.set(200, 300, 100);
	scene.add(flash);

	renderer = new THREE.WebGLRenderer();

	scene.fog = new THREE.FogExp2(0x11111f, 0.002);
	renderer.setClearColor(scene.fog.color);

	renderer.setSize(window.innerWidth, window.innerHeight);
	document.body.appendChild(renderer.domElement);

	let loader = new THREE.TextureLoader();
	loader.load(
		"{{themeAsset('fog.png')}}",
		function (texture) {
			cloudGeo = new THREE.PlaneBufferGeometry(500, 500);
			cloudMaterial = new THREE.MeshLambertMaterial({
				map: texture,
				transparent: true
			});

			for (let p = 0; p < 25; p++) {
				let cloud = new THREE.Mesh(cloudGeo, cloudMaterial);
				cloud.position.set(
					Math.random() * 800 - 400,
					500,
					Math.random() * 500 - 450
				);
				cloud.rotation.x = 1.16;
				cloud.rotation.y = -0.12;
				cloud.rotation.z = Math.random() * 360;
				cloud.material.opacity = 0.6;
				cloudParticles.push(cloud);
				scene.add(cloud);
			}
			animate();
			window.addEventListener("resize", onWindowResize);
		}
	);
}

let lastLightningTime = 0;
let nextLightningDelay = 800 + Math.random() * 1200; // 0.8-2 seconds between strikes

function animate() {
	const currentTime = Date.now();
	const deltaTime = 0.016; // ~60fps

	cloudParticles.forEach((p) => {
		p.rotation.z -= 0.002;
	});

	// Lightning timing - very frequent
	if (currentTime - lastLightningTime > nextLightningDelay) {
		triggerLightning();
		lastLightningTime = currentTime;
		nextLightningDelay = 800 + Math.random() * 1200; // Random delay 0.8-2 seconds
	}

	// Update lightning bolts
	for (let i = lightningBolts.length - 1; i >= 0; i--) {
		const bolt = lightningBolts[i];
		bolt.userData.lifetime += deltaTime;

		const lifeProgress = bolt.userData.lifetime / bolt.userData.maxLifetime;

		// Different opacity logic for glow vs regular bolts
		if (bolt.userData.type === 'glow') {
			// Glow effect is more subtle
			const glowIntensity = 0.3 - (bolt.userData.glowIndex * 0.08);
			if (bolt.userData.fadeIn && lifeProgress < 0.2) {
				bolt.material.opacity = (lifeProgress / 0.2) * glowIntensity;
			} else {
				bolt.userData.fadeIn = false;
				bolt.material.opacity = Math.max(0, (1 - (lifeProgress - 0.2) / 0.8) * glowIntensity);
			}
		} else {
			// Regular bolts
			if (bolt.userData.fadeIn && lifeProgress < 0.3) {
				// Fast fade in
				bolt.material.opacity = lifeProgress / 0.3;
			} else {
				// Slower fade out
				bolt.userData.fadeIn = false;
				bolt.material.opacity = Math.max(0, 1 - (lifeProgress - 0.3) / 0.7);
			}
		}

		// Remove finished bolts
		if (bolt.userData.lifetime >= bolt.userData.maxLifetime) {
			scene.remove(bolt);
			bolt.geometry.dispose();
			bolt.material.dispose();
			lightningBolts.splice(i, 1);
		}
	}

	// Flash light fade
	if (flash.power > 0) {
		flash.power *= 0.92;
		if (flash.power < 1) flash.power = 0;
	}

	renderer.render(scene, camera);
	requestAnimationFrame(animate);
}

init();

function onWindowResize() {
	camera.aspect = window.innerWidth / window.innerHeight;
	camera.updateProjectionMatrix();

	renderer.setSize(window.innerWidth, window.innerHeight);
}

</script>