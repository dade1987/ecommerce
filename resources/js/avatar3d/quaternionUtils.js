/**
 * ============================================================================
 * QUATERNION UTILITIES - Conversioni e offset per animazioni 3D
 * ============================================================================
 *
 * COSA SONO I QUATERNION?
 * -----------------------
 * I quaternion sono un sistema matematico per rappresentare rotazioni 3D.
 * Inventati da William Rowan Hamilton nel 1843, sono preferiti agli angoli
 * di Eulero (X, Y, Z in gradi) nelle animazioni 3D perché:
 *
 * 1. NO GIMBAL LOCK: Gli angoli Euler soffrono di "gimbal lock" - quando due
 *    assi si allineano, si perde un grado di libertà. I quaternion evitano
 *    questo problema completamente.
 *
 * 2. INTERPOLAZIONE FLUIDA: Interpolare tra due quaternion (SLERP) produce
 *    rotazioni fluide e naturali. Interpolare angoli Euler causa movimenti
 *    strani e innaturali.
 *
 * 3. COMPOSIZIONE SEMPLICE: Combinare rotazioni è una semplice moltiplicazione
 *    di quaternion. Con Euler serve trigonometria complessa.
 *
 *
 * STRUTTURA DI UN QUATERNION
 * --------------------------
 * Un quaternion ha 4 componenti: q = (x, y, z, w)
 *
 * - w: parte scalare (reale)
 * - x, y, z: parte vettoriale (immaginaria)
 *
 * Per rotazioni, un quaternion unitario (lunghezza = 1) rappresenta:
 * - L'ASSE di rotazione: normalizzato in (x, y, z)
 * - L'ANGOLO di rotazione: codificato in w e nella magnitudine di (x,y,z)
 *
 * Formula: q = (sin(θ/2)*ax, sin(θ/2)*ay, sin(θ/2)*az, cos(θ/2))
 * dove θ è l'angolo e (ax, ay, az) è l'asse normalizzato.
 *
 *
 * CONVERSIONE GRADI EULER → QUATERNION
 * ------------------------------------
 * Gli angoli Euler (rotX, rotY, rotZ in gradi) vengono convertiti così:
 *
 * 1. Converti gradi in radianti: rad = deg * (π / 180)
 *
 * 2. Calcola seni e coseni dei mezzi angoli:
 *    c1 = cos(rotX/2), s1 = sin(rotX/2)
 *    c2 = cos(rotY/2), s2 = sin(rotY/2)
 *    c3 = cos(rotZ/2), s3 = sin(rotZ/2)
 *
 * 3. Combina secondo l'ordine di rotazione (qui usiamo XYZ):
 *    x = s1*c2*c3 + c1*s2*s3
 *    y = c1*s2*c3 - s1*c2*s3
 *    z = c1*c2*s3 + s1*s2*c3
 *    w = c1*c2*c3 - s1*s2*s3
 *
 *
 * MOLTIPLICAZIONE DI QUATERNION (per applicare offset)
 * ----------------------------------------------------
 * Per applicare un offset di rotazione, moltiplichiamo i quaternion:
 *
 * risultato = offsetQuat × originaleQuat
 *
 * L'ordine conta! In Three.js/OpenGL l'ordine è "locale prima":
 * - offsetQuat × originaleQuat: applica offset DOPO la rotazione originale
 * - originaleQuat × offsetQuat: applica offset PRIMA della rotazione originale
 *
 * Formula moltiplicazione q1 × q2:
 *   x = q1.w*q2.x + q1.x*q2.w + q1.y*q2.z - q1.z*q2.y
 *   y = q1.w*q2.y - q1.x*q2.z + q1.y*q2.w + q1.z*q2.x
 *   z = q1.w*q2.z + q1.x*q2.y - q1.y*q2.x + q1.z*q2.w
 *   w = q1.w*q2.w - q1.x*q2.x - q1.y*q2.y - q1.z*q2.z
 *
 *
 * ESEMPIO PRATICO
 * ---------------
 * Problema: I piedi dell'avatar sono ruotati 20° in avanti (asse X).
 * Soluzione: Applicare offset di -20° sull'asse X.
 *
 * 1. Offset in gradi: { x: -20, y: 0, z: 0 }
 * 2. Converti in quaternion: eulerDegreesToQuaternion(-20, 0, 0)
 * 3. Per ogni keyframe dell'animazione, moltiplica:
 *    nuovoQuat = offsetQuat × quaternionAnimazione
 *
 * ============================================================================
 */

import * as THREE from 'three';

/**
 * Converte gradi in radianti
 * @param {number} degrees - Angolo in gradi
 * @returns {number} Angolo in radianti
 */
export function degreesToRadians(degrees) {
  return degrees * (Math.PI / 180);
}

/**
 * Converte radianti in gradi
 * @param {number} radians - Angolo in radianti
 * @returns {number} Angolo in gradi
 */
export function radiansToDegrees(radians) {
  return radians * (180 / Math.PI);
}

/**
 * Converte angoli Euler (in GRADI) in Quaternion
 *
 * @param {number} x - Rotazione asse X in gradi
 * @param {number} y - Rotazione asse Y in gradi
 * @param {number} z - Rotazione asse Z in gradi
 * @param {string} order - Ordine rotazione (default 'XYZ')
 * @returns {THREE.Quaternion} Quaternion risultante
 *
 * @example
 * // Ruota 45° sull'asse Y
 * const quat = eulerDegreesToQuaternion(0, 45, 0);
 */
export function eulerDegreesToQuaternion(x, y, z, order = 'XYZ') {
  const euler = new THREE.Euler(
    degreesToRadians(x),
    degreesToRadians(y),
    degreesToRadians(z),
    order
  );
  const quaternion = new THREE.Quaternion();
  quaternion.setFromEuler(euler);
  return quaternion;
}

/**
 * Converte un Quaternion in angoli Euler (in GRADI)
 *
 * @param {THREE.Quaternion} quaternion - Quaternion da convertire
 * @param {string} order - Ordine rotazione (default 'XYZ')
 * @returns {{x: number, y: number, z: number}} Angoli in gradi
 */
export function quaternionToEulerDegrees(quaternion, order = 'XYZ') {
  const euler = new THREE.Euler();
  euler.setFromQuaternion(quaternion, order);
  return {
    x: radiansToDegrees(euler.x),
    y: radiansToDegrees(euler.y),
    z: radiansToDegrees(euler.z)
  };
}

/**
 * Moltiplica due quaternion (applica rotazione)
 *
 * NOTA SULL'ORDINE:
 * - multiplyQuaternions(offset, original) = applica offset IN SPAZIO LOCALE
 * - multiplyQuaternions(original, offset) = applica offset IN SPAZIO MONDO
 *
 * Per correggere pose di bones, solitamente vogliamo spazio locale.
 *
 * @param {THREE.Quaternion} q1 - Primo quaternion
 * @param {THREE.Quaternion} q2 - Secondo quaternion
 * @returns {THREE.Quaternion} Risultato q1 × q2
 */
export function multiplyQuaternions(q1, q2) {
  const result = new THREE.Quaternion();
  result.multiplyQuaternions(q1, q2);
  return result;
}

/**
 * Applica un offset in gradi Euler a un quaternion esistente
 *
 * Questa è la funzione principale per correggere le animazioni.
 * Prende i valori dell'animazione originale e applica una correzione.
 *
 * @param {number[]} quatValues - Array [x, y, z, w] del quaternion originale
 * @param {{x: number, y: number, z: number}} offsetDegrees - Offset in gradi
 * @param {string} mode - 'pre' (offset prima) o 'post' (offset dopo, default)
 * @returns {number[]} Array [x, y, z, w] del quaternion corretto
 *
 * @example
 * // Correggi piede ruotato: riduci rotazione X di 20°
 * const original = [0.1, 0.2, 0.3, 0.9]; // quaternion animazione
 * const corrected = applyQuaternionOffset(original, { x: -20, y: 0, z: 0 });
 */
export function applyQuaternionOffset(quatValues, offsetDegrees, mode = 'post') {
  // Crea quaternion dall'array originale
  const original = new THREE.Quaternion(
    quatValues[0],
    quatValues[1],
    quatValues[2],
    quatValues[3]
  );

  // Converti offset da gradi a quaternion
  const offset = eulerDegreesToQuaternion(
    offsetDegrees.x || 0,
    offsetDegrees.y || 0,
    offsetDegrees.z || 0
  );

  // Applica offset (l'ordine determina se è in spazio locale o mondo)
  let result;
  if (mode === 'pre') {
    // Offset applicato PRIMA della rotazione originale
    result = multiplyQuaternions(original, offset);
  } else {
    // Offset applicato DOPO la rotazione originale (default, spazio locale)
    result = multiplyQuaternions(offset, original);
  }

  // Normalizza per sicurezza (quaternion devono avere lunghezza 1)
  result.normalize();

  return [result.x, result.y, result.z, result.w];
}

/**
 * Applica offset a tutti i keyframes di una traccia quaternion
 *
 * Le tracce di animazione Three.js hanno i valori in un Float32Array
 * con formato [x0,y0,z0,w0, x1,y1,z1,w1, x2,y2,z2,w2, ...]
 * Ogni gruppo di 4 valori è un keyframe.
 *
 * @param {Float32Array|number[]} trackValues - Valori della traccia
 * @param {{x: number, y: number, z: number}} offsetDegrees - Offset in gradi
 * @returns {Float32Array} Nuovi valori con offset applicato
 *
 * @example
 * // In mapAnimationTracks, per ogni traccia che necessita correzione:
 * track.values = applyOffsetToTrack(track.values, { x: -20, y: 0, z: 0 });
 */
export function applyOffsetToTrack(trackValues, offsetDegrees) {
  // Crea copia per non modificare l'originale
  const newValues = new Float32Array(trackValues.length);

  // Pre-calcola il quaternion offset (uguale per tutti i keyframes)
  const offset = eulerDegreesToQuaternion(
    offsetDegrees.x || 0,
    offsetDegrees.y || 0,
    offsetDegrees.z || 0
  );

  // Applica a ogni keyframe (gruppi di 4 valori)
  for (let i = 0; i < trackValues.length; i += 4) {
    const original = new THREE.Quaternion(
      trackValues[i],
      trackValues[i + 1],
      trackValues[i + 2],
      trackValues[i + 3]
    );

    // offset × original = applica in spazio locale del bone
    const result = new THREE.Quaternion();
    result.multiplyQuaternions(offset, original);
    result.normalize();

    newValues[i] = result.x;
    newValues[i + 1] = result.y;
    newValues[i + 2] = result.z;
    newValues[i + 3] = result.w;
  }

  return newValues;
}

/**
 * DEBUG: Stampa quaternion in formato leggibile (gradi Euler)
 *
 * @param {THREE.Quaternion|number[]} quat - Quaternion o array [x,y,z,w]
 * @param {string} label - Etichetta per il log
 */
export function debugQuaternion(quat, label = 'Quaternion') {
  let q;
  if (Array.isArray(quat) || quat instanceof Float32Array) {
    q = new THREE.Quaternion(quat[0], quat[1], quat[2], quat[3]);
  } else {
    q = quat;
  }

  const euler = quaternionToEulerDegrees(q);
  console.log(`${label}:`, {
    quaternion: { x: q.x.toFixed(4), y: q.y.toFixed(4), z: q.z.toFixed(4), w: q.w.toFixed(4) },
    euler_degrees: { x: euler.x.toFixed(2), y: euler.y.toFixed(2), z: euler.z.toFixed(2) }
  });
}