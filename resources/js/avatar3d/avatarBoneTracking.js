import * as THREE from 'three';

// CC_Base bone names for tracking
export const BONE_NAMES = {
  head: 'CC_Base_Head',
  neck: 'CC_Base_NeckTwist01',
  leftEye: 'CC_Base_L_Eye',
  rightEye: 'CC_Base_R_Eye',
  leftUpperArm: 'CC_Base_L_Upperarm',
  rightUpperArm: 'CC_Base_R_Upperarm',
  leftClavicle: 'CC_Base_L_Clavicle',
  rightClavicle: 'CC_Base_R_Clavicle',
};

// Find and store bone references from the scene
export function findBones(scene) {
  const bones = {
    head: null,
    neck: null,
    leftEye: null,
    rightEye: null,
    leftUpperArm: null,
    rightUpperArm: null,
    leftClavicle: null,
    rightClavicle: null,
  };

  scene.traverse(node => {
    if (node.isBone) {
      if (node.name === BONE_NAMES.head) bones.head = node;
      if (node.name === BONE_NAMES.neck) bones.neck = node;
      if (node.name === BONE_NAMES.leftEye) bones.leftEye = node;
      if (node.name === BONE_NAMES.rightEye) bones.rightEye = node;
      if (node.name === BONE_NAMES.leftUpperArm) bones.leftUpperArm = node;
      if (node.name === BONE_NAMES.rightUpperArm) bones.rightUpperArm = node;
      if (node.name === BONE_NAMES.leftClavicle) bones.leftClavicle = node;
      if (node.name === BONE_NAMES.rightClavicle) bones.rightClavicle = node;
    }
  });

  return bones;
}

// Update head tracking based on mouse position
export function updateHeadTracking(headBone, mousePosition, mouseOffset) {
  if (!headBone) return;

  const maxRotationX = 0.3; // up/down
  const maxRotationY = 0.5; // left/right

  // Calculate target offset based on mouse position (inverted Y axis)
  const targetOffsetY = mousePosition.x * maxRotationY;
  const targetOffsetX = -mousePosition.y * maxRotationX;

  // Smoothly interpolate the offset
  mouseOffset.headY = THREE.MathUtils.lerp(mouseOffset.headY, targetOffsetY, 0.05);
  mouseOffset.headX = THREE.MathUtils.lerp(mouseOffset.headX, targetOffsetX, 0.05);

  // Add offset to animation rotation
  headBone.rotation.y += mouseOffset.headY;
  headBone.rotation.x += mouseOffset.headX;
}

// Update neck tracking based on mouse position
export function updateNeckTracking(neckBone, mousePosition, mouseOffset) {
  if (!neckBone) return;

  const maxRotationY = 0.2;
  const maxRotationX = 0.1;

  // Calculate target offset
  const targetOffsetY = mousePosition.x * maxRotationY;
  const targetOffsetX = -mousePosition.y * maxRotationX;

  // Smoothly interpolate the offset
  mouseOffset.neckY = THREE.MathUtils.lerp(mouseOffset.neckY, targetOffsetY, 0.03);
  mouseOffset.neckX = THREE.MathUtils.lerp(mouseOffset.neckX, targetOffsetX, 0.03);

  // Add offset to animation rotation
  neckBone.rotation.y += mouseOffset.neckY;
  neckBone.rotation.x += mouseOffset.neckX;
}

// Update arm positioning - moves arms slightly based on mouse
// This helps prevent body/clothing intersection
export function updateArmTracking(bones, mousePosition, mouseOffset, enabled = true) {
  if (!enabled) return;

  const { leftUpperArm, rightUpperArm, leftClavicle, rightClavicle } = bones;
  const armMovement = 0.03; // Very subtle arm movement
  const targetArmZ = mousePosition.x * armMovement;

  mouseOffset.armZ = THREE.MathUtils.lerp(mouseOffset.armZ, targetArmZ, 0.05);

  // Left arm
  if (leftUpperArm) {
    leftUpperArm.rotation.z -= mouseOffset.armZ;
  }

  // Right arm
  if (rightUpperArm) {
    rightUpperArm.rotation.z += mouseOffset.armZ;
  }

  // Clavicles (shoulders) also follow
  if (leftClavicle) {
    leftClavicle.rotation.z -= mouseOffset.armZ * 0.5;
  }
  if (rightClavicle) {
    rightClavicle.rotation.z += mouseOffset.armZ * 0.5;
  }
}

// Debug logging for bones
export function logBones(scene) {
  console.log("=== Model Structure ===");
  scene.traverse(node => {
    if (node.type === 'Mesh' || node.type === 'SkinnedMesh') {
      console.log(`Mesh: ${node.name}, Type: ${node.type}`);
    }
    if (node.isBone) {
      console.log(`Bone: ${node.name}`);
    }
  });
  console.log("=== End Model Structure ===");

  scene.traverse(node => {
    if (node.isSkinnedMesh && node.skeleton) {
      console.log("=== Skeleton Bones ===");
      console.log(node.skeleton.bones.map(b => b.name));
      console.log("=== End Skeleton Bones ===");
    }
  });
}

// Debug logging for morph targets
export function logMorphTargets(scene) {
  scene.traverse(node => {
    if ((node.type === 'Mesh' || node.type === 'SkinnedMesh') && node.morphTargetDictionary) {
      console.log("=== Morph Targets ===");
      console.log(Object.keys(node.morphTargetDictionary));
      console.log("=== End Morph Targets ===");
    }
  });
}

/*
 * MANUAL ARM ROTATION - Experimental values for arm positioning
 * Kept for reference, but arm tracking (updateArmTracking) works better
 *
 * const lefUpperArmBoneXGrade = 1
 * const lefUpperArmBoneYGrade = -.5
 * const lefUpperArmBoneZGrade = 1
 *
 * if (leftUpperArmBone) {
 *   leftUpperArmBone.rotation.x -= lefUpperArmBoneXGrade;
 *   leftUpperArmBone.rotation.y -= lefUpperArmBoneYGrade * -2;
 *   leftUpperArmBone.rotation.z -= lefUpperArmBoneZGrade;
 * }
 * if (rightUpperArmBone) {
 *   rightUpperArmBone.rotation.x += lefUpperArmBoneXGrade;
 *   rightUpperArmBone.rotation.y += lefUpperArmBoneYGrade;
 *   rightUpperArmBone.rotation.z += lefUpperArmBoneZGrade;
 * }
 */