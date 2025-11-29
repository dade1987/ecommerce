import * as THREE from 'three';
import POSE_START from './poses/poseStart.json';
import POSE_END from './poses/poseEnd.json';

/**
 * Animazione braccia e spalle - versione realistica
 * - Movimento fluido con più keyframes
 * - Timing sfasato tra le parti del corpo
 * - Respiro sottile
 * - Leggera asimmetria
 */

// Durata ciclo completo (secondi)
const DURATION = 6;

// Helper: gradi -> radianti
const deg2rad = (deg) => deg * (Math.PI / 180);

// Esporta le pose
export { POSE_START, POSE_END };

// Crea quaternion da euler (gradi)
function eulerToQuat(x, y, z) {
  const euler = new THREE.Euler(deg2rad(x), deg2rad(y), deg2rad(z), 'XYZ');
  const quat = new THREE.Quaternion();
  quat.setFromEuler(euler);
  return [quat.x, quat.y, quat.z, quat.w];
}

// Interpola tra due valori con easing
function lerp(a, b, t) {
  return a + (b - a) * t;
}

// Crea keyframes con easing naturale (più punti intermedi)
function createSmoothKeyframes(start, end, offset = 0, variation = 0) {
  // Offset temporale per sfasare le parti del corpo
  const timeOffset = offset * DURATION;

  // Leggera variazione per asimmetria
  const endWithVariation = {
    x: end.x + variation,
    y: end.y + variation * 0.5,
    z: end.z + variation
  };

  // 5 keyframes per ciclo fluido: start -> mid1 -> end -> mid2 -> start
  const times = [
    0,
    DURATION * 0.25,
    DURATION * 0.5,
    DURATION * 0.75,
    DURATION
  ].map(t => (t + timeOffset) % DURATION).sort((a, b) => a - b);

  // Calcola posizioni intermedie con easing
  const mid1 = {
    x: lerp(start.x, endWithVariation.x, 0.3),
    y: lerp(start.y, endWithVariation.y, 0.4),
    z: lerp(start.z, endWithVariation.z, 0.35)
  };
  const mid2 = {
    x: lerp(endWithVariation.x, start.x, 0.3),
    y: lerp(endWithVariation.y, start.y, 0.4),
    z: lerp(endWithVariation.z, start.z, 0.35)
  };

  const quatStart = eulerToQuat(start.x, start.y, start.z);
  const quatMid1 = eulerToQuat(mid1.x, mid1.y, mid1.z);
  const quatEnd = eulerToQuat(endWithVariation.x, endWithVariation.y, endWithVariation.z);
  const quatMid2 = eulerToQuat(mid2.x, mid2.y, mid2.z);

  const values = [
    ...quatStart,
    ...quatMid1,
    ...quatEnd,
    ...quatMid2,
    ...quatStart
  ];

  return { times: [0, DURATION * 0.25, DURATION * 0.5, DURATION * 0.75, DURATION], values };
}

// Crea keyframes per respiro (movimento sottile ciclico)
function createBreathingKeyframes(basePose, breathAmount = 2) {
  const times = [0, DURATION * 0.3, DURATION * 0.5, DURATION * 0.7, DURATION];

  const poses = [
    basePose,
    { x: basePose.x + breathAmount * 0.5, y: basePose.y, z: basePose.z },
    { x: basePose.x + breathAmount, y: basePose.y + breathAmount * 0.3, z: basePose.z },
    { x: basePose.x + breathAmount * 0.5, y: basePose.y, z: basePose.z },
    basePose
  ];

  const values = poses.flatMap(p => eulerToQuat(p.x, p.y, p.z));
  return { times, values };
}

// Crea l'AnimationClip
export function createArmAnimationClip() {
  const tracks = [];

  // === SPINE (con respiro) ===
  const spine01Data = createBreathingKeyframes(POSE_START.spine01, 1.5);
  tracks.push(new THREE.QuaternionKeyframeTrack(
    'CC_Base_Spine01.quaternion',
    spine01Data.times,
    spine01Data.values
  ));

  const spine02Data = createBreathingKeyframes(POSE_START.spine02, 2);
  tracks.push(new THREE.QuaternionKeyframeTrack(
    'CC_Base_Spine02.quaternion',
    spine02Data.times,
    spine02Data.values
  ));

  // === RIBS (seguono respiro, leggermente sfasati) ===
  const leftRibsData = createSmoothKeyframes(POSE_START.leftRibs, POSE_END.leftRibs, 0.05, 0);
  tracks.push(new THREE.QuaternionKeyframeTrack(
    'CC_Base_L_RibsTwist.quaternion',
    leftRibsData.times,
    leftRibsData.values
  ));

  const rightRibsData = createSmoothKeyframes(POSE_START.rightRibs, POSE_END.rightRibs, 0.08, 0);
  tracks.push(new THREE.QuaternionKeyframeTrack(
    'CC_Base_R_RibsTwist.quaternion',
    rightRibsData.times,
    rightRibsData.values
  ));

  // === CLAVICOLE (sfasate tra loro) ===
  const leftClavData = createSmoothKeyframes(POSE_START.leftClavicle, POSE_END.leftClavicle, 0, 2);
  tracks.push(new THREE.QuaternionKeyframeTrack(
    'CC_Base_L_Clavicle.quaternion',
    leftClavData.times,
    leftClavData.values
  ));

  const rightClavData = createSmoothKeyframes(POSE_START.rightClavicle, POSE_END.rightClavicle, 0.1, -1);
  tracks.push(new THREE.QuaternionKeyframeTrack(
    'CC_Base_R_Clavicle.quaternion',
    rightClavData.times,
    rightClavData.values
  ));

  // === BRACCIA (seguono le clavicole con ritardo) ===
  const leftArmData = createSmoothKeyframes(POSE_START.leftUpperArm, POSE_END.leftUpperArm, 0.05, 1);
  tracks.push(new THREE.QuaternionKeyframeTrack(
    'CC_Base_L_Upperarm.quaternion',
    leftArmData.times,
    leftArmData.values
  ));

  const rightArmData = createSmoothKeyframes(POSE_START.rightUpperArm, POSE_END.rightUpperArm, 0.15, -0.5);
  tracks.push(new THREE.QuaternionKeyframeTrack(
    'CC_Base_R_Upperarm.quaternion',
    rightArmData.times,
    rightArmData.values
  ));

  // === AVAMBRACCI (seguono le braccia con più ritardo) ===
  const leftForeArmData = createSmoothKeyframes(POSE_START.leftForeArm, POSE_END.leftForeArm, 0.1, 3);
  tracks.push(new THREE.QuaternionKeyframeTrack(
    'CC_Base_L_Forearm.quaternion',
    leftForeArmData.times,
    leftForeArmData.values
  ));

  const rightForeArmData = createSmoothKeyframes(POSE_START.rightForeArm, POSE_END.rightForeArm, 0.2, -2);
  tracks.push(new THREE.QuaternionKeyframeTrack(
    'CC_Base_R_Forearm.quaternion',
    rightForeArmData.times,
    rightForeArmData.values
  ));

  const clip = new THREE.AnimationClip('ArmAnimation', DURATION, tracks);

  return clip;
}

export default createArmAnimationClip;
